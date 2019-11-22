var fs = require('fs');
var Parser = require('../Classes/Parser');
var Rule = require('../Classes/Rule');
var Sensor_Type = require('../Classes/Sensor_Type');
const soap = require('soap');
var XMLSelector = require('../Classes/XMLSelector.js');
var SMSelector = require('../Classes/SMSelector.js');
var CSVSelector = require('../Classes/CSVSelector.js');
var Sensor = require('../Classes/Sensor');
request = require('request');
var schedule = require('node-schedule');

var sqlite3 = require('sqlite3').verbose();
var db = new sqlite3.Database('Database.db');

const DAY_IN_MILLISECONDS = 24*60*60*1000;
const HOUR_IN_MILLISECONDS = 60*60*1000;

let wsdl_options = {
    cert: fs.readFileSync(__dirname + '/../certificates/snap4cityD.pem'),    //path to pem
    key: fs.readFileSync(__dirname + '/../certificates/snap4cityD.key'),     //path to key
    rejectUnauthorized: false
};

module.exports = {

    updateRealDataInOrion: function(){

        const promise = getAllActiveSensorsAndRulesFromDb();

        promise.then(function (allActiveSensors) {

            console.log(allActiveSensors.length);

            let minute_rule = new schedule.RecurrenceRule();
            minute_rule.second = 10;                                    //ogni volta che scatta il secondo "10" aggiorno
            let fiveMinutes_rule = new schedule.RecurrenceRule();
            fiveMinutes_rule.minute = new schedule.Range(1, 59, 5);     //ogni 5 minuti (dal minuto "01") aggiorno
            let tenMinutes_rule = new schedule.RecurrenceRule();
            tenMinutes_rule.minute = new schedule.Range(3, 59, 10);     //ogni 10 minuti (dal minuto "03") aggiorno
            let thirtyMinutes_rule = new schedule.RecurrenceRule();
            thirtyMinutes_rule.minute = new schedule.Range(5, 59, 30);  //ogni 30 minuti (dal minuto "05") aggiorno
            let hour_rule = new schedule.RecurrenceRule();
            hour_rule.hour = new schedule.Range(0, 23, 1);
            let twoHours_rule = new schedule.RecurrenceRule();
            let threeHours_rule = new schedule.RecurrenceRule();
            let fourHours_rule = new schedule.RecurrenceRule();
            let day_rule = new schedule.RecurrenceRule();


            schedule.scheduleJob(minute_rule, function () {
                console.log("ogni minuto vengo eseguito");
            });

            schedule.scheduleJob(fiveMinutes_rule, function () {
                console.log("ogni 5 minuti vengo eseguito");
            });


        }).catch(function (message) {
            console.log("Promise rejected: " + message);
        });
    },

    performRequest: function() {

        let sDate = new Date(new Date().getTime() - DAY_IN_MILLISECONDS);
        let currentDate = formatDate(new Date());
        let startDate = formatDate(sDate);
        let query = "select * from ExtractionRule where id in" +
            "(select er from DeviceRuleAssociation where der in" +
            "(select name from DeviceExtractionRule where device in" +
            "(select name from Device where device = \"Milano - via Senato\" AND name in" +
            "(select device from Sensor))))";


        db.serialize(function() {

            let ids = [];
            let selectors = [];
            let basicTypes = [];
            let vts = [];
            let units = [];
            let dictIdTuple = {};

            db.each(query,
                function (err, row) {
                    ids.push(row["id"]);
                    selectors.push(JSON.parse(row["selector"]));
                    basicTypes.push(row["basicType"]);
                    vts.push(row["vt"]);
                    units.push(row["unit"]);

                    dictIdTuple[row["id"].split("sensor_")[1]] = row;
                },
                function (err, count) {

                    if (count === 0){
                        console.log("No result for this query");
                        return;
                    }

                    for(let key in dictIdTuple){
                        let jsonVar = dictIdTuple[key]["selector"];
                        jsonVar = JSON.parse(jsonVar);
                        dictIdTuple[key]["selector"] = jsonVar;
                    }

                    //console.log(dictIdTuple);

                    let query2 = "select * from Sensor where";

                    for(let key in dictIdTuple) {
                        query2 += " sensor_id = \"" + key + "\" or";
                    }
                    query2 = query2.substr(0, query2.length - 4) + "\"";

                    let sensors = [];

                    let queryProva = "select * from Sensor order by sensor_id LIMIT 100";

                    db.each(queryProva,
                        function (err, row) {

                            if(row["function_id"] != null && row["operator_id"] != null
                                && row["latitude"] != null && row["longitude"] != null
                                && row["state"] === "Attivo")
                            {
                                let s = new Sensor(row["sensor_id"], row['vt'], row["freq"], row["latitude"],
                                    row["longitude"], row["name"], row["altitude"], row["state"], row["device"],
                                    row["function_id"], row["operator_id"], row["u"]);

                                sensors.push(s);
                            }
                        },
                        function (err, count) {

                            soap.createClient("https://remws.e015.arpalombardia.it/Dati.svc?wsdl",
                                {request: request, wsdl_options},
                                function (err, client) {

                                    if (err) {
                                        console.log("1) Error Occurred!!");
                                        console.log(err);
                                    }
                                    let start = new Date();

                                    for(let i = 0; i < sensors.length; i++) {

                                        let input = createInputJson(sensors[i], startDate, currentDate);

                                        client.RendiDatiTempoReale(input, wsdl_options,
                                            function (err, result, rawResponse, soapHeader, rawRequest) {

                                                let end = new Date() - start;
                                                console.log("Sensor " + sensors[i].id + " time: " + end / 1000 + "s");

                                                let p = new Parser();
                                                for (let j = 0; j < selectors[i].length; j++) {
                                                    p.addObjRule(selectors[i][j]);
                                                }
                                                let r = p.applyRules(rawResponse);

                                                console.log(r);
                                            });
                                    }
                                });
                        }
                    );
                })
        });
    }
};

