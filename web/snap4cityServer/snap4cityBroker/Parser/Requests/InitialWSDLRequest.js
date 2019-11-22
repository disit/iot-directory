var fs = require('fs');
var Parser = require('../Classes/Parser');
var Rule = require('../Classes/Rule');
const soap = require('soap');
var XMLSelector = require('../Classes/XMLSelector.js');
var SMSelector = require('../Classes/SMSelector.js');
var CSVSelector = require('../Classes/CSVSelector.js');
var utmObj = require('utm-latlng');
var Sensor = require('../Classes/Sensor');
var InsertRules = require('./InsertRules');
request = require('request');

var sqlite3 = require('sqlite3').verbose();
var db = new sqlite3.Database('Database.db');

var wsdl_options = {
    cert: fs.readFileSync(__dirname + '/../certificates/snap4cityD.pem'),    //path to pem
    key: fs.readFileSync(__dirname + '/../certificates/snap4cityD.key'),     //path to key
    rejectUnauthorized: false
};

var json_input = {
    "xInput": {
        "ElencoSensori": {
            "TipoSensore": {
                "IdTipoSensore": [
                    "12"
                ]
            }
        }
    }
};

let province = ["AL","BG","BS","CO","CR","LC","LO","MN","MI","MB","NO","PV","RO","SO","VA","VR"];
let sensorType = ["4","131","145","106","148","101","102","170","146","10","1","105","144","109","107","110","147","104","117","2","12","3","11","9"];

module.exports = {

    updateDb: function () {

        return new Promise(function (resolve, reject) {

            soap.createClient("https://remws.e015.arpalombardia.it/Anagrafica.svc?wsdl",
                {request: request, wsdl_options},
                function (err, client) {
                    if (err) {
                        console.log("1) Error Occurred!!");
                        console.log(err);
                    } else {

                        for (let i = 0; i < province.length; i++) {

                            var json_elencoComuni = {
                                "xInput": {
                                    "ElencoComuni": {
                                        "Province" : {
                                            "Provincia": province[i]
                                        }
                                    }
                                }
                            };

                            client.ElencoComuni(json_elencoComuni, wsdl_options,
                                function (err, result, rawResponse, soapHeader, rawRequest) {

                                    if (err) {
                                        reject('error in request');
                                        return;
                                    }

                                    let parser = new Parser();
                                    parser.addRule(new XMLSelector("//Comune/@Id", -1), Rule.Formats.XML);
                                    let idComuni = parser.applyRules(rawResponse);

                                    //console.log(idComuni);

                                    let json_elencoSensori = initElencoSensoriJson(idComuni);
                                    json_elencoSensori = JSON.parse(json_elencoSensori);
                                    //console.log("%j", json_elencoSensori);

                                    let start = new Date();

                                    client.ElencoSensori(json_elencoSensori, wsdl_options,
                                        function (err, result, rawResponse, soapHeader, rawRequest) {

                                            if(err) {
                                                reject('2) error in request');
                                                return;
                                            }

                                            let end = new Date() - start;
                                            console.log("Total seconds: " + end/1000);

                                            let sensors = extractElements(rawResponse);
                                            console.log(sensors);

                                            if(sensors != null) {
                                                insertData(sensors);
                                                InsertRules.insert();
                                            }
                                        });

                                    resolve(null);
                                });
                        }

                        /*
                        client.ElencoSensori(json_input, wsdl_options,
                            function (err, result, rawResponse, soapHeader, rawRequest) {

                                if (err) {
                                    reject('error in request');
                                    return;
                                }

                                let sensors = extractElements(rawResponse);

                                insertData(sensors);

                                resolve(sensors);
                            });
                        */
                    }
                });
        });
    }
};

