import hashlib
import json
import time
from datetime import datetime, timedelta

import dateutil.parser
import mysql.connector

from DPU_ApiConsumer import call_blockchain, call_Snap4City


def compute_data_verification(deviceId, startDate, endDate, token):
    dt = datetime.strptime(startDate, "%Y-%m-%dT%H:%M:%S")
    dt += timedelta(seconds=-1)
    startDate = dt.strftime("%Y-%m-%dT%H:%M:%S")
    try:
        iotdb = mysql.connector.connect(
            host="192.168.1.139",
            user="root",
            password="vPBIXxaU3oa9xbPv",
            database="iotdb"
        )

    except Exception as Err:
        raise Exception(Err)
    try:
        mycursor2 = iotdb.cursor()
        sql = "UPDATE iotdb.Blockchain_verification_requests SET request_status = %s WHERE device_id = %s AND start_date = %s AND end_date= %s"
        val = ("execution", deviceId, str(startDate), str(endDate))
        mycursor2.execute(sql, val)
        iotdb.commit()
        mycursor2.close()
    except Exception as Err:
        print(Err)
        raise Exception(Err)

    total_matching_values = 0
    total_analysed_values = 0
    total_missing_values = 0
    pagination_start = datetime.strptime(startDate, "%Y-%m-%dT%H:%M:%S")
    pagination_end = datetime.strptime(endDate, "%Y-%m-%dT%H:%M:%S")
    pagination_end = pagination_end + timedelta(days=1, seconds=1)
    res_end_date = pagination_start + timedelta(days=1)
    res_start_date = pagination_start
    matching_values_list = []
    missing_values_list = []
    bc_missing_values_list = []

    elapsed_time_compare = 0
    count_api_calls = 0
    total_api_calls_bc_time = 0
    total_api_calls_snap_time = 0

    while res_end_date <= pagination_end:
        count_api_calls = count_api_calls + 1

        paginated_startdate = res_start_date.strftime("%Y-%m-%dT%H:%M:%S")
        paginated_enddate = res_end_date.strftime("%Y-%m-%dT%H:%M:%S")

        res_start_date += timedelta(days=1)
        res_end_date += timedelta(days=1)

        try:
            # call the blockchain query function
            blockchain_response, api_call_bc_time = call_blockchain(deviceId, paginated_startdate, paginated_enddate,
                                                                    token)

            total_api_calls_bc_time += api_call_bc_time

        except Exception:
            raise

        # call snap4city query function
        try:
            snap4city_response, api_call_snap_time = call_Snap4City(deviceId, paginated_startdate, paginated_enddate,
                                                                    token)

            total_api_calls_snap_time += api_call_snap_time

            if not snap4city_response:
                continue
        except Exception:
            raise

        if not isinstance(blockchain_response, Exception) and not isinstance(snap4city_response, Exception):
            blockchain_response = blockchain_response.json()
            start_time_compare = time.time()
            matching_values, missing_values, bc_missing_values, analysed_objects, number_of_matches = values_clean_and_truncate(
                blockchain_response, snap4city_response)

            end_time_compare = time.time()

            elapsed_time_compare += end_time_compare - start_time_compare

            # paginazione

            if matching_values:
                total_matching_values += number_of_matches
                total_analysed_values += analysed_objects
                matching_values_list.append(matching_values)
            if missing_values:
                missing_values_list.append(missing_values)
            if bc_missing_values:
                bc_missing_values_list.append(bc_missing_values)
            total_missing_values += len(missing_values)

        else:
            return str(blockchain_response), str(snap4city_response)

    final_result = json.dumps({"matching_values": matching_values_list,
                               "missing_values": missing_values_list,
                               "bc_missing_values": bc_missing_values_list,
                               "total_matching_values": total_matching_values,
                               "total_analysed_values": total_matching_values + total_missing_values,
                               "total_missing_values": total_missing_values})

    median_api_calls_bc_time = total_api_calls_bc_time / count_api_calls
    median_api_calls_snap_time = total_api_calls_snap_time / count_api_calls

    return final_result, total_matching_values + total_missing_values, total_matching_values, total_missing_values, elapsed_time_compare, median_api_calls_bc_time, median_api_calls_snap_time


