const request = require('request');
var Sensor = require("../Classes/Sensor");
var sqlite3 = require('sqlite3').verbose();

let db = new sqlite3.Database('Database.db');

module.exports = {

    deleteInOrion: function(sensors){

        for(let i = 0; i < sensors.length; i++) {

            request.delete('http://159.149.129.184:1026/v2/entities/' + sensors[i].id.toString(), (error, response, body) => {

                if (error) {
                    console.error(error);
                    return;
                }

                console.log("status code: " + response.statusCode);
            });
        }
    },

    insertInOrion: function(){

        // query per l'inserimento dei sensori in orion (per decidere quali inserire)
        let getAllSensorsQuery = "select * from Sensor order by sensor_id limit 1";

        db.serialize(function () {

            let sensors = [];

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
                    }
                },
                function (err, count) {

                    if (count === 0) {
                        console.log("Nessun risultato per tale query");
                        return;
                    }

                    for(let i = 0; i < sensors.length; i++) {

                        request.post('http://159.149.129.184:1026/v2/entities/', {
                            json: {
                                "id": sensors[i].id.toString(),
                                "type": "Sensor",

                                "frequenza": {
                                    "type": "integer",
                                    "value": sensors[i].frequency,
                                    "metadata": {}
                                },

                                "geolocalization_lat": {
                                    "type": "Float",
                                    "value": sensors[i].lat,
                                    "metadata": {}
                                },

                                "geolocalization_lon": {
                                    "type": "Float",
                                    "value": sensors[i].lon,
                                    "metadata": {}
                                },

                                "nome":{
                                    "type": "string",
                                    "value": deleteSpecialCharacters(sensors[i].name),
                                    "metadata": {}
                                },

                                "quota":{
                                    "type": "integer",
                                    "value": sensors[i].altitude,
                                    "metadata": {}
                                },

                                "stato": {
                                    "type": "string",
                                    "value": sensors[i].state,
                                    "metadata": {}
                                },

                                "stazione": {
                                    "type": "string",
                                    "value": deleteSpecialCharacters(sensors[i].device),
                                    "metadata": {}
                                },

                                //timestamp
                                //value_dato (direzione, altezza_neve, temp, ecc)

                                "unitaDiMisura": {
                                    "type": "string",
                                    "value": sensors[i].measureType,
                                    "metadata": {}
                                },
                            }
                        }, (error, response, body) => {

                            if (error) {
                                console.error(error);
                                return;
                            }

                            console.log("status code: " + response.statusCode);
                        });
                    }
                });
        });
    }

};

function deleteSpecialCharacters(s) {

    s = s.trim();
    if(s.includes('(') || s.includes(')')){

        const tokens = s.split(/[()]/);
        s = tokens.join("_");
    }

    if(s.includes('{') || s.includes('}')){

        const tokens = s.split(/[{}]/);
        s = tokens.join("_");
    }

    if(s.includes(';')) {
        s = s.replace(/;/gi, "");
    }

    return s;
}
