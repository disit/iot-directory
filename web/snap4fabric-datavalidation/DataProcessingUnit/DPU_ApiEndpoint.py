from datetime import datetime
from datetime import timedelta
from timeit import default_timer as timer

import mysql
import mysql.connector
import requests

import DataProcessingCore


def DataVerification():
    token = callKeycloak()

    try:
        global iotdb
        iotdb = mysql.connector.connect(
            host="192.168.1.139",
            user="root",
            password="vPBIXxaU3oa9xbPv",
            database="iotdb"
        )
        mycursor1 = iotdb.cursor(buffered=True)
        sql = "SELECT * FROM iotdb.Blockchain_verification_requests WHERE request_status='pending' ORDER BY idBlockchain_verification_requests ASC"
        mycursor1.execute(sql)
        queryresult = mycursor1.fetchone()
        mycursor1.close()
        if queryresult:
            deviceId = queryresult[1]
            startDate = queryresult[2]
            endDate = queryresult[3]
            dt = datetime.strptime(startDate, "%Y-%m-%dT%H:%M:%S")
            dt += timedelta(seconds=1)
            startDate = dt.strftime("%Y-%m-%dT%H:%M:%S")
        else:
            print("niente in coda")
            return

    except Exception as Err:
        print("Problem communicating with the DB: " + str(Err))
        return Err

    try:
        start = timer()
        final_result, total_analysed_values, total_matching_values, total_missing_values, elapsed_time_compare, median_api_calls_bc_time, median_api_calls_snap_time = DataProcessingCore.compute_data_verification(
            deviceId, startDate, endDate, token)
        end = timer()
        elapsed_time = end - start
        print(elapsed_time)
        dt = datetime.strptime(startDate, "%Y-%m-%dT%H:%M:%S")
        dt += timedelta(seconds=-1)
        startDate = dt.strftime("%Y-%m-%dT%H:%M:%S")

    except Exception as Err:
        error_handling_db(Err, deviceId, startDate, endDate)

    else:
        try:
            mycursor = iotdb.cursor()
            sql = "UPDATE iotdb.Blockchain_verification_requests SET check_performed=%s ,certified_data=%s , missing_data=%s , report=%s ,report_missing=%s WHERE device_id = %s AND start_date = %s AND end_date= %s"
            val = (str(total_analysed_values), str(total_matching_values), str(total_missing_values), str(final_result),
                   str(final_result), deviceId, startDate, endDate)
            mycursor.execute(sql, val)
            iotdb.commit()
            mycursor.close()

            mycursor3 = iotdb.cursor()
            sql = "UPDATE iotdb.Blockchain_verification_requests SET request_status = %s, elapsed_time = %s, elapsed_time_compare=%s, median_api_calls_bc_time=%s,median_api_calls_snap_time=%s WHERE device_id = %s AND request_status= %s AND start_date = %s AND end_date= %s"
            val = (
                "completed", elapsed_time, elapsed_time_compare, median_api_calls_bc_time, median_api_calls_snap_time,
                deviceId, "execution", startDate, endDate)
            mycursor3.execute(sql, val)
            iotdb.commit()
            mycursor3.close()
        except Exception as Err:
            print("Problems communicating with the db : " + str(Err))
    return


def callKeycloak():
    token_endpoint = "http://dashboard/auth/realms/master/protocol/openid-connect/token"

    # Set the client credentials and user credentials
    client_id = "js-grp-client"
    username = "userrootadmin"
    password = "Sl9.wrE@k"

    # Prepare the data for the POST request
    data = {
        "grant_type": "password",
        "client_id": client_id,
        "username": username,
        "password": password,
    }

    # Set the content type
    headers = {
        "Content-Type": "application/x-www-form-urlencoded"
    }

    # Make the POST request to get the tokens
    response = requests.post(token_endpoint, data=data, headers=headers)

    # Check if the request was successful (status code 200)
    if response.status_code == 200:
        token_data = response.json()
        access_token = token_data.get("access_token")
        refresh_token = token_data.get("refresh_token")
        if access_token:
            return access_token
        else:
            return refresh_token
    else:
        print("Error:", response.status_code, response.text)


def error_handling_db(Err, deviceId, startDate, endDate):
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
        mycursor = iotdb.cursor()
        sql = "UPDATE iotdb.Blockchain_verification_requests SET request_status = %s , report= %s WHERE device_id = %s AND start_date = %s AND end_date= %s"
        val = ("error", str(Err), deviceId, str(startDate), str(endDate))
        mycursor.execute(sql, val)
        iotdb.commit()
        mycursor.close()

    except Exception as Err:
        print("problems communicating with the db" + str(Err))


def main():
    try:
        for i in range(6):
            DataVerification()
    except Exception as Err:
        print(str(Err))


if __name__ == "__main__":
    main()
