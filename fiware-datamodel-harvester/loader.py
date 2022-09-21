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

from git import Repo
import re
import os
import shutil


class Loader:
    def __init__(self, repo_base=""):
        self.tools_link = "https://github.com/smart-data-models/tools"
        self.repositories = []
        if repo_base == "":
            self.repo_base = os.path.dirname(__file__) + "Domains/"
        else:
            self.repo_base = repo_base + "Domains/"
        self.last_repo = ""
        self.definition_schemas = {}

    def get_tools(self):
        if not os.path.isdir("tools/"):
            Repo.clone_from(
                "https://github.com/smart-data-models/tools.git", "tools/")

    def get_repo(self, link, folder_name=""):
        name = folder_name
        if folder_name == "":
            name = "NoNameAssigned"
        os.makedirs(self.repo_base + name + "/", exist_ok=True)
        self.repositories.append(name)
        if not os.listdir(self.repo_base + name + "/"):
            self._load_repository(link, name)
        self.last_repo = name + "/"

    def change_base(self, repo_folder):
        self.last_repo = repo_folder

    def get_last_folder(self):
        return self.repo_base + self.last_repo

    def reset_last_folder(self):
        self.last_repo = ""

    def set_last_folder(self, folder):
        self.last_repo = folder

    def delete_local_data(self, also_tools=True):
        for repo in self.repositories:
            if os.path.isdir(self.repo_base + repo + "/"):
                shutil.rmtree(self.repo_base + repo)
                self.repositories.remove(repo)
        if also_tools:
            if os.path.isdir("tools/"):
                shutil.rmtree("tools/")

    # dataModels è una lista di subDomain, dai quali si vuole ottenere i vari schema
    # Esempio: nel caso di SmartCities (che è il dominio) ho Building, Parking...
    # base_root è consigliabile lasciarlo vuoto - Si assegna la cartella in cui sono presenti tutti i subdomain
    # Restituisce un dizionario:
    # <SUBDOMAIN>: <MODEL> : <Schema uri (abs path of schema.json)>
    def find_schemas(self, dataModels=[], base_root=""):
        working_root = base_root
        schemas_per_subdomain = dict()
        if base_root == "":
            working_root = self.get_last_folder()

        if len(dataModels) == 0:
            if working_root.endswith("data-models"):
                _temp = []
                files = os.listdir(working_root)
                for f in files:
                    if re.search("schema", f) and re.search(".json", f):
                        _temp.append(os.path.join(working_root, f))
                schemas_per_subdomain["common-schemas"] = _temp
            else:
                for subdomain in os.listdir(working_root):
                    if subdomain.startswith("dataModel."):
                        subdomain_name = subdomain[10:]
                        schemas = self._find_schemas_subdomain(
                            subdomain_name, working_root)
                        schemas_per_subdomain[subdomain_name] = schemas
        else:
            for subdomain in dataModels:
                if os.path.isdir(os.path.join(working_root, subdomain)):
                    print("IS DIR")
                schemas_per_subdomain[subdomain] = self._find_schemas_subdomain(
                    subdomain, working_root)

        return schemas_per_subdomain

    def _has_schema(self, folder):
        return os.path.exists(os.path.join(folder, "schema.json")) or os.path.exists(
            os.path.join(folder, "schema.jsonld"))

    # Viene chiamata da find_schemas - Mi cerca gli schema nella cartella dataModel.<SUBDOMAIN>, cioè
    # mi trova tutti gli schema che sono di un certo subdomain
    def _find_schemas_subdomain(self, subdomain, folder):
        result = dict()
        working_folder = folder + "/"
        if subdomain != "" and subdomain != "data-models":
            working_folder = folder + "/dataModel." + subdomain
        elif subdomain == "data-models":
            working_folder = folder + "/"
        _res = self._find_extra_schema(working_folder)
        out = _res[0]
        defs = _res[1]

        if subdomain != "data-models":
            for schema_path in out:
                result[os.path.basename(
                    os.path.dirname(schema_path))] = schema_path
            for _def in defs:
                self.definition_schemas[os.path.basename(
                    os.path.dirname(_def))] = _def
        return result

    def _find_extra_schema(self, folder):
        if not os.path.exists(folder):
            print(f'Warning: Extra schema folder {folder} does not exist')
            return list()

        out = list()
        def_schemas = list()
        files = os.listdir(folder)

        for f in files:
            tested = os.path.join(folder, f)
            if os.path.isfile(tested) and re.search("schema", f):
                if f == "schema.json":
                    out.append(tested)
                else:
                    if f.endswith(".json") and not f.endswith("DTDL.json"):
                        def_schemas.append(tested)

            elif os.path.isdir(tested):
                _res = self._find_extra_schema(tested)
                out.extend(_res[0])
                def_schemas.extend(_res[1])

        return out, def_schemas

    def _load_repository(self, link, repo_name="Repo"):
        Repo.clone_from(link, self.repo_base + repo_name + "/")
        gitmodule_uri = self._search_gitmodule(self.repo_base + repo_name)
        if gitmodule_uri and repo_name != "data-models":
            links = self._read_gitmodules(gitmodule_uri)
            for link in links:
                found = re.findall("dataModel.*[^git]", link)
                if len(found) > 0:
                    end_of_link = found[0]
                else:
                    print(
                        "\tThis "+link+" is a non-common link. Maybe it's duplicated, check it by yourself.")
                    end_of_link = "NNFF-" + \
                        re.sub('https://github.com/smart-data-models/', "", link)
                # Se non metti -1 resta il punto finale
                Repo.clone_from(link, self.repo_base + repo_name +
                                "/" + end_of_link[0:len(end_of_link) - 1])

    def _search_gitmodule(self, base_folder):
        for root, dirs, files in os.walk(base_folder):
            if ".gitmodules" in files:
                return os.path.join(root, ".gitmodules")
        return None

    def _read_gitmodules(self, gitmodules_path):
        links = []

        with open(gitmodules_path) as modules:
            for line in modules:
                link = re.findall("http.*git$", line)
                if len(link) == 1:
                    links.append(link[0])

        return links

    def get_definition_schemas(self):
        return self.definition_schemas
