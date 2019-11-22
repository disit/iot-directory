const request = require('request');

var fs = require('fs');
var Parser = require('../Classes/Parser');
var Rule = require('../Classes/Rule');
var JSONSelector = require('../Classes/JSONSelector.js');

request.get({
	    url: "https://muoversi2015.e015.servizirl.it/planner/rest/soluzioniJson/e015Search/?param=milano%20affori%20m3&maxResult=10",
	    agentOptions: {
	        cert: fs.readFileSync(__dirname + '/../certificates/snap4cityD.pem'),    //path to pem
		    key: fs.readFileSync(__dirname + '/../certificates/snap4cityD.key'),     //path to key
			rejectUnauthorized: false
	    },
	}, function (error, response, body) {
		if (error != null)
			console.log(error);
		else
		{
			console.log(body);
			let parser = new Parser();
            parser.addRule(new JSONSelector("$..points[0:5].label"), Rule.Formats.JSON);
			console.log(parser.applyRules(body));
		}
	});