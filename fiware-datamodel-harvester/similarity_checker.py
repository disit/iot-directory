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
import dictionary_evaluator
import json
from dbschemahelper import DbSchemaHelper


class SimilarityChecker():
    def __init__(self, db_helper: DbSchemaHelper, s4c_dict_link):
        self.db_helper = db_helper
        self.s4c_dictionary = dictionary_evaluator.DictEval(
            db_helper, s4c_dict_link)
        self.message = ""

    # Vengono analizzati gli attributi del payload con gli attributi dello schema, o degli altri schema.
    # Quando si modifica un attributo, inserisci all'interno della voce "value_type", il value type corrispondente
    # a quello presente nel dizionario di S4C. Se c'è bisogno di aggiungere un nuovo value_type,
    # bisogna aggiungerlo proprio dal sito S4C e non qui.
    # Una volta inserito il value_type corrispondente a quell'attributo, inserire "True" in CHECKED
    def fit_value_type(self, attribute_key, schema_tuple):
        if attribute_key == "type" or attribute_key == "id":
            return None
        print(
            f"Fit 'value_type' of '{attribute_key}' in model: '{json.dumps(schema_tuple)}'")
        _model = schema_tuple[0]
        _subdomain = schema_tuple[1]
        _domain = schema_tuple[2]
        _version = schema_tuple[3]
        if self.db_helper.attribute_exists(attribute_key, _model, _subdomain, _domain, _version):
            # Controllo negli attributi dello schema
            _value_type = self._check_consistent_value_type(
                attribute_key, _model, _subdomain, _domain, _version)
            if _value_type:
                return _value_type
            # Controllo tra i value_type di s4c per trovare un nome simile
            _value_type = self._check_in_s4c_dict(
                attribute_key, _model, _subdomain, _domain, _version)
            if _value_type:
                return _value_type
        else:
            _value_type_2 = self._find_in_common_schemas(
                attribute_key, schema_tuple)
            if _value_type_2:
                return _value_type_2
        return None

    def _check_consistent_value_type(self, attribute, model, subdomain, domain, version):
        _attr = self.db_helper.get_attribute(
            attribute, model, subdomain, domain, version)
        if _attr:
            if _attr[0][4]["checked"] == "True":
                val_type = _attr[0][4]["value_type"]
                print(
                    f"\tAttribute '{attribute}' is checked. Expect to have '{val_type}' inside Snap4City dictionary")
                _temp = self.s4c_dictionary.fit_value_type(
                    val_type, silent=True)
                if isinstance(_temp, tuple):
                    print(
                        f"\tFound '{val_type}' inside Snap4City dictionary")
                    return _temp
                else:
                    print(
                        f"\tError: value_type inside attribute is wrong. Value_type: '{val_type}'. Assumed as value_type: '{val_type}'")
                    # return val_type # questo caso viene gestito sotto, quando si chiama il metodo che fa il check su s4c
                    self.message += f"[ Add this value_type {val_type} to s4c dictionary ]"
                    self.db_helper.add_rule_problem(model, subdomain, domain, version,
                                                    f"Error: {attribute} is checked but its value_type doesn't correspond to any s4c dictionary "
                                                    f"definition. MESSAGE: {self.get_message()}")

        return None

    def _find_in_common_schemas(self, attribute_key, schema_tuple):
        _common_attr = self.db_helper.get_attribute(
            attribute_key, domain="definition-schemas")
        _model = schema_tuple[0]
        _subdomain = schema_tuple[1]
        _domain = schema_tuple[2]
        _version = schema_tuple[3]
        if len(_common_attr) == 0:
            print(f"No attribute found with name '{attribute_key}'")
            self.message = f"[ No attribute found named '{attribute_key}'. ]"
            self.db_helper.append_to_logs(_model, _subdomain, _domain, _version,
                                          attribute_key, f"Added '{attribute_key}', not present in schema.")
            # self.db_helper.append_to_logs(_model, _subdomain, _domain, _version, attribute_key, "s4c_rule checked False")
        elif len(_common_attr) > 0:
            while len(_common_attr) > 0:
                _cm_attr = _common_attr.pop()
                _cm_model = _cm_attr[0]
                _cm_subdomain = _cm_attr[1]
                _cm_domain = _cm_attr[2]
                _cm_version = _cm_attr[3]
                _cm_attr = _cm_attr[4]
                if _cm_attr["checked"] == "True":
                    _res = self.s4c_dictionary.fit_value_type(
                        _cm_attr["value_type"], True)
                    if isinstance(_res, tuple):
                        return _res
                    else:
                        self.message += f"[ Add this value_type {_cm_attr['value_type']} to s4c dictionary ]"
                        self.db_helper.add_rule_problem(_cm_model, _cm_subdomain, _cm_domain, _cm_version,
                                                        f"Error: {attribute_key} is checked but its value_type doesn't correspond to any s4c dictionary "
                                                        f"definition. MESSAGE: {self.get_message()}")
                else:
                    # Imposto returnList a True, così se trovo più di un elemento in s4c dict, questo viene aggiunto
                    # ai possibili value_type.
                    _res = self._check_in_s4c_dict(
                        attribute_key, _cm_model, _cm_subdomain, _cm_domain, _cm_version, returnList=True)
                    # if isinstance(_res, list):
                    # Nel caso io abbia una lista, è perché ho trovato più value_type
                    if isinstance(_res, tuple):
                        # Se è una tuple, il metodo ha già assegnato un value_type usando check_in_s4c.
                        # Allora è stato assegnato il value_type e questo viene restituito sotto forma di tuple
                        return _res

    # Entro qui se non ho un value type checked. Se trovo un solo value_type, lo assegno e metto checked, ma salvo che ho fatto
    # questo assegnamento

    def _check_in_s4c_dict(self, attribute_key, model, subdomain, domain, version, returnList=False):
        _pool = self.s4c_dictionary.fit_value_type(attribute_key)
        # Pool è una tupla se trovo esattamente quello che ha lo stesso nome,
        # oppure Pool è una lista di possibilità.
        if isinstance(_pool, list):
            if len(_pool) == 1:
                _instance = _pool[0]
                self.db_helper.update_attribute_field(model, subdomain, domain, version, attribute_key, "checked",
                                                      "True")
                self.db_helper.update_attribute_field(model, subdomain, domain, version, attribute_key, "value_type",
                                                      _instance[0])
                self.message += f'[ Assigned value_type found in S4C Dictionary (name|id), and set Checked \'True\': {_instance} ]'
                self.db_helper.append_to_logs(
                    model, subdomain, domain, version, attribute_key, self.get_message())
                return _instance
            elif len(_pool) > 1:
                _attrs = json.dumps(_pool)
                self.message += f'[ Found more definitions in S4C Dictionary (name|id): {_attrs} ]'
                self.db_helper.add_rule_problem(model, subdomain, domain, version,
                                                f"Error: no value_type found for attribute {attribute_key}. MESSAGE: {self.get_message()}")
                if returnList:
                    return _pool
            elif len(_pool) == 0:
                self.message += f'[ {attribute_key} is not defined in S4C Dictionary  ]'
                self.db_helper.add_rule_problem(model, subdomain, domain, version,
                                                f"Error: no value_type found for attribute {attribute_key}. MESSAGE: {self.get_message()}")

        elif isinstance(_pool, tuple):
            _instance = _pool
            self.db_helper.update_attribute_field(
                model, subdomain, domain, version, attribute_key, "checked", "True")
            self.db_helper.update_attribute_field(
                model, subdomain, domain, version, attribute_key, "value_type", _instance[0])
            self.message += f'[ Assigned value_type found in S4C Dictionary (name|id), and set Checked \'True\': {_instance} ]'
            # aggiungi questo su attrLog
            return _instance

        return None

    def get_message(self):
        _temp = str(self.message)
        self.message = ""
        return _temp
