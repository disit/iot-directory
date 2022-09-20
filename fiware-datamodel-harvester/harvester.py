 #Snap4City: IoT-Directory
 # Copyright (C) 2017 DISIT Lab https://www.disit.org - University of Florence

  #This program is free software; you can redistribute it and/or
  #modify it under the terms of the GNU General Public License
  #as published by the Free Software Foundation; either version 2
  #of the License, or (at your option) any later version.
  #This program is distributed in the hope that it will be useful,
  #but WITHOUT ANY WARRANTY; without even the implied warranty of
  #MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  #GNU General Public License for more details.
  #You should have received a copy of the GNU General Public License
  #along with this program; if not, write to the Free Software
  #Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
import loader as ld
import os
import json
import schema_interpreter as s4c


class SmartDataModelsHarvester:

    def __init__(self,
                 base_link="https://github.com/smart-data-models/",
                 domains=None,
                 download_folder="",
                 result_folder="",
                 database=None):

        if domains is None:
            return
        self.domains = domains
        if download_folder == "":
            self.download_folder = "/media/giuseppe/Archivio2/Download/"  # Where to downaload Repos
        else:
            self.download_folder = download_folder
        self.loader = ld.Loader(self.download_folder)
        if result_folder == "":
            self.result_folder = os.path.dirname(__file__) + "/Results/"
        else:
            self.result_folder = result_folder
        os.makedirs(self.result_folder[:-1], exist_ok=True)
        self.blacklist_schemas = ["geometry-schema.json", "schema.org.json"]
        self.db_helper = database
        self.base_link = base_link
        self.location_schemas = None
        self.pandas_dataframe = None
        #if not self.dict_already_exists():
        self.load_required_files()
        self.load_domain_dict()
            #self.save_domain_dict()
        #else:
            #self.load_created_dict()
        self.schema_reader = s4c.Schema_interpreter(result_folder=self.result_folder)
        self.unsaved_models = []

    def dict_already_exists(self):
        return os.path.exists(self.result_folder + "schemas_location.json")

    def load_created_dict(self):
        with open(self.result_folder + "schemas_location.json") as file:
            self.location_schemas = json.load(file)

    def _prepare_location_schema(self):
        _definition_schemas = {
            "definition-schemas":
                {
                    "common-schema": {}, "field-common-schema": {}
                }
        }
        _def_schema = _definition_schemas["definition-schemas"]
        _0 = self.location_schemas.pop("0")
        _data_models = self.location_schemas.pop("data-models")
        for _common_schema in _data_models["common-schemas"]:
            _name = os.path.basename(_common_schema)
            if _name not in self.blacklist_schemas:
                _def_schema["common-schema"][_name] = _common_schema
        for _field_common_schema in _0["0"]:
            _name = _field_common_schema[10:]
            _def_schema["field-common-schema"][_name] = _0["0"][_field_common_schema]
        _temp = {}
        _temp["definition-schemas"] = _definition_schemas
        self.location_schemas = {**_temp["definition-schemas"], **self.location_schemas}

    def _clean_location_schema(self):
        if "0" in self.location_schemas.keys():
            for _definition_schema in self.location_schemas["0"]["0"]:
                _schema = self.location_schemas["0"]["0"][_definition_schema]
                self.schema_reader.procedure(_schema, None, None, None)
        if "data-models" in self.location_schemas.keys():
            for _schema in self.location_schemas["data-models"]["common-schemas"]:
                _name = os.path.basename(_schema)
                if _name not in self.blacklist_schemas:
                    self.schema_reader.procedure(_schema, None, None, None)
        _keys_to_rename = ["data-models", "0"]
        for _key in _keys_to_rename:
            _temp = self.location_schemas.pop(_key, None)

    def create_db_from_dict(self, also_wrongs=False, overwrite=False):
        self._prepare_location_schema()
        for domain in self.location_schemas.keys():
            for subdomain in self.location_schemas[domain].keys():
                for model in self.location_schemas[domain][subdomain].keys():
                    _schema_link = self.location_schemas[domain][subdomain][model]
                    with open(_schema_link, encoding="utf8") as _json_schema:
                        _schema_content = json.load(_json_schema)
                    self.schema_reader.procedure(_schema_link, domain, subdomain, model)
                    if model not in self.schema_reader.get_wrongs() or also_wrongs:
                        _scalar_attr = self.schema_reader.get_scalar_attribute()
                        _attributes = self.schema_reader.get_s4c_attrs()
                        _errors = self.schema_reader.get_errors()
                        _attr_log = self.schema_reader.get_attributes_log()
                        _esit, return_msg = self.db_helper.add_model((domain, subdomain, model,
                                                                      _scalar_attr["$schemaVersion"], _attributes,
                                                                      _errors, _attr_log, _schema_content),
                                                                     overwrite)
                        if not _esit:
                            print(return_msg)
                            if input("Would you like to continue?") in ["False", "false", "no", "No", "NO",
                                                                        "FALSE"]:
                                return
                    else:
                        self.unsaved_models.append((model, subdomain, domain))
        with open(self.result_folder + "Unsaved-Models.json", "w", encoding="utf8") as file:
            json.dump(self.unsaved_models, file, indent=2)

    def load_required_files(self):
        for domain in self.domains:
            print(f"Loading {domain}")
            self.loader.get_repo(link=self.base_link + domain + ".git", folder_name=domain)
        print("Domains loaded.")

    def load_domain_dict(self):
        main_dict = dict()
        main_dict["0"] = {}
        main_dict["0"]["0"] = None
        for domain in self.domains:
            self.loader.set_last_folder(domain)
            main_dict[domain] = self.loader.find_schemas()
        main_dict["0"]["0"] = self.loader.get_definition_schemas()
        self.location_schemas = main_dict

    def get_locations_schema(self):
        return self.location_schemas

    def get_domain_schemas(self, domain):
        return self.location_schemas[domain]

    def delete_local_files(self):
        self.loader.delete_local_data(True)

    def save_domain_dict(self):
        _keys_to_clean = ["AllSubjects", "ontologies_files"]
        with open(self.result_folder + "schemas_location.json", "w") as file:
            json.dump(self.location_schemas, file, indent=2)