# check if a timestamp is in the correct ISO datetime format
def is_valid_iso_timestamp(timestamp):
    try:
        datetime.fromisoformat(timestamp.replace('Z', '+00:00'))
        return True
    except ValueError:
        return False


# hashing function for snap4city data
def hash_calculation(to_hash_snap4item):
    to_hash_snap4item = str(to_hash_snap4item).replace("'", "\"")
    to_hash_snap4item = json.loads(to_hash_snap4item)
    to_hash_snap4item = str(to_hash_snap4item).replace("'", "")
    to_hash_snap4item = str(to_hash_snap4item).replace(" ", "")
    to_hash_snap4item = json.dumps(to_hash_snap4item)

    return hashlib.sha1(to_hash_snap4item.encode()).hexdigest()


def values_clean_and_truncate(blockchain_response, snap4city_response):
    matching_values = []
    missing_values = []
    bc_missing_values = []

    # for every blockchain value recover timestamp and hash from the blockchain response data
    j = 0
    i = 0
    try:
        while i < len(blockchain_response):
            bc_timestamp = blockchain_response[i]['timeStamp']
            bc_hash = blockchain_response[i]['hashDev']

            bc_timestamp = dateutil.parser.isoparse(bc_timestamp)
            snap4_timestamp = dateutil.parser.isoparse(snap4city_response[j]['dateObserved']['value'])

            # uguali
            if bc_timestamp == snap4_timestamp:

                snap4city_ordered_values = value_ordering_and_truncate(snap4city_response[j])
                snap4_hash = hash_calculation(snap4city_ordered_values)

                if snap4_hash == bc_hash:
                    matching_values.append({
                        'bc_timestamp': bc_timestamp.strftime("%Y-%m-%d %H:%M:%S %Z"),
                        'bc_hash': bc_hash,
                        'varDev': blockchain_response[i]['varDev'],
                        'snap4_hash': hash_calculation(snap4city_ordered_values)
                    })

                elif snap4_hash != bc_hash:
                    print("hash mismatch")
                    missing_values.append({'hash_mismatch ': snap4city_response[j]['dateObserved']})
                j += 1
                i += 1
            # uguali

            elif bc_timestamp > snap4_timestamp:
                # dato di snap non presente su bc
                missing_values.append({'snap_timestamp': snap4city_response[j]['dateObserved']})
                # scorro di uno snap
                j += 1
            elif bc_timestamp < snap4_timestamp:
                bc_missing_values.append({'bc_timestamp': blockchain_response[i]['dateObserved']})
                i += 1

    except Exception:
        raise Exception('Internal error while checking data')
    return matching_values, missing_values, bc_missing_values, len(blockchain_response), len(matching_values)


def value_ordering_and_truncate(snap4city_response):
    snap4city_ordered_values = {k: v for k, v in sorted(snap4city_response.items())}

    for key in snap4city_ordered_values:
        value = snap4city_ordered_values[key]["value"]

        if isinstance(value, str) and key != 'dateObserved':
            if "E-" in snap4city_ordered_values[key]["value"]:
                snap4city_ordered_values[key]["value"] = str(
                    convert_scientific_notation(snap4city_ordered_values[key]["value"]))
                snap4city_ordered_values[key]["value"] = (snap4city_ordered_values[key]["value"])[:8]
            elif len(snap4city_ordered_values[key]["value"]) < 6:
                if "." in snap4city_ordered_values[key]["value"]:
                    while len(snap4city_ordered_values[key]["value"]) < 6:
                        snap4city_ordered_values[key]["value"] = snap4city_ordered_values[key]["value"] + "0"

                else:
                    snap4city_ordered_values[key]["value"] = snap4city_ordered_values[key]["value"] + "."
                    while len(snap4city_ordered_values[key]["value"]) < 6:
                        snap4city_ordered_values[key]["value"] = snap4city_ordered_values[key]["value"] + "0"
            else:
                # If the value is a string, try converting it to a float and then format it with 7 decimal places
                try:

                    snap4city_ordered_values[key]["value"] = (snap4city_ordered_values[key]["value"])[:6]

                except ValueError:
                    pass  # If conversion fails, leave the value unchanged

    return snap4city_ordered_values


def convert_scientific_notation(float_str):
    try:
        value = format(float(float_str), 'f')
        return value
    except ValueError:
        raise ValueError("Invalid exponent in scientific notation")
