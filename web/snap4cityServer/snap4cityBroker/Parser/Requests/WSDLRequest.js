var fs = require('fs');
const soap = require('soap');
const request = require('request');
var Parser = require('../Classes/Parser');
var Rule = require('../Classes/Rule');
var CSVSelector = require('../Classes/CSVSelector');
var XMLSelector = require('../Classes/XMLSelector');
var SMSelector = require('../Classes/SMSelector');
var Sensor_Type = require('../Classes/Sensor_Type');

process.env['NODE_TLS_REJECT_UNAUTHORIZED'] = '0';

var sqlite3 = require('sqlite3').verbose();
var db = new sqlite3.Database('Database.db');

var wsdl_options = {
    cert: fs.readFileSync(__dirname + '/../certificates/snap4cityD.pem'),    //path to pem
    key: fs.readFileSync(__dirname + '/../certificates/snap4cityD.key'),     //path to key
    rejectUnauthorized: false
};

module.exports = class WSDLRequest
{
    constructor(sensors, start_date, end_date) {// servono per creare la richiesta da fare alla regione lombardia
        this._sensors = sensors;
        this._start_date = start_date;
        this._end_date = end_date;

        this._json_getSensorType = JSON.parse(this._initTypesJson());
        this._json_getRealTimeData = JSON.parse(this._initRealTimeDataJson());
        //console.log("%j", this._json_getSensorType);
        //console.log("%j", this._json_getRealTimeData);
    }

    performRequest() { //chiamiamo il metodo performRequest che esegue il suo interno

        let self = this;
        let query = "select id, selector, basicType, vt, unit from ExtractionRule where id in " +
            "(select er from DeviceRuleAssociation where der in " +
            "(select name from DeviceExtractionRule where device in " +
            "(select name from device where name =\"Milano_vialeMarche\" or name=\"Milano_Lambrate\" )))";

        db.serialize(function() {
            let ids = [];
            let selectors = [];
            let basicTypes = [];
            let vts = [];
            let units = [];

            db.each(query,
                function (err, row) {
                    ids.push(row["id"]);
                    selectors.push(JSON.parse(row["selector"]));
                    basicTypes.push(row["basicType"]);
                    vts.push(row["vt"]);
                    units.push(row["unit"]);
                },
                function (err,  count) {

                    if (count === 0){
                        console.log("No result for this query");
                        return;
                    }

                    self._getRealTimeDataFromAPI(ids, selectors, basicTypes, vts, units);// questa funzione ci serve per fare la chiamata alla regione lombardia
                });
        });
    }

    /*
     * soap client is used to get the sensor data values from "Dati" wsdl
     * then we have to update data in Orion
     */
    _getRealTimeDataFromAPI(ids, selectors, basicTypes, vts, units) {

        let self = this;

        // soap client
        soap.createClient("https://remws.e015.arpalombardia.it/Dati.svc?wsdl",
            {request: request, wsdl_options},
            function (err, client) {
                if (err) {
                    console.log("1) Error Occurred!!");
                    console.log(err);
                }
                else {
                    let dictionary = {};

                    client.RendiDatiTempoReale(self.json_getRealTimeData, wsdl_options,
                        function (err, result, rawResponse, soapHeader, rawRequest) {
                            let r;

                            for (let i = 0; i < selectors.length; i++)
                            {
                                //Creating parser and adding our rules
                                let p = new Parser();
                                for (let j = 0; j < selectors[i].length; j++) {
                                    p.addObjRule(selectors[i][j]);
                                }
                                r = p.applyRules(rawResponse);

                                if(Array.isArray(r)){

                                    dictionary[ids[i] + "_ts_vt"] = vts[i].split(",")[0].trim();
                                    dictionary[ids[i] + "_ts_basicType"] = basicTypes[i].split(",")[0].trim();
                                    //dictionary[ids[i] + "_ts_unit"] = units[i].split(",")[0].trim();

                                    dictionary[ids[i] + "_value_vt"] = vts[i].split(",")[1].trim();
                                    dictionary[ids[i] + "_value_basicType"] = basicTypes[i].split(",")[1].trim();
                                    //dictionary[ids[i] + "_value_unit"] = units[i].split(",")[1].trim();

                                    dictionary[ids[i] + "_val_vt"] = vts[i].split(",")[2].trim();
                                    dictionary[ids[i] + "_val_basicType"] = basicTypes[i].split(",")[2].trim();
                                    //dictionary[ids[i] + "_val_unit"] = units[i].split(",")[2].trim();

                                    dictionary[ids[i] + "_ts"] = r[0];//value del timestamp
                                    dictionary[ids[i] + "_value"] = r[1];//value di velocita temp e direzione ecc..
                                    dictionary[ids[i] + "_val"] = r[2];
                                }
                                else {
                                    dictionary[ids[i] + "_vt"] = vts[i].trim();
                                    dictionary[ids[i] + "_basicType"] = basicTypes[i].trim();
                                    //dictionary[ids[i] + "_unit"] = units[i].trim();

                                    dictionary[ids[i]] = r;
                                }
                            }

                            //console.log(dictionary);

                            // Update data on Orion
                            self._update_orion(request, dictionary, ids);
                    });
                }
        });
    }

    _update_orion(request, dictionary, ids) {

        for (let i = 0; i < this.sensors.length; i++)
        {
            request.post('http://159.149.129.184:1026/v2/entities/' + this.sensors[i] + '/attrs', {
                json: {
                    [dictionary["sensor_" + this.sensors[i] + "_value_vt"]]: {
                        "type":  dictionary["sensor_" + this.sensors[i] + "_value_basicType"],
                        "value": dictionary["sensor_" + this.sensors[i] + "_value"],
                        "metadata": {}
                    },

                    [dictionary["sensor_" + this.sensors[i] + "_ts_vt"]]: {
                        "type": dictionary["sensor_" + this.sensors[i] + "_ts_basicType"],
                        "value": dictionary["sensor_" + this.sensors[i] + "_ts"],
                        "metadata": {}
                    },

                    [dictionary["sensor_" + this.sensors[i] + "_val_vt"]]: {
                        "type":  dictionary["sensor_" + this.sensors[i] + "_val_basicType"],
                        "value": dictionary["sensor_" + this.sensors[i] + "_val"],
                        "metadata": {}
                    }
                }
            }, (error, response, body) => {

                if (error) {
                    console.error(error);
                    return
                }

                console.log("status code: " + response.statusCode);
            });
        }
    }

    get sensors() {
        return this._sensors;
    };

    get start_date() {
        return this._start_date;
    }

    get end_date() {
        return this._end_date;
    }

    get json_getSensorType (){
        return this._json_getSensorType;
    }

    get json_getRealTimeData (){
        return this._json_getRealTimeData;
    }

    _initTypesJson() {

        let json_sensorTypeString =
            '{"xInput":' +
            '{"ElencoSensori":' +
            '{"Sensori":'+
            '{"IdSensore":[';

        for(let i = 0; i < this.sensors.length; i++) {
            json_sensorTypeString +=
                '"' + + this.sensors[i] + '"';
            if(i !== this.sensors.length - 1)
                json_sensorTypeString += ',';
        }
        json_sensorTypeString += ']}}}}';

        return json_sensorTypeString;
    }

    _initRealTimeDataJson() {

        let json_realTimeDataString =
            '{"xInput":' +
            '{"RendiDatiTempoReale":' +
            '{"Sensore":[';

        for(let i = 0; i < this.sensors.length; i++) {
            json_realTimeDataString += '{' +
                '"IdSensore":' + '"' + this.sensors[i] + '",' +
                '"IdFunzione":"1",' +
                '"IdOperatore":"1",' +
                '"IdPeriodo":"1",' +
                '"DataInizio":' + '"' + this.start_date + '",' +
                '"DataFine":' + '"' + this.end_date + '"}';
            if(i !== this.sensors.length - 1)
                json_realTimeDataString += ',';
        }
        json_realTimeDataString += ']}}}';

        return json_realTimeDataString;
    }
};
