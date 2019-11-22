var fs = require('fs');
var Sensor = require('../Classes/Sensor');
var sqlite3 = require('sqlite3').verbose();

let db = new sqlite3.Database('Database.db');

module.exports = {

    insert: function ()
    {
        let getAllSensorsQuery = "select * from Sensor";

        db.serialize(function () {

            let sensors = [];
            let deviceNames = [];

            db.each(getAllSensorsQuery,
                function (err, row) {

                    if (err) {
                        console.error("Problemi con la query");
                        return;
                    }

                    if (row["function_id"] != null && row["operator_id"] != null
                        && row["latitude"] != null && row["longitude"] != null
                        && row["state"] === "Attivo") {
                        let s = new Sensor(row["sensor_id"], row['vt'], row["freq"], row["latitude"],
                            row["longitude"], row["name"], row["altitude"], row["state"], row["device"],
                            row["function_id"], row["operator_id"], row["u"]);

                        sensors.push(s);
                        deviceNames.push(row["device"]);
                    }
                },
                function (err, count) {

                    if (count === 0) {
                        console.log("Nessun risultato per tale query");
                        return;
                    }

                    console.log("Inserted/updated row: " + sensors.length);

                    insertExtractionRules(sensors);
                    insertDeviceRuleAssociation(sensors);
                    insertDeviceExtractionRule(deviceNames);
                });
        });
    }
};

function insertExtractionRules(sensors) {

    for(let i = 0; i < sensors.length; i++) {

        let sensor_id = "sensor_" + sensors[i].id;
        let category = "sensor";
        let basicType = "timestamp, float, integer";
        let vt = "timestamp, " + sensors[i].sensorType.toString() + ", validita_dato";
        let unit = "s, " + sensors[i].measureType + ", null";
        let selector = createSelector();

        db.run(
            'INSERT OR REPLACE INTO ExtractionRule (id, selector, category, basicType, vt, unit)' +
            'VALUES (?, ?, ?, ?, ?, ?)',
            [sensor_id, selector, category, basicType, vt, unit]
        )
    }
}

function insertDeviceRuleAssociation(sensors) {

    for(let i = 0; i < sensors.length; i++) {

        let device = sensors[i].device;
        let sensor_id = "sensor_" + sensors[i].id;

        device = convertStringDevice(device);

        //console.log(sensor_id + ": " + device);

        db.run(
            'INSERT OR REPLACE INTO DeviceRuleAssociation (der, er)' +
            'VALUES (?, ?)',
            [device, sensor_id]
        )
    }
}

function insertDeviceExtractionRule(devices) {

    let deviceSet = new Set(devices);

    for (let item of deviceSet) {

        db.run(
            'INSERT OR REPLACE INTO DeviceExtractionRule (name, device)' +
            'VALUES (?, ?)',
            [convertStringDevice(item), item]
        )
    }
}

function createSelector() {

    return "[\n" +
        "  {\n" +
        "    \"type\": \"XML\",\n" +
        "    \"param\": {\n" +
        "      \"s\": \"//Dati\",\n" +
        "      \"i\": 0\n" +
        "    }\n" +
        "  },\n" +
        "  {\n" +
        "    \"type\": \"SM\",\n" +
        "    \"param\": {\n" +
        "      \"s\": \"trim\",\n" +
        "      \"i\": []\n" +
        "    }\n" +
        "  },\n" +
        "  {\n" +
        "    \"type\": \"CSV\",\n" +
        "    \"param\": {\n" +
        "      \"s\": [\n" +
        "        \"\\n\"\n" +
        "      ],\n" +
        "      \"i\": [\n" +
        "        -1\n" +
        "      ]\n" +
        "    }\n" +
        "  },\n" +
        "  {\n" +
        "    \"type\": \"CSV\",\n" +
        "    \"param\": {\n" +
        "      \"s\": [\n" +
        "        \";\"\n" +
        "      ],\n" +
        "      \"i\": [\n" +
        "        0\n" +
        "      ]\n" +
        "    }\n" +
        "  }\n" +
        "]"
}

function convertStringDevice(device) {

    device = device.trim().replace(/ /gi, "");
    device = device.replace("-", "_");

    if(device.includes('(') || device.includes(')')){

        const tokens = device.split(/[()]/);
        tokens[1] = tokens[1].toUpperCase();
        device = tokens.join("");
    }

    if(device.includes('{') || device.includes('}')){

        const tokens = device.split(/[{}]/);
        tokens[1] = tokens[1].toUpperCase();
        device = tokens.join("");
    }

    device = device + "_rule";

    return device;
}

function convertSpecialCharactersForOrion(string) {

    if(string.includes("<")) {
        string = string.replace(/</gi, "%3C");
    }
    if(string.includes(">")) {
        string = string.replace(/>/gi, "%3E");
    }
    if(string.includes("\"")) {
        string = string.replace(/"/gi, "%22");
    }
    if(string.includes("'")) {
        string = string.replace(/'/gi, "%27");
    }
    if(string.includes("=")) {
        string = string.replace(/=/gi, "%3D");
    }
    if(string.includes(";")) {
        string = string.replace(/;/gi, "%3B");
    }
    if(string.includes("(")) {
        string = string.replace(/[(]/gi, "%28");
    }
    if(string.includes(")")) {
        string = string.replace(/[)]/gi, "%29");
    }

    return string;
}