function getAllActiveSensorsAndRulesFromDb() {

    return new Promise(function (resolve, reject) {

        db.serialize(function () {

            let sensors = [];
            let firstQuery = "SELECT * " +
                "FROM Sensor " +
                "WHERE state = 'Attivo' and function_id is not NULL";
            
            db.each(firstQuery,
                function (err, row) {

                    let s = new Sensor(row["sensor_id"], row['vt'], row["freq"], row["latitude"],
                        row["longitude"], row["name"], row["altitude"], row["state"], row["device"],
                        row["function_id"], row["operator_id"], row["u"]);

                    sensors.push(s);
                },
                function (err, count) {

                    if(err || count === 0){
                        reject('error in query');
                        return;
                    }

                    resolve(sensors);
                })
        })
    });
}



function formatDate(date) {
    date = date.getFullYear() + "-" +
        ((date.getMonth() + 1) < 10 ? '0' : '') + (date.getMonth() + 1) + "-" +
        (date.getDate() < 10 ? '0' : '') + date.getDate() + " " +
        (date.getHours() < 10 ? '0' : '') + date.getHours() + ":" +
        (date.getMinutes() < 10 ? '0' : '') + date.getMinutes() + ":" +
        (date.getSeconds() < 10 ? '0' : '') + date.getSeconds();
    return date;

}

function createInputJson(sensor, startDate, currentDate) {

    return {
        "xInput": {
            "RendiDatiTempoReale": {
                "Sensore": {
                    "IdSensore": sensor.id,
                    "IdFunzione": sensor.functionId,
                    "IdOperatore": sensor.operatorId,
                    "IdPeriodo": sensor.getIdPeriodo_From_Frequency(),
                    "DataInizio": startDate,
                    "DataFine": currentDate
                }
            }
        }
    };
}
