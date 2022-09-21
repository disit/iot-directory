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

import json
import os
import shutil
import re
from schema_exceptions import Schema_exception


class Schema_interpreter:

    def __init__(self,
                 schema_uri="",
                 domain="", subdomain="", model="",
                 constraint_schema=None,
                 result_folder=None
                 ):
        self.base_folder = os.path.dirname(__file__) + "/"
        if result_folder is None:
            self.result_folder = self.base_folder + "Results/"
        else:
            self.result_folder = result_folder
        self.domain = domain
        self.subdomain = subdomain
        self.model = model
        self.attribute_type = None  # Type property (Building, etc)
        self.schema_uri = schema_uri
        # Can exist some definitions schema into a domain
        self.subdomain_common_schema_uri = []
        self.raw_schema = None
        self.scalar_attributes = {
            "title": "-DEFAULT TITLE-",
            "description": "-DEFAULT DESCRIPTION-",
            "$schema": "-DEFAULT SCHEMA-",
            "$id": "-DEFAULT ID-",
            "$schemaVersion": "0.0.0",
            "modelTags": "-DEFAULT TAGS-",
            "type": "-DEFAULT TYPE-",
            "derivedFrom": "-DEFAULT DERIVEDFROM",
            "license": "-DEFAULT LICENSE",
            "required": "-DEFAULT REQUIREMENTS-"
        }
        if constraint_schema is None:
            self.constraint_schema = ["allOf", "anyOf", "oneOf", "not"]
        else:
            self.constraint_schema = constraint_schema
        self.external_ref = []  # References from definitions schema
        self.schema_type = "object"  # Normally it's "object"
        self.raw_attributes = {"$ref": []}
        self.schema_details = ""
        self.schema_valid = ""  # append this to the end of the file that save schema_details
        self.properties = None
        self.errors = []
        # Definitions keys of all definitions schema inside a subdomain
        self.common_definitions = []
        self.definition_schema = False
        self.schemas_commons = []
        self.analyzed_attrs = {}
        self.attributes_log = {}
        self.wrong_models = []
        self.unresetted_errors = {}  # Error saved during exception launch

    # Procedure istanzia un nuovo modello - Può essere chiamato da fuori contenendo questo oggetto
    def procedure(self, schema_uri, domain, subdomain, model):
        print(f"\nAnalysis of {domain} {subdomain} {model}")
        self._reset()
        self.schema_uri = schema_uri
        self.domain = domain
        self.subdomain = subdomain
        self.model = model
        try:
            self._load_schema()
            if not self.definition_schema:
                self._clean_schema()
                self._validate_schema_structure()
                self._control_standardized_schema()
                self._control_raw_properties()
                self._save_details()
            else:
                print("This schema is a definitions schema. Loaded its definitions.")
                self._manage_description_schema()
        except Schema_exception as e:
            self.errors.append(f"- Error: {e.get_error()} <-(from EXCEPTION)")
            self.unresetted_errors[model] = self.errors
            self._reset()
            print(e)

    def _load_schema(self):
        if not self._exists_schema(self.schema_uri):
            self.schema_details += f"Error: {self.schema_uri} doesn't exist! THIS SCHEMA IS INVALID-!\n"
            self.schema_valid = "INVALID-"
            raise Schema_exception(
                f"Error: {self.schema_uri} doesn't exist! THIS SCHEMA IS INVALID-!")

        with open(self.schema_uri, encoding="utf8") as schema:
            _raw_schema = json.load(schema)
            if self._is_definition_schema(_raw_schema):
                self.subdomain_common_schema_uri.append(self.schema_uri)
                self.definition_schema = True
                self.raw_schema = _raw_schema
            else:
                self.raw_schema = _raw_schema

        _iterator = len(self.subdomain_common_schema_uri)
        while _iterator > 0:
            _temp = self.subdomain_common_schema_uri.pop()
            with open(_temp) as _definition_schema:
                _raw_schema_def = json.load(_definition_schema)
                self._load_common_schema(_raw_schema_def)
            _iterator -= 1

    def _is_definition_schema(self, _schema):
        if self.find_key_from_dict("definitions", False, _schema, []) is None:
            return False
        else:
            self._add_common_schema_name(self.schema_uri)
            return True

    def _validate_schema_structure(self):
        if len(self.raw_schema.keys()) > 0:
            self.schema_details += "ATTENTION: raw schema can't be recognized in all of its attributes. Please check it by yoursef.\n"
            _keys_to_delete = []
            for _unkn_key in self.raw_schema.keys():
                for _scal_key in self.scalar_attributes.keys():
                    if re.findall(_unkn_key, _scal_key):
                        self.errors.append(
                            f"- Attention: assumed key '{_unkn_key}' equals to '{_scal_key}'.")
                        self.schema_details += f"\tI'm assuming that key '{_unkn_key}' is '{_scal_key}'.\n"
                        self.scalar_attributes[_scal_key] = self.raw_schema[_unkn_key]
                        _keys_to_delete.append(_unkn_key)
            self.schema_details += "\n"
            for _key in _keys_to_delete:
                self.raw_schema.pop(_key)
        if len(self.raw_schema.keys()) == 0:
            if self.properties is None:
                self.schema_details += "This schema is non common, because properties are 'None' (not found).\n"
                print(self.schema_uri + " haven't an attribute 'properties'.")
                self.wrong_models.append(self.model)
        else:
            self.schema_valid = "INVALID-"
            self.schema_details += f"- ATTENTION: unable recognize the following keys. [{str(self.raw_schema.keys())}]"
            self.errors.append(
                f"- Error: unable recognize the following keys. [{str(self.raw_schema.keys())}]")
            raise Schema_exception(
                f"Raw schema can't be recognized in all of its attributes.\n\tAttributes '{str(self.raw_schema.keys())}' hasn't been recognized")

    def _control_standardized_schema(self):
        if self.scalar_attributes["type"] != "object":
            self.schema_details += f"Attention: this schema is not OBJECT. It's {self.schema_type} . THIS SCHEMA IS INVALID-\n"
            self.errors.append(
                f"- Attention: Schema is not an 'object', but '{self.schema_type}' So its wrong.")
            # Delete "type" attribute (this "type" is nomally object, and its not reffered to true "type" in propierties)
        elif "definitions" in self.raw_schema.keys():
            self.schema_details += "This schema is a definition schema. It contains definitions (collections of objects), " \
                                   " instead of attributes! This definitions will be loaded.\n"

    # After this execution, raw_schema must contain only the details of this schema.
    def _clean_schema(self):
        _keys = list(self.raw_schema.keys())
        for _key in _keys:
            _new_key = re.sub(" ", "", _key)
            if _key != _new_key:
                _temp = self.raw_schema.pop(_key)
                self.raw_schema[_new_key] = _temp
        self.schema_details += "AUTOMATIC GENERATED DETAILS\n\n### Setting up Scalar Attributes.\n"
        _temp, path = self.find_from_schema("properties")
        _constraint = self._check_constraint(path)
        if _constraint is None:
            self.properties = self.find_from_schema(
                "properties", delete=True)[0]
        else:
            if _constraint != "allOf":
                self.errors.append(
                    f"- Attention: container for properties should be 'allOf', instead of '{_constraint}'.")
            self.properties = self.find_from_schema(_constraint, True)[0]
        _general_constraint = self._check_constraint(self.raw_schema.keys())
        if _general_constraint is not None:
            _temp = {"properties": self.properties}
            self.raw_schema[_general_constraint].append(_temp)
            self.properties = self.raw_schema.pop(_general_constraint)
        for key in self.scalar_attributes.keys():
            if key not in self.raw_schema.keys():
                # self.schema_details += "ATTENTION: the attribute  " + str(key) + " can be wrong. Check it by yourself.\n"
                _temp, _path = self.find_from_schema(key, True)
                if _temp is not None:
                    self.scalar_attributes[key] = _temp
                else:
                    _key_found = False
                    if type(self.properties) is list:
                        _iterator = len(self.properties) - 1
                        while _iterator > 0 and not _key_found:
                            if key in self.properties[_iterator].keys():
                                _key_found = True
                                self.scalar_attributes[key] = self.properties[_iterator].pop(
                                    key)
                                if key == "required":
                                    if "type" not in self.scalar_attributes["required"]:
                                        self.errors.append(
                                            "- Error: 'required' attribute is wrong (in scalar attribute).")
                                        self.scalar_attributes[key] = []
                                        _key_found = False
                                        self.properties[_iterator][key] = self.scalar_attributes[key]
                                        self.scalar_attributes.pop(key)
                                elif key == "type":
                                    if type(self.scalar_attributes["type"]) is not str:
                                        self.errors.append(
                                            f"- Error: 'type' attribute was expected to be a string. ")
                                        self.scalar_attributes[key] = "object"
                                        _key_found = False
                                        self.properties[_iterator][key] = self.scalar_attributes[key]
                                        self.scalar_attributes.pop(key)
                            _iterator -= 1
                    elif type(self.properties) is dict:
                        if key in self.properties.keys():
                            if not self._is_attribute(self.properties[key]):
                                self.scalar_attributes[key] = self.properties.pop(
                                    key)

                    if not _key_found:
                        self.errors.append(
                            f"- Error: scalar attribute '{key}' not found")
                        self.schema_details += "I haven't found any attribute named " + \
                            key + ". Set default value for this.\n"
                        if key == "required":
                            self.scalar_attributes["required"] = []
                            self.errors.append(
                                f"- Error: attribute 'required' not found.")
                            if self.model not in self.wrong_models:
                                self.wrong_models.append(self.model)
                        else:
                            self.scalar_attributes[key] = ""
            else:
                self.scalar_attributes[key] = self.raw_schema.pop(key)

    def get_scalar_attribute(self):
        return self.scalar_attributes

    def _control_raw_properties(self, properties=None):
        if properties is None:
            _properties = self.properties
        else:
            _properties = properties
        if type(_properties) is list:
            _iterators_to_delete = []
            for _property in _properties:
                if type(_property) is dict:
                    if "$ref" in _property.keys() and len(_property.keys()) == 1:
                        if not self._is_known_ref(_property["$ref"]):
                            self.errors.append(
                                f"- Error: $ref is unknown. $ref='{_property['$ref']}'")
                        else:
                            self.raw_attributes["$ref"].append(
                                _property['$ref'])
                            _iterators_to_delete.append(_property)
                    elif "$ref" in _property.keys() and len(_property.keys()) > 1:
                        if not self._is_known_ref(_property["$ref"]):
                            self.errors.append(
                                f"- Error: $ref is unknown. $ref='{_property['$ref']}'")
                        else:
                            self.raw_attributes["$ref"].append(
                                _property['$ref'])
                            _iterators_to_delete.append(_property)
                    else:
                        _fields_to_delete = []
                        for _field in _property.keys():
                            if _field == "properties":
                                self._control_properties_dict(
                                    _property["properties"])
                                _fields_to_delete.append(_field)
                            elif _field == "$ref":
                                if not self._is_known_ref(_property['$ref']):
                                    self.errors.append(
                                        f"- Error: $ref is unknown. $ref='{_property['$ref']}'")
                                else:
                                    _fields_to_delete.append(_field)
                            else:
                                if self._is_attribute(_property[_field], _field):
                                    self.raw_attributes[_field] = _property[_field]
                                    _fields_to_delete.append(_field)
                                else:
                                    self.errors.append(
                                        f"- Error: look in '{_field}' of 'properties'")
                        for _field in _fields_to_delete:
                            _property.pop(_field)
                        if not len(_property) == 0:
                            self.errors.append(
                                f"- Error: '[{str(_property)}]' hasn't been recognized.")

                else:
                    self.errors.append(
                        f"- Error: expected dictionary, but found a wrong list of lists: '{str(_properties)}' ")
                    print(f"This ___ should be a dict")
            for _delete in _iterators_to_delete:
                _properties.remove(_delete)
            for _property in _properties:
                if len(_property.keys()) > 0:
                    self.errors.append(
                        f"- Error: some of the raw properties hasn't been recognized")
        elif type(_properties) is dict:
            self._control_properties_dict(_properties)
        elif _properties is None:
            self.errors.append(f"- Error: 'properties' hasn't been found.")
        else:
            self.errors.append(
                f"- Error: 'properties' hasn't been recognized. Type: {type(self.properties)}")
            self.schema_valid = "INVALID-"
            print(f"self.properties type {type(self.properties)} - ???")

    def _is_attribute(self, attribute, attribute_name):
        if type(attribute) is dict:
            if "type" in attribute.keys():
                if type(attribute["type"]) is not dict:
                    return True
            if "$ref" in attribute.keys():
                if self._is_known_ref(attribute["$ref"]):
                    return True
            return self._is_constraint_attribute(attribute, attribute_name)
        else:
            return self._is_known_ref(attribute)

    def _is_constraint_attribute(self, attribute, attribute_name):
        _eventually_constraint = self._check_constraint(attribute.keys())
        if _eventually_constraint is not None:
            _iterator = len(attribute[_eventually_constraint]) - 1
            _temp_type = ""
            while _iterator >= 0:
                _item = attribute[_eventually_constraint][_iterator]
                if "type" not in _item.keys():
                    if "$ref" not in _item.keys():
                        if not self._check_constraint(_item.keys()):
                            self.errors.append(
                                f"- Error: '{attribute_name}' is not an attribute. {attribute_name}=[{str(_item)}].")
                            return False
                else:
                    if _temp_type == "":
                        _temp_type = _item["type"]
                    elif _temp_type != _item["type"]:
                        self.errors.append(
                            f"- Attention: {attribute_name} may be wrong because all of the attributes haven't the same value type.")
                _iterator -= 1
        # Ok if i pass here, attribute is an Real attribute of the form allOf[{..},{...}]
        if not "description" in attribute.keys():
            self.errors.append(
                f"- Attention: {attribute_name} haven't a description")
        return True

    def _control_properties_dict(self, _properties):
        if "type" in _properties.keys():
            _type = _properties.pop("type", None)
            if _type is not None:
                self._analyze_attribute(_type, "type")
                if self.attribute_type is None:
                    self.attribute_type = _type
                    self.raw_attributes["type"] = _type
                else:
                    self.schema_details += "Error: this schema already have a type. Please check the schema.json."
                    self.schema_valid = "INVALID-"
                    self.errors.append(
                        "- Error: found two type definition for this model (wrong, only one is expected)")
        _properties_to_delete = []
        for _property in _properties.keys():
            if type(_properties[_property]) is dict:
                self.schema_details += "\n"
                if self._is_attribute(_properties[_property], _property):
                    self._analyze_attribute(_properties[_property], _property)
                    self.raw_attributes[_property] = _properties[_property]
                    _properties_to_delete.append(_property)
                elif type(_properties[_property]) is list:
                    if self._check_constraint(_properties[_property].keys()):
                        self.errors.append(
                            f"- Error: problem analyzing '{_property}' in 'properties'")
                    else:
                        a = None
                        # Ok questo caso
                else:
                    self.errors.append(
                        f"- Error: problem analyzing '{_property}' in 'properties'")
            else:
                self.schema_details += f"{_property} is not a dict. Please check schema.json.\n"
                self.schema_valid = "INVALID-"
                self.errors.append(
                    f"- Error: attribute {str(_property)} isn't a dictionary (expected to be a dictionary)")
        for _property in _properties_to_delete:
            _properties.pop(_property)

        if len(_properties) > 0:
            self.errors.append(
                f"- Error: some properties hasn't been recognized.")

    def _exists_schema(self, schema_uri):
        if os.path.exists(schema_uri) and not os.path.isdir(schema_uri):
            return True
        return False

    def _calculate_errors(self):
        # verifica i nomi di ogni singolo attributo (fai l'escape)
        self._escape_attributes_name()
        self._check_required()  # verifica che ci siano tutti gli attributi
        self._localization_check()  # scrivi quando manca l'attributo di localizzazione
        # (questa operazione viene fatta in check required)
        self._check_remains()

    def _escape_attributes_name(self):
        _temp_attributes = {}
        for _attr_key in self.raw_attributes.keys():
            _escaped_key = re.sub("[><=;()]", "", _attr_key)
            _escaped_key = re.sub('["]', "", _escaped_key)
            _escaped_key = re.sub("[']", "", _escaped_key)
            _temp_attributes[_escaped_key] = self.raw_attributes[_attr_key]
        self.raw_attributes = _temp_attributes
        _temp_attributes_2 = {}
        for _attr_key in self.analyzed_attrs.keys():
            _escaped_key = re.sub("[><=;()]", "", _attr_key)
            _escaped_key = re.sub('["]', "", _escaped_key)
            _escaped_key = re.sub("[']", "", _escaped_key)
            _temp_attributes_2[_escaped_key] = self.analyzed_attrs[_attr_key]
        self.analyzed_attrs = _temp_attributes_2

    def _check_required(self):
        _required = self.scalar_attributes["required"]
        for requirement in _required:
            if requirement not in self.external_ref:  # Known referenced
                if requirement not in self.raw_attributes.keys():
                    if requirement not in self.common_definitions:
                        self.errors.append(
                            f"- Attention: missing requirement '{requirement}'")
                        self.schema_details += f"\t Missing requirement '{requirement}'\n"
                        print(f"Missing {requirement}")

    def _localization_check(self):
        _location_found = False
        _localization_values = ["location",
                                "Location-Commons", "address", "areaServed"]
        for requirement in _localization_values:
            if requirement in self.scalar_attributes["required"]:
                _location_found = True
        if not _location_found:
            self.errors.append(
                f"- Attention: missing any 'Localization' requirement.")
            self.schema_valid = "INVALID-"

    def _check_remains(self):
        # Properties need to be empty - Otherwise some attribute hasn't been read correctly
        if type(self.properties) is list:
            for _attr in self.properties:
                if len(_attr.keys()) > 0:
                    self.errors.append(
                        f"- Error: Unrecognized properties. 'properties'=[{str(self.properties)}].")
        elif type(self.properties) is dict:
            if len(self.properties.keys()) > 0:
                self.errors.append(
                    f"- Error: schema was misinterpreted; Some attributes hasn't been interpreted")
        elif self.properties is None:
            self.errors.append(f"- Error: 'properties' not found.")
            # if self.properties is None, this has already been write as an error!!
        else:
            self.errors.append(
                f"- Error: 'properties' is type '{type(self.properties)}'")

    def _load_common_schema(self, common_schema):
        _defs = self.find_key_from_dict(
            "definitions", False, common_schema, [])
        for _definition in _defs.keys():
            _temp_propr = self.find_key_from_dict(
                "properties", False, _defs[_definition], [])
            if _temp_propr is None:
                self.external_ref.append(_definition)
            else:
                for _property in _temp_propr:
                    if _property not in self.external_ref:
                        self.external_ref.append(_property)
            if _definition not in self.common_definitions:
                self.common_definitions.append(_definition)

    def find_from_schema(self, key, delete=False):
        path = []
        result = self.find_key_from_dict(key, delete, self.raw_schema, path)
        path = path[::-1]
        return result, path

    def find_from_attribute(self, attribute, target_key):
        attribute, path = self.find_from_schema(key=attribute, delete=False)
        result = self.find_key_from_dict(target_key, False, attribute, path)
        return result, path

    def find_from_path(self, path: list):
        if len(path) == 0:
            return None
        result, full_result, goal = self._path_composer(path)

        if result is None:
            if type(full_result) is dict:
                if goal in full_result.keys():
                    return full_result[goal]  # Single key in an attribute
                else:
                    return full_result  # Attribute selected
            elif type(full_result) is list:
                if goal in full_result:
                    return goal
                else:
                    return full_result
        else:
            return result

    def find_key_from_dict(self, target_key, delete, entry_dict: dict, path):
        for _key in entry_dict.keys():
            if _key == target_key:
                path.append(_key)
                return_temp = entry_dict[target_key]
                if delete:
                    entry_dict.pop(target_key, None)
                return return_temp
            else:
                temp = entry_dict[_key]
                if type(temp) is list:
                    for item in entry_dict[_key]:
                        if type(item) is dict:
                            maybe_append = _key
                            temp_res = self.find_key_from_dict(
                                target_key, delete, item, path)
                            if temp_res is not None:
                                path.append(maybe_append)
                                return temp_res
                elif type(temp) is dict:
                    temp_res = self.find_key_from_dict(
                        target_key, delete, temp, path)
                    if temp_res is not None:
                        path.append(_key)
                        return temp_res

    def _path_composer(self, path):
        _path = path[::-1]
        goal = _path[0]
        temp = self.raw_schema
        while len(_path) > 0:
            last = _path.pop()
            if type(temp[last]) is list:
                for item in temp[last]:
                    if type(item) is dict:
                        if len(_path) > 0:
                            new_index = _path.pop()
                        else:
                            return None, temp[last], goal
                        if new_index in item.keys():
                            temp = item[new_index]

                        else:
                            _path.append(new_index)
                # _path.append(new_index)
            elif type(temp[last]) is dict:
                temp = temp[last]

        if type(temp) is dict:
            if goal in temp.keys():
                return temp[goal], temp, goal
            else:
                return None, temp, goal
        elif type(temp) is list:
            return None, temp, goal
        else:
            return None, temp, goal

    def print_path(self, path: list):
        temp = ""
        last = path.pop()
        for level in path:
            temp = temp + str(level) + "->"
        temp += last
        return temp

    def _add_common_schema_name(self, uri_common_schema):
        _split = uri_common_schema.rsplit("/")
        _common_schema = _split[-1]
        if _common_schema not in self.schemas_commons:
            self.schemas_commons.append(_common_schema)

    def _is_known_ref(self, _ref):
        if re.search("#", _ref):
            _ref_list = _ref.rsplit("/")
            _ref_link = []
            _iterator = len(_ref_list) - 1
            _path = []
            _common_schema = ""
            if _iterator <= 0:
                print(
                    f"Ref {_ref} is not defined. Please check if it's referred in another extra schema.")
                self.schema_details += f"\t- Ref {_ref} is not a recognizable reference\n"
                return False
            while _iterator > 0:
                _temp = _ref_list[_iterator]
                if not _temp.endswith("#"):
                    _path.append(_temp)
                else:
                    _common_schema = _temp[:-1]
                    break
                _iterator -= 1
            _path = _path[::-1]
            if len(_path) > 0:
                _temp_pop = _path.pop()
                if _temp_pop in self.common_definitions:
                    # self.errors.append(f"- {_temp_pop} is defined into {_common_schema}")
                    self.schema_details += f"-> Ref {_ref} is a DEFINITION in {_common_schema}\n\n"
                    return True
                elif _temp_pop in self.external_ref:
                    # self.errors.append(f"- {_temp_pop} is an attribute of an object in {_common_schema}")
                    self.schema_details += f"-> Ref {_ref} is an ATTRIBUTE defined in {_common_schema}\n\n"
                    return True
                elif _common_schema in self.schemas_commons:
                    return True
                else:
                    # self.errors.append(f"- {_temp_pop} is a unknown and uncommon reference.")
                    self.schema_details += f"-> Ref {_ref} is not defined.\n"
                    return False
            else:
                return True
        elif re.search("schema", _ref):
            _ref_list = _ref.rsplit("/")
            for _part in _ref_list:
                if re.findall("schema", _part):
                    if _part in self.schemas_commons:
                        return True
        self.errors.append(
            f"- Error: {_ref} not belongs to definition schemas.")
        return False

    def _analyze_attribute(self, attribute, attribute_name):
        if self._is_attribute(attribute, attribute_name):
            if type(attribute) is dict:
                self.schema_details += "-> Attribute: \t" + attribute_name + "\n"
                _eventually_constraint = self._check_constraint(
                    attribute.keys())
                if "type" in attribute.keys():
                    _attr_type = attribute["type"]
                    if "description" in attribute.keys():
                        _attr_descr = attribute['description']
                    else:
                        _attr_descr = "(This attribute haven't a description)."
                    self.schema_details += f"\tType : {_attr_type}\n\tDescription: {_attr_descr}\n"
                    if _attr_type == "array":
                        self._manage_array(attribute, attribute_name)
                    elif _attr_type == "integer":
                        self._manage_integer(attribute, attribute_name)
                    elif _attr_type == "boolean":
                        self._manage_boolean(attribute, attribute_name)
                    elif _attr_type == "string":
                        self._manage_string(attribute, attribute_name)
                    elif _attr_type == "null":
                        self._manage_null(attribute, attribute_name)
                    elif _attr_type == "number":
                        self._manage_numeric(attribute, attribute_name)
                    elif _attr_type == "object":
                        self._manage_object(attribute, attribute_name)
                    self.schema_details += f"\tRaw attribute: \n\t{attribute}\n\tUse a json formatter for read attribute.\n"
                elif "$ref" in attribute.keys():
                    if not self._is_known_ref(attribute["$ref"]):
                        self.errors.append(
                            f"- Error: '{attribute_name}' contains an unknown reference: '{attribute['$ref']}'")
                    else:
                        self._manage_ref(attribute, attribute_name)
                elif _eventually_constraint:
                    self._manage_object(attribute, attribute_name, True)
                else:
                    self.errors.append(
                        f"- Error: unrecognized attribute. Name: '{attribute_name}'")
            elif type(attribute) is list:
                self.errors.append(
                    f"- Error: something went wrong in attribute '{attribute_name} - This attribute is a 'list'.")
            else:
                self.errors.append(
                    f"- Error: something went wrong in attribute '{attribute_name} - This attribute is '{type(attribute)}'")
        else:
            self.errors.append(
                f"- Error: '{attribute_name}' is not an attribute.")

    def _check_constraint(self, _list):
        for item in _list:
            if item in self.constraint_schema:
                return item
        return None

    def _save_details(self):
        self._calculate_errors()
        _dir = self.result_folder + self.domain + \
            "/dataModel." + self.subdomain + "/" + self.model
        self.schema_details += "\n\n~~~ Error messages ~~~\n\n"
        for error in self.errors:
            self.schema_details += f"\t{error}\n"
        os.makedirs(_dir, exist_ok=True)
        with open(_dir + f"/{self.schema_valid}{self.model}_Analysis.txt", "w", encoding="utf8") as details:
            details.write(self.schema_details)
        shutil.copyfile(self.schema_uri,
                        f"{_dir}/schema_" + re.sub('\.', '-', self.scalar_attributes['$schemaVersion']) + ".json")
        self.schema_details = ""

    def get_raw_attributes(self):
        return self.raw_attributes

    # Adattamento degli attributi per essere salvati in un database che abbia i seguenti attributi:
    #       {
    #          "value_name":"{NOME DELLA PROPRIETA'}",
    #          "data_type":"{STRING..}",
    #          "value_type":"", ** String: format ;
    #          "value_unit":"", ** Se specifica, definita in un attributo
    #          "healthiness_criteria":"refresh_rate",
    #          "healthiness_value":"300",
    #          "editable":"0"
    #       }
    #       Vorrei creare un log contenente tutte le assegnazioni che sono state fatte, e tutte le informazioni tagliate
    # Andrebbero completati per aggiungere informazioni ai log su vari schema (non utile, solo bello da vedere se completo)

    def _manage_array(self, attribute, attribute_name):
        _value_type = "-"
        _data_type = "-"
        _temp_log = []
        if "items" in attribute.keys():
            if type(attribute["items"]) is dict:
                _eventually_constraint = self._check_constraint(
                    attribute["items"].keys())
                if _eventually_constraint is None:
                    if type(attribute["items"]) is dict:
                        if "type" in attribute["items"].keys():
                            if type(attribute["items"]["type"]) is str:
                                _data_type = attribute["items"]["type"]
                else:
                    _first_edit = True
                    _items = attribute["items"][_eventually_constraint]
                    for item in _items:
                        if type(item) is dict:
                            if "type" in item.keys():
                                _temp = item["type"]
                                if type(_temp) is str:
                                    if _data_type != _temp:
                                        if _first_edit:
                                            _data_type = _temp
                                            _first_edit = False
                                        else:
                                            _data_type = "-"
                                else:
                                    a = None
                            if "format" in item.keys():
                                _temp_log.append("Format: "+item["format"])
            else:
                if "type" in attribute["items"][0]:
                    _temp = attribute["items"][0]["type"]
                    if type(_temp) is str:
                        _data_type = _temp
        _new_attribute = {
            "value_name": f"{attribute_name}",
            "data_type": f"{_data_type}",
            "value_type": f"{_value_type}",
            "value_unit": "-",
            "healthiness_criteria": "refresh_rate",  # Standard
            "healthiness_value": "300",  # Standard
            "editable": "0",  # Standard
            "checked": "False",
            "raw_attribute": attribute
        }
        self.analyzed_attrs[attribute_name] = _new_attribute
        self.attributes_log[attribute_name] = _temp_log

        if "enum" in attribute.keys():
            self.schema_details += "\tEnum found. This attribute have to be one of the following:\n\t["
            last_enum = attribute["enum"].pop()
            if len(attribute["enum"]) > 0:
                for enum in attribute["enum"]:
                    self.schema_details += '"' + str(enum) + '", '
            self.schema_details += '"' + str(last_enum) + '"]\n'
        if "items" in attribute.keys():
            if type(attribute["items"]) is dict:
                if "type" in attribute["items"].keys():
                    self.schema_details += f"\tItems have to be of type {attribute['items']['type']}\n"
                elif "$ref" in attribute["items"].keys():
                    self.schema_details += "\tThis array can contain types referred in this link: "
                    # in self.known_ref:
                    if self._is_known_ref(attribute["items"]["$ref"]):
                        self.schema_details += f"{attribute['items']['$ref']} (This ref is a known one).\n"
                    else:
                        self.schema_details += f"{attribute['items']['$ref']} (Unknown ref).\n"
                else:
                    self.schema_details += "\tThis item is unknown. It's wrong.\n"
            else:
                self.schema_details += "\tRead the items value to understand what's inside.\n"
        elif "prefixItems" in attribute.keys():
            self.schema_details += "\tThis array have a prefixItems: this means that this array have to be\n" \
                                   "done by concatenating values in this way (this can also be considered TUPLE):"
            for temp in attribute["prefixItems"]:
                self.schema_details += "[ " + str(temp)
            self.schema_details += " ]"
        else:
            self.schema_details += "\tNo items found for this array - Error.\n"

    def _manage_numeric(self, attribute, attribute_name):
        _temp_log = []
        _value_unit = "-"
        _value_type = "-"
        _attr_keys = attribute.keys()
        if "minimum" in _attr_keys and "maximum" in _attr_keys:
            _min = attribute["minimum"]
            _max = attribute["maximum"]
            if _min == 0 and _max == 1:
                self.errors.append(
                    f"- Attention: '{attribute_name}' could be a percentage.")
                _temp_log.append("This attribute can be a percentage")
                _value_type = "percentage[0,1]"

        _new_attribute = {
            "value_name": f"{attribute_name}",  # Prendo il nome dell'attributo
            "data_type": "float",
            "value_type": f"{_value_type}",
            "value_unit": f"{_value_unit}",
            "healthiness_criteria": "refresh_rate",  # Standard
            "healthiness_value": "300",  # Standard
            "editable": "0",  # Standard
            "checked": "False",
            "raw_attribute": attribute
        }
        self.analyzed_attrs[attribute_name] = _new_attribute
        self.attributes_log[attribute_name] = _temp_log

    def _manage_integer(self, attribute, attribute_name):
        _temp_log = []
        _new_attribute = {
            "value_name": f"{attribute_name}",  # Prendo il nome dell'attributo
            "data_type": "integer",  # Se sono in questo metodo, sono sicuramente un intero
            "value_type": "-",  # VA INTERPRETATO
            "value_unit": "number (#)",  # From S4C - integer
            "healthiness_criteria": "refresh_rate",  # Standard
            "healthiness_value": "300",  # Standard
            "editable": "0",  # Standard
            "checked": "False",
            "raw_attribute": attribute
        }
        self.analyzed_attrs[attribute_name] = _new_attribute
        self.attributes_log[attribute_name] = _temp_log

    def _manage_boolean(self, attribute, attribute_name):
        _temp_log = []
        _value_type = "-"
        _new_attribute = {
            "value_name": f"{attribute_name}",  # Prendo il nome dell'attributo
            "data_type": "string",
            "value_type": f"{_value_type}",
            "value_unit": "boolean (bool)",
            "healthiness_criteria": "refresh_rate",  # Standard
            "healthiness_value": "300",  # Standard
            "editable": "0",  # Standard
            "checked": "False",
            "raw_attribute": attribute
        }
        self.analyzed_attrs[attribute_name] = _new_attribute
        self.attributes_log[attribute_name] = _temp_log

    def _manage_null(self, attribute, attribute_name):
        _temp_log = []
        _value_type = "-"
        _new_attribute = {
            "value_name": f"{attribute_name}",  # Prendo il nome dell'attributo
            "data_type": "-",
            "value_type": f"{_value_type}",
            "value_unit": "-",
            "healthiness_criteria": "refresh_rate",  # Standard
            "healthiness_value": "300",  # Standard
            "editable": "0",  # Standard
            "checked": "False",
            "raw_attribute": attribute
        }
        self.analyzed_attrs[attribute_name] = _new_attribute
        self.attributes_log[attribute_name] = _temp_log

    def _manage_object(self, attribute, attribute_name, _stop=False):
        _temp_log = []
        _value_type = "-"
        _new_attribute = {
            "value_name": f"{attribute_name}",  # Prendo il nome dell'attributo
            "data_type": "json",
            "value_type": f"{_value_type}",
            "value_unit": "-",
            "healthiness_criteria": "refresh_rate",  # Standard
            "healthiness_value": "300",  # Standard
            "editable": "0",  # Standard
            "checked": "False",
            "raw_attribute": attribute
        }
        self.analyzed_attrs[attribute_name] = _new_attribute
        self.attributes_log[attribute_name] = _temp_log

        self.schema_details += f"- {attribute_name} is an object (it's defined by ITS OWN PROPERTIES)"
        if not _stop:
            if "properties" in attribute.keys():
                self._control_raw_properties(dict(attribute["properties"]))
            elif "$ref" in attribute.keys():
                # self._analyze_attribute(attribute["$ref"], attribute_name + "[$ref]")
                pass
            elif "type" in attribute.keys():
                # self._analyze_attribute(attribute, attribute_name)
                pass  # already added at the start of this method

    def _manage_ref(self, attribute, attribute_name):
        _temp_log = []
        _new_attribute = {
            "value_name": f"{attribute_name}",  # Prendo il nome dell'attributo
            "data_type": "string",
            "value_type": "-",
            "value_unit": "-",
            "healthiness_criteria": "refresh_rate",  # Standard
            "healthiness_value": "300",  # Standard
            "editable": "0",  # Standard
            "checked": "False",
            "raw_attribute": attribute
        }
        self.analyzed_attrs[attribute_name] = _new_attribute
        self.attributes_log[attribute_name] = _temp_log

    def _manage_string(self, attribute, attribute_name):
        _value_type = "-"
        _value_unit = "-"
        _data_type = "string"
        _temp_log = []
        if "format" in attribute.keys():
            _temp_log.append(
                f"Found 'format': {attribute['format']}. It will be default value_type.")
            _value_type = attribute['format']
            if _value_type == "date-time":
                _value_unit = "datetime"
                _data_type = "datetime"

        _new_attribute = {
            "value_name": f"{attribute_name}",  # Prendo il nome dell'attributo
            "data_type": f"{_data_type}",
            "value_type": f"{_value_type}",
            "value_unit": f"{_value_unit}",
            "healthiness_criteria": "refresh_rate",  # Standard
            "healthiness_value": "300",  # Standard
            "editable": "0",  # Standard
            "checked": "False",
            "raw_attribute": attribute
        }
        self.analyzed_attrs[attribute_name] = _new_attribute
        self.attributes_log[attribute_name] = _temp_log

    def _manage_description_schema(self):
        _definitions = self.find_from_schema("definitions", delete=True)[0]
        _keys = list(_definitions.keys())
        while len(_keys) > 0:
            _key = _keys.pop(0)
            _definition = _definitions.pop(_key)
            _eventually_constraint = self._check_constraint(_definition.keys())
            self._analyze_attribute(_definition, _key)

    def get_errors(self):
        return self.errors

    def _reset(self):
        self.raw_schema = None
        self.raw_attributes = {"$ref": []}
        self.errors = []
        self.schema_details = ""
        self.definition_schema = False
        self.attribute_type = None
        self.attributes_log = {}
        self.analyzed_attrs = {}
        self.properties = None

    def get_wrongs(self):
        return self.wrong_models

    def get_attributes_log(self):
        return self.attributes_log

    def get_s4c_attrs(self):
        return self.analyzed_attrs
