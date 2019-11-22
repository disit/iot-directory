let Sensor_Type = require('./Sensor_Type');


module.exports = class Sensor {

    constructor(id, sensorType, frequency, lat, lon, name, altitude, state, device,
                functionId, operatorId, measureType){
        this._id = id;
        this._lat = lat;
        this._lon = lon;
        this._frequency = frequency;
        this._name = name;
        this._altitude = altitude;
        this._state = state;
        this._functionId = functionId;
        this._operatorId = operatorId;
        this._measureType = measureType;

        device = device.trim();

        /*if(device.includes('(') || device.includes(')')){

            device = device.replace (/ /gi, "_");
            const tokens = device.split(/[()]/);
            device = tokens.join("");
        }
        else {
            device = device.replace(/ /gi, "_");
            device = device.replace("-", "_");
        }*/

        this._device = device;

        /*for(let i in Sensor_Type.TYPE) {
            if(Sensor_Type.TYPE[i].toLowerCase() === sensorType.toLowerCase()) {
                this._sensorType = i;
                break;
            }
        }*/
        this._sensorType = sensorType;
    }

    getIdPeriodo_From_Frequency() {

        switch (this.frequency) {

            case 10: return 1;
            case 30: return 2;
            case 60: return 3;
            case 1440: return 4;
            case 1: return 5;
            case 180: return 6;
            case 120: return 8;
            case 240: return 9;
            case 5: return 10;

            default: return -1;
        }
    }

    get measureType() {
        return this._measureType;
    }

    set measureType(value) {
        this._measureType = value;
    }

    get functionId() {
        return this._functionId;
    }

    set functionId(value) {
        this._functionId = value;
    }
    get operatorId() {
        return this._operatorId;
    }

    set operatorId(value) {
        this._operatorId = value;
    }
    get device() {
        return this._device;
    }

    set device(device) {

        device = device.trim();

        if(device.includes('(') || device.includes(')')){

            device = device.replace (/ /gi, "_");
            const tokens = device.split(/[()]/);
            device = tokens.join("");
        }
        else {
            device = device.replace(/ /gi, "_");
            device = device.replace("-", "_");
        }

        this._device = device;
    }

    get state() {
        return this._state;
    }

    set state(value) {
        this._state = value;
    }
    get altitude() {
        return this._altitude;
    }

    set altitude(value) {
        this._altitude = value;
    }
    get name() {
        return this._name;
    }

    set name(value) {
        this._name = value;
    }
    get frequency() {
        return this._frequency;
    }

    set frequency(value) {
        this._frequency = value;
    }
    get lon() {
        return this._lon;
    }

    set lon(value) {
        this._lon = value;
    }
    get lat() {
        return this._lat;
    }

    set lat(value) {
        this._lat = value;
    }

    get id() {
        return this._id;
    }

    set id(value) {
        this._id = value;
    }

    get sensorType() {
        return this._sensorType;
    }

    set sensorType(value) {

        for(let i in Sensor_Type.TYPE) {
            if(Sensor_Type.TYPE[i].toLowerCase() === value.toLowerCase())
                this._sensorType = i;
        }
    }

    sensorTypeNotConverted() {
        return Sensor_Type.TYPE[this.sensorType];
    }
};
