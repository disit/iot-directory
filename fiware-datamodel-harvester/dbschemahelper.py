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

import ast
import mysql.connector
import json
import statics


class DbSchemaHelper:
    def __init__(self, connection_details):
        if connection_details["ssl_disabled"] == 1:
            temp_bool = True
        else:
            temp_bool = False

        self.connector_mysql = mysql.connector.connect(
            host=connection_details["host"],
            user=connection_details["user"],
            password=connection_details["password"],
            database=connection_details["database"],
            ssl_disabled=temp_bool
        )
        self.prepared_cursor_mysql = self.connector_mysql.cursor(prepared=True)
        self.cursor_mysql = self.connector_mysql.cursor(buffered=True)

        _table_raw_schema_model = """CREATE TABLE IF NOT EXISTS raw_schema_model
                (
                    domain VARCHAR(50) NOT NULL DEFAULT "Unset",
                    subdomain VARCHAR(50) NOT NULL DEFAULT "Unset",
                    model VARCHAR(50) NOT NULL DEFAULT "Unset",
                    version VARCHAR(10) NOT NULL DEFAULT "0.0.0",
                    attributes JSON,
                    warnings JSON,
                    attributesLog JSON,
                    json_schema JSON,
                    timestamp TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    unvalidAttributes JSON NULL DEFAULT NULL,
                    subnature VARCHAR(50) DEFAULT NULL,
                    PRIMARY KEY (domain, subdomain, model, version)
                );"""

        _table_default_versions = """CREATE TABLE IF NOT EXISTS default_versions
                (
                    domain VARCHAR(50) NOT NULL DEFAULT "Unset",
                    subdomain VARCHAR(50) NOT NULL DEFAULT "Unset",
                    model VARCHAR(50) NOT NULL DEFAULT "Unset",
                    defaultVersion VARCHAR(10) NOT NULL DEFAULT "0.0.0",
                    timestamp TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (domain, subdomain, model)
                );"""

        _table_map_id = """CREATE TABLE IF NOT EXISTS map_id
                (
                    model_id INT NOT NULL AUTO_INCREMENT,
                    domain VARCHAR(50) NOT NULL DEFAULT "Unset",
                    subdomain VARCHAR(50) NOT NULL DEFAULT "Unset",
                    model VARCHAR(50) NOT NULL DEFAULT "Unset",
                    version VARCHAR(10) NOT NULL DEFAULT "0.0.0",
                    PRIMARY KEY (model_id)
                );"""

        _table_rules = """
                CREATE TABLE IF NOT EXISTS `EXT_values_rules` (
                      `Name` varchar(40) NOT NULL,
                      `If_statement` text DEFAULT NULL,
                      `Then_statement` text DEFAULT NULL,
                      `Organization` varchar(40) DEFAULT NULL,
                      `Timestamp` datetime NOT NULL,
                      `mode` varchar(45) DEFAULT NULL,
                      `contextbroker` varchar(45) DEFAULT NULL,
                      `service` varchar(25) DEFAULT NULL,
                      `servicePath` varchar(96) DEFAULT NULL,
                      `deviceId` varchar(100) DEFAULT NULL,
                      PRIMARY KEY (`Name`, `Timestamp`)
                );"""

        _function_delete_checked = """
                CREATE FUNCTION filter_checked (json_doc JSON, checked VARCHAR(5))
                RETURNS TEXT
                DETERMINISTIC
                RETURN ( SELECT JSON_OBJECTAGG(one_key, output)
                         FROM ( SELECT one_key, JSON_EXTRACT(json_doc, CONCAT('$.', one_key)) output
                                FROM JSON_TABLE(JSON_KEYS(json_doc),
                                                '$[*]' COLUMNS (one_key VARCHAR(64) PATH '$')) jsontable
                                HAVING output->>'$.checked' = checked ) subquery );"""

        _table_context_brokers = """
                CREATE TABLE IF NOT EXISTS EXT_brokers (
                    organization VARCHAR(50) NOT NULL,
                    contextBrokerID VARCHAR(50) NOT NULL,
                    accessLink VARCHAR(256) NOT NULL,
                    multitenancy BOOLEAN NOT NULL,
                    NGSIvers VARCHAR(50),
                    service VARCHAR(50),
                    servicePath VARCHAR(50),
                    timestamp TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (accessLink, service)
                );"""

        _tables = [
            _table_raw_schema_model,
            _table_default_versions,
            _table_map_id,
            _table_rules,
            _table_context_brokers
            # _function_delete_checked
        ]

        for _table in _tables:
            try:
                self.cursor_mysql.execute(_table)
                self.cursor_mysql.reset()
            except mysql.connector.Error as err:
                if err.msg != "Table 'attrs' already exists":
                    print("Something went wrong: {}".format(err))

    def update_default_version(self, model, subdomain, domain, new_version):
        _query = f'UPDATE default_versions SET  defaultVersion="{new_version}" WHERE ' \
                 f'model="{model}" AND subdomain="{subdomain}" AND domain="{domain}"'
        try:
            self.cursor_mysql.execute(_query)
            self.connector_mysql.commit()
            self.cursor_mysql.reset()
        except mysql.connector.Error as err:
            print("Something went wrong: {}".format(err))
            self.cursor_mysql.reset()
            self.connector_mysql.rollback()

    def _default_version_procedure(self, model, subdomain, domain, version):
        try:
            a = f"INSERT IGNORE INTO default_versions VALUES ('{domain}', '{subdomain}', '{model}', '{version}', NOW())"
            self.cursor_mysql.execute(a)
            self.cursor_mysql.reset()
        except mysql.connector.Warning as war:
            print("Something went wrong: {}".format(war))
        except mysql.connector.Error as err:
            print("Something went wrong: {}".format(err))

    def get_default_version(self, model, subdomain=None, domain=None):
        _query = f"SELECT defaultVersion, model, subdomain, domain FROM default_versions WHERE model='{model}'"
        if subdomain:
            _query += f" AND subdomain='{subdomain}'"
        if domain:
            _query += f" AND domain='{domain}'"
        try:
            self.cursor_mysql.execute(_query)
            res = self.cursor_mysql.fetchall()
            if res is None:
                return None
            elif len(res) == 1:
                return res[0][0]
            else:
                if self.check_same_modelsName_same_schema(model):
                    return res[0][0]
        except mysql.connector.Warning as war:
            print("Something went wrong: {}".format(war))
            return None
        except mysql.connector.Error as err:
            print("Something went wrong: {}".format(err))
            return None

    def get_id(self, model, subdomain=None, domain=None, version=None):
        _query = f'SELECT * FROM map_id WHERE model="{model}"'
        if subdomain:
            _query += f' AND subdomain="{subdomain}"'
        if domain:
            _query += f' AND domain="{domain}"'
        if version:
            _query += f' AND version="{version}"'
        try:
            self.cursor_mysql.execute(_query)
            _res = self.cursor_mysql.fetchall()
            if len(_res) == 1:
                return _res[0][0]
            else:
                return None
        except mysql.connector.Warning as war:
            print("Something went wrong: {}".format(war))
        except mysql.connector.Error as err:
            print("Something went wrong: {}".format(err))

    def _procedure_add_id(self, model, subdomain, domain, version):
        _maybe_id = self.get_id(model, subdomain, domain, version)
        if not _maybe_id:
            try:
                _query = f"INSERT INTO map_id (model, subdomain, domain, version) VALUES ('{model}', '{subdomain}', '{domain}', '{version}')"
                self.cursor_mysql.execute(_query)
                self.cursor_mysql.reset()
            except mysql.connector.Warning as war:
                print("Something went wrong: {}".format(war))
            except mysql.connector.Error as err:
                print("Something went wrong: {}".format(err))

    def _prepare_tuple(self, _tuple):
        _domain = _tuple[0]
        _subdomain = _tuple[1]
        _model = _tuple[2]
        _version = _tuple[3]
        _atts = json.dumps(_tuple[4])
        _errors = json.dumps(_tuple[5])
        _atts_log = json.dumps(_tuple[6])
        _schema = json.dumps(_tuple[7])

        return _domain, _subdomain, _model, _version, _atts, _errors, _atts_log, _schema

    def add_model(self, _tuple, overwrite):
        try:
            _t = self._prepare_tuple(_tuple)
            _domain = _t[0]
            _subdomain = _t[1]
            _model = _t[2]
            _version = _t[3]
            self._default_version_procedure(
                _model, _subdomain, _domain, _version)
            self._procedure_add_id(_model, _subdomain, _domain, _version)
            _overwrite = "INSERT"
            _end = f'ON DUPLICATE KEY UPDATE timestamp=NOW()'
            if overwrite:
                _overwrite = "REPLACE"
                _end = ""
            self.prepared_cursor_mysql.execute(
                f'{_overwrite} INTO raw_schema_model  (`domain`, `subdomain`, `model`, `version`, `attributes`, `warnings`, `attributesLog`, `json_schema`, `timestamp`, `unvalidAttributes`, `subnature`) VALUES (?,?,?,?,?,?,?,?,NOW(),JSON_ARRAY(), NULL) {_end}', _t)
            self.connector_mysql.commit()
            self.prepared_cursor_mysql.reset()
            return True, ""
        except mysql.connector.Error as err:
            print("Something went wrong: {}".format(err))
            self.connector_mysql.rollback()

    def generic_query(self, query, returns=True):
        try:
            self.cursor_mysql.execute(query)
            self.connector_mysql.commit()
            if returns:
                return self.cursor_mysql.fetchall()
            else:
                self.cursor_mysql.reset()
        except mysql.connector.Error as err:
            print("Something went wrong: {}".format(err))
            self.connector_mysql.rollback()
        except mysql.connector.Warning as err:
            print("Something went wrong: {}".format(err))
            self.connector_mysql.rollback()

    def get_model_schema(self, model, subdomain=None, domain=None, version=None):
        _query = f'SELECT version, json_schema, subdomain, domain FROM raw_schema_model WHERE model="{model}"'
        if subdomain:
            _query += f' AND subdomain="{subdomain}"'
        if domain:
            _query += f' AND domain="{domain}"'
        if version:
            _query += f' AND version="{version}"'
        try:
            self.cursor_mysql.execute(_query)
            query_res = self.cursor_mysql.fetchall()
            if len(query_res) == 1:
                _dict_str = query_res[0]
                if _dict_str is not None:
                    _string = _dict_str[1]
                    res = json.loads(_string)
                    # res = ast.literal_eval(_string)
                    return res
                else:
                    return None
            elif len(query_res) > 1 and version is None:
                _default_version = self.get_default_version(
                    model, subdomain, domain)
                if _default_version is None:
                    return None
                print(
                    f"Found some versions.. Selecting default version for model '{model}' (version {_default_version}).")
                _temp_res = None
                for item in query_res:
                    if item[0] == _default_version:
                        if _temp_res is None:
                            _temp_res = item[1]
                        elif not statics.json_is_equals(_temp_res, item[1]):
                            print(
                                "Ambiguous.. Specify other parameters, like subdomain or domain.")
                            return None
                        else:
                            print(
                                f"Same version of schema in different keys 'domain'({item[3]}) and 'subdomain'({item[2]}).")
                return _temp_res
            return None
        except mysql.connector.Error as err:
            print("Something went wrong: {}".format(err))
            return None
        except mysql.connector.Warning as err:
            print("Something went wrong: {}".format(err))
            return None

    def get_errors(self, model, subdomain=None, domain=None, version=None, print_res=True):
        _query = f'SELECT version, warnings FROM raw_schema_model WHERE model="{model}"'
        if subdomain:
            _query += f' AND subdomain="{subdomain}"'
        if domain:
            _query += f' AND domain="{domain}"'
        if version:
            _query += f' AND version="{version}"'
        try:
            self.cursor_mysql.execute(_query)
            a = self.cursor_mysql.fetchall()
            if a is not None:
                if len(a) == 1:
                    res = ast.literal_eval(a[0][1])
                    if print_res:
                        _str = f"Errors of model {model}:\n"
                        for error in res:
                            _str += error + "\n"
                        print(_str)
                    return res
                elif len(a) > 1 and version is None:
                    _def_version = self.get_default_version(
                        model, subdomain, domain)
                    if _def_version is None:
                        return None
                    print(
                        f"Getting errors of default version (version {_def_version})")
                    _temp_res = None
                    for item in a:
                        if item[0] == _def_version:
                            if _temp_res is None:
                                _temp_res = item[1]
                            else:
                                print(
                                    "Ambiguous.. Specify other parameters, like subdomain or domain.")
                                return None
                    return None
            else:
                return None
        except mysql.connector.Error as err:
            print("Something went wrong: {}".format(err))
            return None
        except mysql.connector.Warning as err:
            print("Something went wrong: {}".format(err))
            return None

    def get_all_versions(self, model, subdomain=None, domain=None):
        _query = f'SELECT version, subdomain, domain FROM raw_schema_model WHERE model="{model}"'
        if subdomain:
            _query += f' AND subdomain="{subdomain}"'
        if domain:
            _query += f' AND domain="{domain}"'
        try:
            self.cursor_mysql.execute(_query)
            return self.cursor_mysql.fetchall()
        except mysql.connector.Error as err:
            print("Something went wrong: {}".format(err))
            return None
        except mysql.connector.Warning as err:
            print("Something went wrong: {}".format(err))
            return None

    def get_attributes(self, model=None, subdomain=None, domain=None, version=None, onlyChecked="",
                       also_attributes_logs=False, excludeType=True, excludedAttr=[]):
        _attr_log = ""
        if also_attributes_logs:
            _attr_log = ", attributesLog"
        _exc_type_1 = ""
        _exc_type_2 = ""
        if excludeType:
            _exc_type_1 = f'JSON_REMOVE('
            _exc_type_2 = f', "$.type")'
        _query = f'select {_exc_type_1}attributes{_exc_type_2}, model, subdomain, domain, version{_attr_log} from raw_schema_model WHERE version!=" "'
        if model:
            _query += f' AND model="{model}"'
        if subdomain:
            _query += f' AND subdomain="{subdomain}"'
        if domain:
            _query += f' AND domain="{domain}"'
        if version:
            _query += f' AND version="{version}"'
        try:
            self.cursor_mysql.execute(_query)
            query_res = self.cursor_mysql.fetchall()
            _result = []
            while len(query_res) > 0:
                _tuple = query_res.pop(0)
                _list = list(_tuple)
                if _list[0] is not None:
                    _attrs = json.loads(_list[0])
                    _attr_keys = list(_attrs.keys())
                    _temp = {}
                    while len(_attr_keys) > 0:
                        _attr_key = _attr_keys.pop(0)
                        _obj = _attrs[_attr_key]
                        if onlyChecked:
                            if _obj["checked"] == onlyChecked:
                                _temp[_attr_key] = _obj
                        else:
                            _temp[_attr_key] = _obj
                    _list[0] = _temp
                else:
                    _list[0] = {}
                if also_attributes_logs:
                    _list[5] = json.loads(_list[5])
                if len(_list[0]) > 0:
                    _result.append(tuple(_list))
            return _result
        except mysql.connector.Error as err:
            print("Something went wrong: {}".format(err))
            return None
        except mysql.connector.Warning as err:
            print("Something went wrong: {}".format(err))
            return None

    def get_unchecked_attrs(self, model=None, subdomain=None, domain=None, version=None):
        _query = f'SELECT model, subdomain, domain, version, attributes->"$.*.value_name" FROM raw_schema_model WHERE json_contains(attributes->"$.*.checked",\'"False"\',"$")'
        if model:
            _query += f' AND model="{model}"'
        if subdomain:
            _query += f' AND subdomain="{subdomain}"'
        if domain:
            _query += f' AND domain="{domain}"'
        if version:
            _query += f' AND version="{version}"'
        _query += " ORDER BY model"
        try:
            self.cursor_mysql.execute(_query)
            q_res = self.cursor_mysql.fetchall()
            if not model and not subdomain and not domain and not version:
                _temp = []
                for _tuple in q_res:
                    _atts = json.loads(_tuple[4])
                    _tple = (_tuple[0], _tuple[1], _tuple[2], _tuple[3], _atts)
                    _temp.append(_tple)
                return _temp  # Seleziona tutti i modelli
            if model and subdomain and domain and version:
                return ast.literal_eval(q_res[0][4])
            if q_res is not None:
                if len(q_res) == 1:
                    return ast.literal_eval(q_res[0][4])
                elif len(q_res) > 1:
                    _def_version = self.get_default_version(
                        model, subdomain, domain)
                    if _def_version is None:
                        return None
                    _temp_res = None
                    for item in q_res:
                        if item[3] == _def_version:
                            if _temp_res is None:
                                _temp_res = item[4]
                            else:
                                _temp_res = None
                    return _temp_res
            else:
                return None
        except mysql.connector.Error as err:
            print("Something went wrong: {}".format(err))
            return None
        except mysql.connector.Warning as err:
            print("Something went wrong: {}".format(err))
            return None

    # Return list of tuple with python dict of attributes
    # It can find some attribute with same name if model .. is not specified.
    def get_attribute(self, attribute_name, model=None, subdomain=None, domain=None, version=None,
                      onlyChecked_True_False=None):
        _query = f"""select model, subdomain, domain, version, attributes->"$.{attribute_name}" from raw_schema_model where json_contains(attributes->>"$.*.value_name", '"{attribute_name}"', "$" )"""
        if model:
            _query += f' AND model="{model}"'
        if subdomain:
            _query += f' AND subdomain="{subdomain}"'
        if domain:
            _query += f' AND domain="{domain}"'
        if version:
            _query += f' AND version="{version}"'
        if onlyChecked_True_False:
            _query += f' AND attributes->>"$.{attribute_name}.checked"="{onlyChecked_True_False}"'
        _query_res = self.generic_query(_query)
        _res = []
        if _query_res:
            for item in _query_res:
                _mdl = item[0]
                _sbd = item[1]
                _dmn = item[2]
                _vrs = item[3]
                _att = json.loads(item[4])
                _res.append((_mdl, _sbd, _dmn, _vrs, _att))
        return _res

    def get_sbdom_dom(self, model, version=None):
        _query = f"SELECT model, subdomain, domain, version FROM raw_schema_model WHERE model='{model}'"
        if version:
            _query += f" AND version='{version}'"
        _query_res = self.generic_query(_query)
        if len(_query_res) == 1:
            return _query_res[0][1], _query_res[0][2], _query_res[0][3]
        else:
            print("Some version of this model has been found in database")
            self.check_same_modelsName_same_schema(model)

    # Controlla se tutti i modelli con quel nome sono uguali tra loro.
    # Questo puà andare bene a questo livello, ma se venissero rilasciate nuove versioni di questi modelli, questo
    # dovrebbe non funzionare più TODO Leggi qui
    # Potrebbe essere vincolato usando anche la versione: modelli con lo stesso nome e stessa versione, sono identici?
    # Possibile funzioni così.
    def check_same_modelsName_same_schema(self, model, version=None):
        _versions = self.get_all_versions(model)
        _same_schemas = True
        if len(_versions) > 1:
            _itr = 0
            while _itr < len(_versions) - 1:
                # Each tuple is (version, subdomain, domain)
                _sch_outside = _versions[_itr]
                _current_schema = self.get_model_schema(
                    model, _sch_outside[1], _sch_outside[2], _sch_outside[0])
                _iterator = _itr
                _itr += 1
                while _iterator < len(_versions):
                    _sch_inside = _versions[_iterator]
                    _schema = self.get_model_schema(
                        model, _sch_inside[1], _sch_inside[2], _sch_inside[0])
                    if not statics.json_is_equals(_current_schema, _schema):
                        _same_schemas = False
                    _iterator += 1
            if _same_schemas:
                print(f"All of this schemas are equals. Model: '{model}'")
            else:
                print(
                    f"Different schemas with same model name. Model: '{model}'")
        return _same_schemas

    def update_attribute_field(self, model, subdomain, domain, version, attribute_name, field="checked",
                               value_to_set="False"):
        try:
            # self.cursor_mysql.execute(_query)
            if not isinstance(value_to_set, str):
                value_to_set = json.dumps(value_to_set)
            _t = (value_to_set,)
            self.prepared_cursor_mysql.execute(
                f'UPDATE raw_schema_model '
                f'SET attributes=JSON_SET(attributes, "$.{attribute_name}.{field}", %s) '
                f'WHERE model="{model}" AND subdomain="{subdomain}" AND domain="{domain}" AND version="{version}"', _t)

            self.connector_mysql.commit()
            self.prepared_cursor_mysql.reset()
        except mysql.connector.Error as err:
            print("Something went wrong: {}".format(err))
            self.connector_mysql.rollback()
            return None
        except mysql.connector.Warning as err:
            print("Something went wrong: {}".format(err))
            self.connector_mysql.rollback()
            return None

    def count_attributes(self, attribute, group_by=""):
        if not group_by:
            group_by = "model"
        _query = f'select {group_by}, count(*) from raw_schema_model ' \
                 f'where json_contains(attributes->"$.*.value_name", \'"{attribute}"\', "$" ) ' \
                 f'group by {group_by}'
        res = self.generic_query(_query)
        return res

    def add_rule(self, rule, multitenancy=False):
        _name = rule[0]
        _ifs = json.dumps(rule[1])
        _thens = json.dumps(rule[2])
        _org = rule[3]
        _cb = rule[4]
        _device = rule[5]
        _t = (_name, _ifs, _thens, _org, _cb)  # , _device)
        if multitenancy:
            _srv = rule[5]
            _service_path = rule[6]
            _t = (_name, _ifs, _thens, _org, _cb, _srv, _service_path, _device)
        try:
            if multitenancy:
                self.prepared_cursor_mysql.execute(f'INSERT INTO EXT_values_rules VALUES (?,?,?,?,NOW(),"1",?,?,?,?)',
                                                   _t)
            else:
                self.prepared_cursor_mysql.execute(
                    f'INSERT INTO EXT_values_rules VALUES (?,?,?,?,NOW(),"1", ?, Null, Null)', _t)
            self.connector_mysql.commit()
            self.prepared_cursor_mysql.reset()
        except mysql.connector.Error as err:
            print("Something went wrong: {}".format(err))
            return None
        except mysql.connector.Warning as err:
            print("Something went wrong: {}".format(err))
            return None

    def create_attribute_if_not_exists(self, attribute_name, model, subdomain, domain, version):
        if not self.attribute_exists(attribute_name, model, subdomain, domain, version):
            _query = f'UPDATE raw_schema_model SET attributes=JSON_INSERT(attributes->"$", "$.{attribute_name}", ' \
                     f'JSON_OBJECT("value_type", "-", "value_name", "{attribute_name}", "checked", "False", "editable", "0", "data_type", "-", ' \
                     f'"value_unit", "-", "raw_attribute", JSON_OBJECT(), "healthiness_value", "300", "healthiness_criteria", "refresh_rate")) ' \
                     f'WHERE model="{model}" AND subdomain="{subdomain}" AND domain="{domain}" AND version="{version}"'
            self.generic_query(_query, returns=False)
            _query = f'UPDATE raw_schema_model SET attributesLog=JSON_SET(attributesLog->"$", "$.{attribute_name}", JSON_ARRAY()) ' \
                     f'WHERE model="{model}" AND subdomain="{subdomain}" AND domain="{domain}" AND version="{version}"'
            self.generic_query(_query, False)

    def attribute_exists(self, attribute_name, model=None, subdomain=None, domain=None, version=None):
        _query = f'SELECT count(*) from attrs where value_name="{attribute_name}"'
        if model:
            _query += f' AND model="{model}"'
        if subdomain:
            _query += f' AND subdomain="{subdomain}"'
        if domain:
            _query += f' AND domain="{domain}"'
        if version:
            _query += f' AND version="{version}"'
        _query += " ORDER BY model"
        _query_res = self.generic_query(_query)
        if _query_res[0][0] > 0:
            return True
        return False

    def delete_attribute(self, attribute_name, model, subdomain, domain, version):
        _query = f"UPDATE raw_schema_model " \
                 f"SET attributes=JSON_REMOVE(attributes, \"$.{attribute_name}\") " \
                 f"WHERE model=\"{model}\" AND subdomain=\"{subdomain}\" AND domain=\"{domain}\" AND version=\"{version}\""
        self.generic_query(_query, False)

    def attribute_is_checked(self, attribute_name, model, subdomain, domain, version):
        _query = f"SELECT count(*) " \
                 f"FROM attrs WHERE model='{model}' AND subdomain='{subdomain}' AND domain='{domain}' AND version='{version}' " \
                 f"AND checked='True' AND value_name='{attribute_name}'"
        _res = self.generic_query(_query, True)
        if len(_res) > 0:
            if _res[0][0] > 0:
                return True
        return False

    def get_brokers(self, organization=None):
        _query = "SELECT * FROM EXT_brokers"
        if organization:
            _query += f" WHERE organization='{str(organization)}'"
        return self.generic_query(_query, True)

    def set_broker_status(self, context_broker_link, status):
        _query = f"UPDATE TABLE EXT_brokers SET status='{status}' WHERE accessLink='{context_broker_link}'"
        self.generic_query(_query, False)

    def get_external_update(self):
        _query = f"SELECT model, subdomain, domain, version, attributes, attributesLog " \
                 f"FROM raw_schema_model"
        _query_res = self.generic_query(_query, True)
        _temp = []
        while len(_query_res) > 0:
            _tuple = list(_query_res.pop())
            _tuple[4] = json.loads(_tuple[4])
            _tuple[5] = json.loads(_tuple[5])
            _temp.append(tuple(_tuple))
        return _temp

    def add_rule_problem(self, model, subdomain, domain, version, error):
        try:
            self.prepared_cursor_mysql.execute(f'UPDATE raw_schema_model SET unvalidAttributes=JSON_ARRAY_APPEND(unvalidAttributes, "$", %s) '
                                               f'WHERE model="{model}" AND subdomain="{subdomain}" AND domain="{domain}" AND version="{version}"', (error, ))
            self.connector_mysql.commit()
            self.prepared_cursor_mysql.reset()
        except mysql.connector.Error as err:
            print("Something went wrong: {}".format(err))
            self.connector_mysql.rollback()
            return None
        except mysql.connector.Warning as err:
            print("Something went wrong: {}".format(err))
            self.connector_mysql.rollback()
            return None

    def set_attribute(self, model, subdomain, domain, version, attribute_name, attribute_dict):
        for attr_key in attribute_dict:
            self.update_attribute_field(
                model, subdomain, domain, version, attribute_name, attr_key, attribute_dict[attr_key])

    def update_attributes_log(self, model, subdomain, domain, version, attribute_name, new_log):
        _query = f'UPDATE raw_schema_model SET attributesLog=JSON_SET(attributesLog, "$.{attribute_name}", JSON_ARRAY()) ' \
                 f'WHERE model="{model}" AND subdomain="{subdomain}" AND domain="{domain}" AND version="{version}"'
        self.generic_query(_query, False)
        for _log_row in new_log:
            _query = f'UPDATE raw_schema_model SET attributesLog=JSON_ARRAY_APPEND(attributesLog, "$.{attribute_name}", "{_log_row}") ' \
                     f'WHERE model="{model}" AND subdomain="{subdomain}" AND domain="{domain}" AND version="{version}"'
            self.generic_query(_query, False)

    def append_to_logs(self, model, subdomain, domain, version, attribute_name, row):
        self.create_attribute_if_not_exists(
            attribute_name, model, subdomain, domain, version)
        try:
            self.prepared_cursor_mysql.execute(f'UPDATE raw_schema_model '
                                               f'SET attributesLog=JSON_ARRAY_APPEND(attributesLog, "$.{attribute_name}", "{row}") '
                                               f'WHERE model="{model}" AND subdomain="{subdomain}" AND domain="{domain}" AND version="{version}"')
            self.connector_mysql.commit()
            self.prepared_cursor_mysql.reset()
        except mysql.connector.Error as err:
            print("Something went wrong: {}".format(err))
            self.connector_mysql.rollback()
            return None
        except mysql.connector.Warning as err:
            print("Something went wrong: {}".format(err))
            self.connector_mysql.rollback()
            return None
