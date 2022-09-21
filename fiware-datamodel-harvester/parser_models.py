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

from jsonschema import validate
from jsonschema import ValidationError
import statics
import re

# Set a list of payloads by using set_payloads, or add one per one by using append_payload
# Each of them, will be converted into keyValue payload


class Parser():
    def __init__(self, db_helper):
        self.raw_payloads = []
        self.raw_metadata = []
        self.validated_payloads = []
        self.unvalidated_payloads = []
        self.db_helper = db_helper

    def _correct_payload(self, payload):
        if statics.is_normalized(payload):
            return payload, None
        else:
            res = statics.normalized_2_keyvalue(payload)
            self.raw_metadata.append(res[1])
            return res[0], res[1]

    def append_payload(self, payload: dict):
        _temp = self._correct_payload(payload)
        self.raw_payloads.append(_temp)

    def set_payloads(self, payloads: list, also_execute=True):
        self.reset_parser()
        _temp = []
        for payload in payloads:
            _temp.append(self._correct_payload(payload))
        self.raw_payloads = _temp
        if also_execute:
            _unvalidated_payloads = self.execute_parsing()
            self.parse_unparsed(_unvalidated_payloads)

    def execute_parsing(self, payloads=None):
        _payloads = payloads
        _unvalidated_payloads = []
        if payloads is None:
            _payloads = self.raw_payloads
            print("Executing first parsing of payloads with respectives schema...")
        while len(_payloads) > 0:
            # _raw_payload contiene anche metadata eventuali estratti
            _raw_payload = _payloads.pop(0)
            payload = _raw_payload[0]
            _payload_model = payload["type"]
            _vers_subd_dom = self.db_helper.get_all_versions(_payload_model)
            if len(_vers_subd_dom) == 1:
                _schema_key = _vers_subd_dom[0]
                _schema = self.db_helper.get_model_schema(_payload_model)
                try:
                    validate(payload, _schema)
                    print(
                        f"Device '{payload['id']} of type '{payload['type']}': PARSED.")
                    self.validated_payloads.append(
                        ((payload, _raw_payload[1]), (_payload_model, _schema_key[1], _schema_key[2], _schema_key[0])))
                except Exception as e:
                    _unval_errors = {"unvalidated": [], "errors": []}
                    _unval_errors["unvalidated"].append(
                        (_payload_model, _schema_key[1], _schema_key[2], _schema_key[0]))
                    _unval_errors["errors"].append(e)
                    _unvalidated_payloads.append(
                        ((payload, _raw_payload[1]), _unval_errors))
                continue  # Vado al prossimo payload
            elif len(_vers_subd_dom) > 1:
                # Caso in cui ci sono più schema
                # Controllo, tramite il metodo del db, SE TUTTI GLI SCHEMA CHE HANNO LO STESSO NOME, sono uguali
                if self.db_helper.check_same_modelsName_same_schema(_payload_model):
                    _schema_key = _vers_subd_dom[0]
                    _schema = self.db_helper.get_model_schema(
                        _payload_model, _schema_key[1], _schema_key[2], _schema_key[0])
                    try:
                        validate(payload, _schema)
                        self.validated_payloads.append(
                            ((payload, _raw_payload[1]), (_payload_model, _schema_key[1], _schema_key[2], _schema_key[0])))
                    except Exception as e:
                        _unval_errors = {"unvalidated": [], "errors": []}
                        _unval_errors["unvalidated"].append(
                            (_payload_model, _schema_key[1], _schema_key[2], _schema_key[0]))
                        _unval_errors["errors"].append(e)
                        _unvalidated_payloads.append(
                            ((payload, _raw_payload[1]), _unval_errors))
                    continue  # Vado al prossimo payload
                # Ho diversi schema.json, con lo stesso model name, ma appunto con schema diversi. Vedo per quale schema valida
                else:
                    _iterator = 0
                    # Collezione di model, subdomain, domain, version  che hanno validato il payload
                    _validated = []
                    # stessa collezione di sopra, che NON hanno validato il payload, e l'errore che hanno lanciato inserendo quello schema
                    _unval_errors = {"unvalidated": [], "errors": []}
                    while _iterator < len(_vers_subd_dom):
                        # VERSION - SUBDOMAIN - DOMAIN
                        _tuple = _vers_subd_dom[_iterator]
                        _iterator += 1
                        _schema = self.db_helper.get_model_schema(
                            _payload_model, subdomain=_tuple[1], domain=_tuple[2], version=_tuple[0])
                        try:
                            validate(payload, _schema)
                            _validated.append(
                                (_payload_model, _tuple[1], _tuple[2], _tuple[0]))
                        except Exception as e:
                            _unval_errors["unvalidated"].append(
                                (_payload_model, _tuple[1], _tuple[2], _tuple[0]))
                            _unval_errors["errors"].append(e)
                    # Solo uno schema ha validato - C'era ambiguità su modello, ma è stata risolta
                    if len(_validated) == 1:
                        self.validated_payloads.append(
                            ((payload, _raw_payload[1]), _validated[0]))
                    # Nessuno degli schema ha validato.
                    elif len(_validated) == 0:
                        # Voglio controllare se i modelli che non hanno validato sono comunque uguali
                        _unvalidated_payloads.append(
                            ((payload, _raw_payload[1]), _unval_errors))
                    else:  # Più schema hanno validato payload. Sono uguali?
                        print(
                            "NEW CASE: please go in parser_model.py -> execute_parsing()")
            else:
                print(
                    f"No schema found in database for model '{_payload_model}'.")
        return _unvalidated_payloads

    # Ci sono errori che si possono semplicemente risolvere in modo autonomo per parsare i payload.
    # Quindi se riconosco l'errore, posso correggere e provare a validarlo di nuovo
    # Questo metodo è l'unico che carica i payload non validati nella corrispondente variabile di classe
    def parse_unparsed(self, unvalidated_payloads):
        while len(unvalidated_payloads) > 0:
            _raw_payload = unvalidated_payloads.pop(0)
            _metadata = _raw_payload[0][1]
            _item = (_raw_payload[0][0], _raw_payload[1])
            _payload = _item[0]
            _schemas_id = _item[1]["unvalidated"]
            _errors = _item[1]["errors"]
            if len(_schemas_id) == 1:
                # Caso in cui ho un errore e uno schema
                _schema_tuple = _schemas_id[0]
                _error = _errors[0]
                _schema = self.db_helper.get_model_schema(
                    _schema_tuple[0], _schema_tuple[1], _schema_tuple[2], _schema_tuple[3])
                if isinstance(_error, ValidationError):
                    if re.search("is a required", _error.message):
                        # Questo tipo di errore non mi permette di agire - Lo inserisco in quelli imparsabili
                        print(
                            f"Device '{_payload['id']}' of type '{_payload['type']}' is not parsable as a Smart Data Model.-> \n\t->Error msg: {_error.message}")

                        self.unvalidated_payloads.append((_item, _metadata))
                        # Chiamo execute_parsing correggendo un po' il payload
                    elif re.search("is not of type", _error.message):
                        _d_type = ""
                        if re.search("array", _error.message):
                            _d_type = "array"
                        elif re.search("number", _error.message):
                            _d_type = "number"
                        if _d_type == "":    # Se non è un errore riconosciuto, lo sposto direttamente tra quelli non validi
                            self.unvalidated_payloads.append(
                                (_item, _metadata))
                            print(
                                f"Device '{_payload['id']}' of type '{_payload['type']}' is not parsable as a Smart Data Model."
                                f"-> \n\t->Error msg: {_error.message}")
                            continue
                        _path = _error.json_path
                        _path = _path.rsplit(".")
                        _pointer = _path.pop(0)
                        _tmp = _payload
                        while len(_path) > 0:
                            _pointer = _path.pop(0)
                            if len(_path) == 0:
                                if _d_type == "array":
                                    _tmp[_pointer] = [_tmp[_pointer]]
                                elif _d_type == "number":
                                    _tmp[_pointer] = float(_tmp[_pointer])
                            else:
                                _tmp = _tmp[_pointer]
                        a = self.execute_parsing([(_payload, _metadata)])
                        if len(a) > 0:
                            print(
                                f"Corrected error when parsing with schema of '{_payload['id']}'")
                            unvalidated_payloads.append(a[0])
                    else:
                        # Se non è uno dei casi che so gestire, allora confermo che si tratta di un payload non correggibile
                        self.unvalidated_payloads.append(_item)
                        print(
                            f"Device '{_payload['id']}' of type '{_payload['type']}' is not parsable as a Smart Data Model."
                            f"-> \n\t->Error msg: {_error.message}")
                else:
                    # Se non è uno dei casi che so gestire, allora confermo che si tratta di un payload non correggibile
                    self.unvalidated_payloads.append(_item)
                    print(
                        f"Device '{_payload['id']}' of type '{_payload['type']}' is not parsable as a Smart Data Model.")
            elif len(_schemas_id) == 0:
                continue
            else:
                print("NEW CASE: please go to parser_model.py -> parse_unparsed()")
                for _schema_tuple in _schemas_id:
                    a = None

    def get_results(self):
        return [self.validated_payloads, self.unvalidated_payloads, self.thrown_exceptions]

    def reset_parser(self):
        self.validated_payloads = []
        self.unvalidated_payloads = []
        self.thrown_exceptions = []
        self.raw_payloads = []
        self.raw_metadata = []
