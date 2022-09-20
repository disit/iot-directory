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
from git import Repo
import json
import re
import pandas as pd
import os
from jsonschema import validate
import keyvalueToNormalized as kvtn
import normalizedToKeyvalue as ntkv

def ordered(obj):
    if isinstance(obj, dict):
        return sorted((k, ordered(v)) for k, v in obj.items())
    if isinstance(obj, list):
        return sorted(ordered(x) for x in obj)
    else:
        return obj

s = ordered(a) == ordered(b)
sd = None
# Expect dictionary
def find_key_from_dict(self, target_key, delete, entry_list, path):
    for _key in entry_list.keys():
        if _key == target_key:
            result_temp = entry_list[target_key]
            if delete:
                entry_list.pop(target_key, None)
            return result_temp
        else:
            if type(entry_list[_key]) in [dict, list]:
                if type(entry_list[_key]) is list:
                    temp_dict = {}
                    iterator = 0
                    for i in range(len(entry_list[_key])):
                        temp_dict["_temp-" + str(iterator)] = entry_list[_key][i]
                        iterator += 1
                else:
                    temp_dict = entry_list[_key]
                path.append(_key)
                temp_res = self.find_key_from_dict(target_key, delete, temp_dict)
                if temp_res is not None:
                    print(path)
                    return temp_res
                else:
                    path.remove(_key)


def find_extra_schema(folder):
    if not os.path.exists(folder):
        print(f'NOT Warning: Extra schema folder {folder} does not exist')
        return list()

    out = list()
    files = os.listdir(folder)

    for f in files:
        tested = os.path.join(folder, f)
        if os.path.isfile(tested) and f.endswith('schema.json') and f.startswith('schema.json'):
            out.append(tested)
        elif os.path.isdir(tested):
            out.extend(find_extra_schema(tested))

    return out

out = find_extra_schema("/home/giuseppe/PycharmProjects/auto_downl_ghub/SmartCities/dataModel.Building/")
resutl = dict()
for schema_path in out:
    resutl[os.path.basename(os.path.dirname(schema_path))] = schema_path

#print(resutl)

link = "/home/giuseppe/PycharmProjects/auto_downl_ghub/SmartCities/dataModel.Building/Building/schema.json"
# Need to take another look
def calculate_model(schema_uri):
    with open("/home/giuseppe/PycharmProjects/auto_downl_ghub/mask.json") as mask:
        new_model = json.load(mask)
        new_model["d_attributes"] = list()
        with open(schema_uri) as opened:
            json_ = dict(json.load(opened))
            if not json_["type"] == "object":
                print("NOT AN OBJECT")
            else:
                properties = (json_["allOf"])
                for propierty in properties:
                    for key in propierty:
                        if type(propierty[key]) is dict:
                            # Function to clean up the attributes
                            _attr_type = propierty[key].pop("type", None)
                            _attr_cat = propierty[key].pop("category", None)
                            #print("AFTER:", proprierty[key])
                            if _attr_type is not None and _attr_cat is not None:
                                for _key in propierty[key]:
                                    if _key in ["allOf", "anyOf", "oneOf", "not"]:
                                        print("DO SOMETHING HERE")
                                    print("LIST OF KEYS:", propierty[key])
                                    if "type" in propierty[key][_key].keys():
                                        # Posso saltare gli attributi GSMA e quelli LOCATION. in quanto qualche altro componente
                                        # li crea seguendo il formalismo.

                                        # Un attributo di questi presenti
                                        new_attribute = dict()
                                        #print("Attr type:", attr_type)
                                        #print(proprierty[key][_key])
                                        #print("KEYS:", proprierty[key][_key].keys())
                                        #print("TYPE:", proprierty[key][_key]["type"])
                                        #print(proprierty[key][_key].keys())
                                        base = propierty[key][_key]
                                        attr_type = base["type"]
                                        new_attribute["is_primitive"] = "1"
                                        new_attribute["content_type"] = "null"
                                        new_attribute["content"] = "null"
                                        new_attribute["attr_description"] = base["description"]
                                        if attr_type == "array":
                                            #print("IM an array. How can you handle me?")
                                            #print(base["items"])
                                            new_attribute["is_primitive"] = "0"
                                            new_attribute["content_type"] = "null"
                                            if "type" in base["items"]:
                                                #print("HERADS")
                                                #print(base["items"])
                                                print()
                                            new_attribute["content"] = base["items"]
                                        if _attr_type == "object":
                                            new_attribute["is_primitive"] = "0"
                                            new_attribute["content_type"] = "null"
                                            new_attribute["content"] = base["properties"]
                                            #print("Need handle: "+ properties[key][_key] +"is an object")
                                        new_attribute["value_name"] = "SDM-"+"Building-"+_key
                                        new_attribute["data_type"] = attr_type
                                        new_attribute["value_type"] = "manually-assign"
                                        new_attribute["editable"] = "0"
                                        new_attribute["value_unit"] = "manually-assign"
                                        new_attribute["healthiness_criteria"] = "manually-assign"
                                        new_attribute["healthiness_value"] = "manually-assign"
                                        #print("Attributes of SMART DATA MODEL")
                                        #print(proprierty[key][_key])
                                        #print("Created attribute")
                                        #print(new_attribute)
                                        new_model["d_attributes"].append(new_attribute)

            new_model["name"] = "Smart Data Model - Building"
            new_model["device_type"] = ""
            new_model["frequency"] = 10
            new_model["kind"] = "sensor"
            new_model["subnature"] = "null"
            new_model["static_attributes"] = "null"
            new_model["service"] = "null"
            new_model["service_path"] = "null"
            new_model["producer"] = "Fiware"
            new_model["description"] = json_["description"]

            return new_model


def load_repository(link, repo_name="Repo"):
    Repo.clone_from(link, "./"+repo_name+"/")
    links = read_gitmodules("./"+repo_name+"/.gitmodules")
    for link in links:
        end_of_link = re.findall("dataModel.*[^git]", link)[0]
        Repo.clone_from(link, "./"+repo_name+"/"+end_of_link[0:len(end_of_link)-1]) # Se non metti -1 resta il punto finale TODO

def read_gitmodules(gitmodules_path):
    links = []
    iterator = 0
    with open(gitmodules_path) as modules:
        for line in modules:
            if iterator % 2 == 1:
                line = modules.readline()
                link = re.findall("http.*git$", line)
                links.append(link[0])
            iterator += 1
    return links