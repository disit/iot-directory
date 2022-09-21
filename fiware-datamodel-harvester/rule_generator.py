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

import random

import similarity_checker

# Avendo un payload validato, procedo a generare il value_type. Se lo trovo, creo una regola per quel dispositivo


class RuleGenerator():
    def __init__(self, database, dict_link):
        self.sim_checker = similarity_checker.SimilarityChecker(
            database, dict_link)
        self.db_helper = database

# Rule is a tuple:
    # Rule name
    # [{field:"", operator:"",value:""}..] - fields : [cb, device, deviceType, value_name, data_type, model, value_type, value_unit ]
    # [{field:"", valueThen:""}...]
    # Organization
    # time
    # mode (1)
    # contextbroker
    # service
    # servicePath
###
    def _gen_if(self, field=None, operator=None, value=None):
        return {"field": field, "operator": operator, "value": value}

    def _gen_then(self, field=None, value=None):
        return {"field": field, "valueThen": value}

    def _check_valid_dict(self, _dict):
        if None in _dict.values():
            return False
        return True

   # def create_rule(self, payload: dict, context_brocker, multitenancy, service, servicePath, organization, prefix):
    def create_general_rules(self, _domain, _subdomain, _model, _attr):
        if _attr["checked"] == "True":
            _rule_name = _domain + "_" + _subdomain + "_" + _model + "_" + \
                _attr["value_name"] + str((100*random.random()).__round__())
        else:
            _rule_name = _domain + "_" + _subdomain + \
                "_" + _model + "_" + _attr["value_name"]
        _ifs = []
        _thens = []
        _ifs.append(self._gen_if("value_name", "IsEqual", _attr["value_name"]))
        _thens.append(self._gen_then("value_type", _attr["value_type"]))
        _thens.append(self._gen_then("value_unit", _attr["value_unit"]))
        _thens.append(self._gen_then("data_type", _attr["data_type"]))
        print(f"Generated rule with ifs  {_ifs}\n and  {_thens}\n ")
        _rule = [_rule_name, _ifs, _thens, '', '', '']
        print(f"Generated rule for value_type of {_attr['value_name']}\n")
        return _rule

    def create_rule(self, payload: dict, context_brocker, multitenancy, service, servicePath, organization, prefix):
        _payload = payload[0][0]
        _metadata = payload[0][1]
        _schema_tuple = payload[1]
        _device = _payload["id"]
        _organization = organization
        _context_broker = context_brocker

        if multitenancy:
            _service = service
            _servicePath = servicePath
        _rules = []
        _needed_rules = len(_payload) - 2
        _rules_created = 0
        print(f"\nCreating rules for device '{_device}'")
        for attribute in _payload.keys():
            _create_rule = False
            value_type = self.sim_checker.fit_value_type(
                attribute, _schema_tuple)
            if isinstance(value_type, tuple):  # Quando trovo un match con id
                _create_rule = True
            elif attribute not in ["type", "id"]:
                print(f"No value_type found for '{attribute}'")

            if _create_rule:
                _rule_name = prefix+_device+f"-{attribute}"
                _ifs = []
                _thens = []
                _ifs.append(self._gen_if("cb", "IsEqual", _context_broker))
                _ifs.append(self._gen_if("model", "IsEqual", _payload["type"]))
                _ifs.append(self._gen_if("device", "IsEqual", _payload["id"]))
                _ifs.append(self._gen_if("value_name", "IsEqual", attribute))
                _thens.append(self._gen_then("value_type",
                                             value_type[0] if isinstance(value_type, tuple) else value_type)
                              )
                # if attribute in _metadata.keys():
                #    if "unit" in _metadata[attribute].keys():
                # Devo creare una nuova regola che vincola questo unit?
                # Potrebbe essere non necessario
                #        _thens.append(self._gen_then("value_unit", _metadata[attribute]["unit"]))
                _rule = [_rule_name, _ifs, _thens,
                         _organization, _context_broker]
                if multitenancy:
                    _rule.append(service)
                    _rule.append(servicePath)
                _rule.append(_device)
                # Devo creare una regola per ognuno degli attributi.
                _rules.append(tuple(_rule))
                print(f"Generated rule for value_type of {attribute}\n")
                _rules_created += 1
            else:
                print(
                    f"Unable to generate rule for value_type of {attribute}\n")
        print(
            f"Numer of rules created for '{_device}': {_rules_created} | Expected {_needed_rules} \n")
        return _rules
