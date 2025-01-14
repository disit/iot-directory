import json
import time
from datetime import datetime, timedelta

import requests


# Chiamata alle api della blockchain
def call_blockchain(deviceId, startDate, endDate, token):
    start_time_api_bc_call = time.time()
    dt = datetime.strptime(endDate, "%Y-%m-%dT%H:%M:%S")
    dt += timedelta(seconds=-1)
    endDate = dt.strftime("%Y-%m-%dT%H:%M:%S")
    parametersBC = {
        "deviceId": deviceId,
        "startDate": startDate,
        "endDate": endDate
    }
    headers = {"authorization": "Token: " + token}
    try:
        response_bc = requests.post("http://127.0.0.1:9999/api/dataCertificationCheckTimeSeries", params=parametersBC,
                                    headers=headers)
        response_bc.raise_for_status()
    except Exception:
        raise
    else:
        end_time_api_bc_call = time.time()
        api_call_time_bc = end_time_api_bc_call - start_time_api_bc_call
        return response_bc, api_call_time_bc


# chiamata a snap
def call_Snap4City(deviceId, startDate, endDate, token):
    start_time_api_snap_call = time.time()
    if "Z" in startDate:
        startDate = startDate[:-5]
    dt = datetime.strptime(endDate, "%Y-%m-%dT%H:%M:%S")
    dt += timedelta(seconds=-1)
    endDate = dt.strftime("%Y-%m-%dT%H:%M:%S")
    parametersSnap = {
        "serviceUri": 'http://www.disit.org/km4city/resource/iot/orion-1/Organization/' + deviceId,
        "fromTime": startDate,
        "toTime": endDate
    }
    headers = {"Authorization": "Bearer " + token}

    try:
        response_snap = requests.get("http://127.0.0.1/ServiceMap/api/v1", params=parametersSnap, headers=headers)
        response_snap.raise_for_status()
        end_time_api_snap_call = time.time()
        api_call_time_snap = end_time_api_snap_call - start_time_api_snap_call
    except Exception:
        raise
    else:
        try:
            bindings = extract_bindings(json.dumps(response_snap.json()))
        except Exception:
            raise
        else:
            return bindings, api_call_time_snap


def extract_bindings(json_data):
    try:
        # Parse the JSON data
        data = json.loads(json_data)

        # Prova per gestire latitude e longitude fra i parametri
        # latitude =data.get("Service",{}).get("features",[0])
        # latitude=latitude[0].get("geometry",{}).get("coordinates",[])
        # latitude=json.loads(str(latitude))
        # print(latitude[0])
        # print(latitude[1])
        # latitude=latitude[0]
        # longitude=latitude[1]
        # print(latitude)
        # print(longitude)

        # Extract the bindings
        bindings = data.get("realtime", {}).get("results", {}).get("bindings", [])
        for binding in bindings:
            binding.pop("measuredTime", None)

        return bindings
    except json.JSONDecodeError as e:
        print(f"Error parsing JSON: {e}")
        raise
