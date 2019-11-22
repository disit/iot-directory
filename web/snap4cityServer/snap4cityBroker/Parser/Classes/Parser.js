var xpath = require('xpath');
var dom = require('xmldom').DOMParser;
var jp = require('jsonpath');
var Rule = require('./Rule.js');

var CSVSelector = require('../Classes/CSVSelector.js');
var XMLSelector = require('../Classes/XMLSelector.js');
var SMSelector = require('../Classes/SMSelector.js');
var JSONSelector = require('../Classes/JSONSelector.js');

/*
 * Available formats
 */
var Formats = {"XML":1, "JSON":2, "CSV":3, "STRING_MANIPULATION": 4};
Rule.Formats = Formats;

/*
 * Parser class
 */
module.exports = class Parser {
    /*
     * Empty constructor
     */
    constructor() {
        this.rules = []
    }

    /*
     * Adds a rule in the parser
     */
    addRule(rule, format) {
        this.rules.push(new Rule(rule, format));
    }

    /*
     * Clear rules in the parser
     */
    clearRules() {
        this.rules = [];
    }

    /*
     * Adds a rule in the parser with the json file read from the database
     */
    addObjRule(obj,type) {
        switch (type)
        {
            case "CSV":
                this.addRule(new CSVSelector(obj["param"]["s"], obj["param"]["i"]), Rule.Formats.CSV);
                break;
            case "XML":
                this.addRule(new XMLSelector(obj["param"]["s"], obj["param"]["i"]), Rule.Formats.XML);
                break;
            case "SM":
                this.addRule(new SMSelector(obj["param"]["s"], obj["param"]["i"]), Rule.Formats.STRING_MANIPULATION);
                break;
            case "JSON":
                this.addRule(new JSONSelector(obj["param"]["s"], obj["param"]["i"]), Rule.Formats.JSON);
                break;
				
        }
    }

    /*
     * Applies the previous added rules
     */
    applyRules(d) {
        let _this = this;
        this.rules.forEach(function (r) {
            d = _this.applyRule(d, r);
        });
        return d;
    }

    /*
     * Applies the rule r to the data d
     */
    applyRule(d, r) {
        switch (r.format) {
            case Rule.Formats.XML:
                var nodes;
                var attributes = [];

                // If there is an attribute
                if(r.selector.path.includes("@")) {

                    //attribute = xpath.select1(r.selector.path, new dom().parseFromString(d));
                    attributes = xpath.select(r.selector.path, new dom().parseFromString(d));

                    if(attributes.length === 0) {
                        console.log("Path error");
                        return null;
                    }

                    if(r.selector.indexes >= 0) {

                        return attributes[r.selector.indexes].value;
                    }
                    else {
                        let res = [];
                        for (let i = 0; i < attributes.length; i++) {
                            res[i] = attributes[i].value;
                        }

                        if (res.length === 1)
                            return res[0];

                        return res;
                    }
                }
                else {
                    nodes = xpath.select(r.selector.path, new dom().parseFromString(d));

                    if(nodes.length === 0) {
                        console.log("Path error: length nodes = 0");
                        return null;
                    }
                    else {
                        if (r.selector.indexes >= 0)
                        {
                            // Returns an array containing the string of the elements with the index given in input
                            return nodes[r.selector.indexes].firstChild.data;
                        }
                        else
                        {
                            // Returns an array of the strings of all elements
                            let e = [];
                            for (let i = 0; i < nodes.length; i++) {
                                if(nodes[i].firstChild === null)
                                    e.push(null);
                                else
                                    e.push(nodes[i].firstChild.data);
                            }
                            return e;
                        }
                    }
                }
            case Rule.Formats.JSON:
                return jp.query(JSON.parse(d), r.selector.path);
            case Rule.Formats.CSV:
                for (let i = 0; i < r.selector.separators.length; i++) {
                    if (Array.isArray(d)) {
                        let a = [];
                        d.forEach(function (row) {
                            a.push(row.split(r.selector.separators[i]));
                        });

                        d = a;
                        //d = [].concat.apply([], a); //convert array of array to a simple array

                    } else {
                        d = d.split(r.selector.separators[i]);
                    }

                    if (r.selector.indexes[i] !== 0) {
                        let index = (r.selector.indexes[i] > 0) ? r.selector.indexes[i] - 1 : d.length + r.selector.indexes[i];
                        d = d[index];
                    }
                }
                return d;
            case Rule.Formats.STRING_MANIPULATION:
                switch(r.selector.function)
                {
                    case "trim":
                        if (Array.isArray(d))
                        {
                            let a = [];
                            for (let i = 0; i < d.length; i++)
                                a.push(d[i].trim());
                            return a;
                        }
                        else
                            return d.trim();
                }
                return d;
            default:
                console.log("Errore, rule: " + r.format + " not recognized");
        }
    }
};
