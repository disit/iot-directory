#Snap4City: IoT-Directory
# Copyright (C) 2017 DISIT Lab https://www.disit.org - University of Florence
# This program is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License
# as published by the Free Software Foundation; either version 2
# of the License, or (at your option) any later version.
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software
# Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.

import parser_models as pm
import json
import requests
import ast
import rule_generator


class PayloadsIngestor():
    def __init__(self, database, dict_link):
        self.database = database
        self.model_parser = pm.Parser(database)
        self.rule_generator = rule_generator.RuleGenerator(database, dict_link)

    # curl -H "Fiware-Service:Tampere" https://context.tampere.fiware.cityvision.cloud:443/v2/entities
    def open_link(self, link: str, header=""):
        if header:
            _h = ast.literal_eval(header)
            r = requests.get(link, headers=_h)
        else:
            r = requests.get(link)
        r = r.json()
        if len(r) > 0:
            self.model_parser.set_payloads(r, True)
            _triple = self.model_parser.get_results()
            return _triple
        return [[], [], []]

    def open_payloads_file(self, payloads_file: str):
        _list = []
        with open(payloads_file, encoding="utf8") as file:
            a = json.load(file)
            if type(a) is list:
                _list = a
        if len(_list) > 0:
            self.payloads_list.append(_list)
            self.model_parser.set_payloads(_list, True)
        _triple = self.model_parser.get_results()
        return _triple

    def analize_results(self, triple, context_broker, multitenancy, service, servicePath, organization, prefix: str):
        _correct_payloads = triple[0]
        _uncorrect_payloads = triple[1]
        _error_thrown = triple[2]
        _itr = 0
        while _itr < len(_correct_payloads):
            _rules = self.rule_generator.create_rule(_correct_payloads[_itr],
                                                     context_brocker=context_broker,
                                                     multitenancy=multitenancy,
                                                     service=service, servicePath=servicePath, organization=organization, prefix=prefix)
            for _rule in _rules:
                self.database.add_rule(_rule, multitenancy)
            _itr += 1
        #_messages = "Payload Analysis results\n"
        #_iterator = len(_uncorrect_payloads) - 1
        # while _iterator >= 0:
        #    _err = _error_thrown[_iterator]
        #    _messages += "ID: " + _err.instance["id"] + "\tMessage: '"
        #    _messages += _err.message + "'\n"
        #    _iterator -= 1
        #statics.create_folders([self.results_folder + "Payload-Ingestor/"])
        # with open(self.results_folder + "Payload-Ingestor/results.txt", "w", encoding="utf8") as results:
        #    results.write(_messages)

    def clean_payloads(self):
        self.payloads_list = []