function extractElements(rawResponse) {

    let sensors = [];

    let parser = new Parser();
    parser.addRule(new XMLSelector("//Anagrafica/IdSensore", -1), Rule.Formats.XML);
    let sensors_id = parser.applyRules(rawResponse);

    if(sensors_id === null){
        console.log("Zero sensori con questi parametri");
        return null;
    }

    parser = new Parser();
    parser.addRule(new XMLSelector("//Anagrafica/Stato/@NomeStato", -1), Rule.Formats.XML);
    let sensors_state = parser.applyRules(rawResponse);

    parser = new Parser();
    parser.addRule(new XMLSelector("//Anagrafica/NomeSensore", -1), Rule.Formats.XML);
    let sensors_name = parser.applyRules(rawResponse);

    parser = new Parser();
    parser.addRule(new XMLSelector("//Anagrafica/Frequenza", -1), Rule.Formats.XML);
    let frequencies = parser.applyRules(rawResponse);

    parser = new Parser();
    parser.addRule(new XMLSelector("//Anagrafica/UnitaMisura", -1), Rule.Formats.XML);
    let units = parser.applyRules(rawResponse);

    parser = new Parser();
    parser.addRule(new XMLSelector("//Anagrafica/Quota", -1), Rule.Formats.XML);
    let altitudes = parser.applyRules(rawResponse);

    parser = new Parser();
    parser.addRule(new XMLSelector("//Anagrafica/TipoSensore/@NomeTipoSensore", -1), Rule.Formats.XML);
    let types = parser.applyRules(rawResponse);

    parser = new Parser();
    parser.addRule(new XMLSelector("//Anagrafica/Stazione/@NomeStazione", -1), Rule.Formats.XML);
    let devices = parser.applyRules(rawResponse);

    parser = new Parser();
    parser.addRule(new XMLSelector("//Anagrafica/UTM_Nord", -1), Rule.Formats.XML);
    let utm_nord = parser.applyRules(rawResponse);

    parser = new Parser();
    parser.addRule(new XMLSelector("//Anagrafica/UTM_Est", -1), Rule.Formats.XML);
    let utm_est = parser.applyRules(rawResponse);

    parser = new Parser();
    parser.addRule(new XMLSelector("//Sensore/DatiDisponibili/TempoReale/Dato[1]/@IdOperatore", -1), Rule.Formats.XML);
    let operation_id = parser.applyRules(rawResponse);

    parser = new Parser();
    parser.addRule(new XMLSelector("//Sensore/DatiDisponibili/TempoReale/Dato[1]/@IdFunzione", -1), Rule.Formats.XML);
    let function_id = parser.applyRules(rawResponse);

    if(function_id === null || operation_id === null) {
        function_id = [];
        operation_id = [];
    }

    if(altitudes === null)
        altitudes = [];

    for(let i = 0; i < sensors_id.length; i++) {

        let utm = new utmObj();

        let latLon = {};
        if(utm_est[i] === null || utm_nord[i] === null){
            latLon['lat'] = null;
            latLon['lng'] = null;
        }
        else
            latLon = utm.convertUtmToLatLng(utm_est[i], utm_nord[i], 32, 'N');


        if (function_id.length === 0 || typeof function_id[i] === 'undefined')
            function_id[i] = null;
        if (operation_id.length === 0 || typeof operation_id[i] === 'undefined')
            operation_id[i] = null;
        if (altitudes.length === 0 || typeof altitudes[i] === 'undefined')
            altitudes[i] = null;

        sensors[i] = new Sensor(sensors_id[i], types[i], frequencies[i], latLon['lat'], latLon['lng'], sensors_name[i],
            altitudes[i], sensors_state[i], devices[i], function_id[i], operation_id[i], units[i]);
    }

    return sensors;
}

function insertData(sensors) {

    for(let i = 0; i < sensors.length; i++) {

        db.run(
            'INSERT OR REPLACE INTO Sensor (sensor_id, name, vt, u, freq, device, latitude, longitude, state, function_id, operator_id)' +
            'VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [sensors[i].id, sensors[i].name, sensors[i].sensorType, sensors[i].measureType, sensors[i].frequency, sensors[i].device, sensors[i].lat,
                sensors[i].lon, sensors[i].state, sensors[i].functionId, sensors[i].operatorId]
        )
    }
}

function initElencoSensoriJson(idComuni) {

    let json_sensorTypeString =
        '{"xInput":' +
        '{"ElencoSensori":' +
        '{"Comuni":'+
        '{"IdComune":[';

    for(let i = 0; i < idComuni.length; i++) {
        json_sensorTypeString +=
            '"' + + idComuni[i] + '"';
        if(i !== idComuni.length - 1)
            json_sensorTypeString += ',';
    }
    json_sensorTypeString += ']},'; //'}}}';
    //json_sensorTypeString += '"TipoSensore":' +
    //    '{"IdTipoSensore":"11"}}}}';

    json_sensorTypeString +=
        '"TipoSensore":' +
        '{"IdTipoSensore":[';

    for(let i = 0; i < sensorType.length; i++){
        json_sensorTypeString +=
            '"' + sensorType[i] + '"';
        if(i !== sensorType.length - 1)
            json_sensorTypeString += ',';
    }
    json_sensorTypeString += ']}}}}';

    /*let jsonProva = '{"xInput":' +
        '{"ElencoSensori":' +
        '{"Comuni":' +
        '{"IdComune":[' +
        '"380", "381"]' + '},' +
        '"TipoSensore":"5"}}}';

    console.log("%j", JSON.parse(jsonProva));*/

    return json_sensorTypeString;
}
