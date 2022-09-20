#!/usr/bin/env python
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
import harvester as hv
import dbschemahelper as db
import payloadsingestor as ingstr
import statics
import rule_generator as RG

if __name__ == "__main__":
    # LOAD CONFIG #
    _config = statics.load_config("./config.json")
    base_link = _config["repository_base_link"]
    dictionary_link = _config["s4c_dictionary_link"]
    download_folder = _config["download_folder"]
    result_folder = _config["results_folder"]
    db_config_file = _config["db_config_file"]
    domains = _config["domains"]
    _harvest_models = _config["harvest_smartdatamodels"]

    # EXECUTE CONFIG #
    statics.create_folders([download_folder, result_folder])
    dbconfig = statics.load_db_config(db_config_file)
    _db_helper = db.DbSchemaHelper(dbconfig)

    _hv = hv.SmartDataModelsHarvester(
                base_link=base_link,
                domains=domains,
                download_folder=download_folder,
                result_folder=result_folder,
                database=_db_helper
            )


    if _harvest_models:
        _overwrite = _config["overwrite_database_models"]
        _also_wrongs = _config["save_wrong_models"]
        _hv.create_db_from_dict(also_wrongs=_also_wrongs, overwrite=_overwrite)

    _ingestor = ingstr.PayloadsIngestor(_db_helper, dictionary_link)

    # MODEL INGESTION
    '''
     # Aggiorna il databse in base alle regole che vengono inserite su s4c:
    # bisogna che sia presente una regola tipo: s4c_rule value_type value
    _logs = _db_helper.get_external_update()
    while len(_logs) > 0:
        _log = _logs.pop(0)
        _model = _log[0]
        _subdomain = _log[1]
        _domain = _log[2]
        _version = _log[3]
        _attrs = _log[4]
        _attrsLog = _log[5]
        _keys = list(_attrsLog.keys())
        print(_keys,"------+"*3)
        _new_attribute_keys = list(statics.ATTRIBUTE_MASK.keys())
        while len(_keys) > 0:
            _update_attr = False
            _attribute = _keys.pop()
            _obj = _attrsLog[_attribute]
            _messages_iterator = len(_obj) - 1
            while _messages_iterator >= 0:
                _msg = _obj[_messages_iterator]
                _msg = _msg.rsplit()
                # Check if its a rule
                if len(_msg)>0 and _msg[0] == "s4c_rule":
                    _msg.pop(0)
                    _message_word_iterator = 0
                    # Use existing attribute, or create a new one
                    if _attribute not in _attrs.keys():
                        _db_helper.create_attribute_if_not_exists(_attribute, _model, _subdomain, _domain, _version)
                        _attr = dict(statics.ATTRIBUTE_MASK)
                        _attr["value_name"] = _attribute
                    else:
                        _attr = _attrs[_attribute]


                    _rule=_ingestor.rule_generator.create_general_rules(_domain, _subdomain, _model, _attr )
                    _ingestor.database.add_rule(_rule, False)


                    # attr ready - now update or set attributes field
                    # Read msg attributes. Each of them need to be [.., field_name, value, ..]
                    _delete_msg = False
                    while _message_word_iterator < (len(_msg)-1):
                        _field = _msg[_message_word_iterator]
                        if _field in _new_attribute_keys:   # il field è valido
                            _value = _msg[_message_word_iterator + 1]  # prendo il value

                            if _config["overwrite_rules"] or _attr["checked"] == "False":    # Correggo l'attributo se non checked, o se forced

                                _attr["checked"] = "True"
                                _attr[_field] = _value
                                _db_helper.set_attribute(_model, _subdomain, _domain, _version,
                                                         _attribute, _attr)
                                _delete_msg = True
                                _update_attr = True
                        _message_word_iterator += 2
                    if _delete_msg:
                        _obj.pop(_messages_iterator)
                _messages_iterator -= 1
            if _update_attr:    # messo True quando viene cambiato qualcosa
                _db_helper.update_attributes_log(_model, _subdomain, _domain, _version,
                                                   attribute_name=_attribute, new_log=_obj) # Elimino i messaggi caricando solo quelli
                                                                                            # rimanenti in _obj
    
    
    if _config["all_organizations"]:
        _brokers = _db_helper.get_brokers()
    else:
        _brokers = _db_helper.get_brokers(_config["organization"])
    while len(_brokers) > 0:
        _broker = _brokers.pop()
        _organization = _broker[0]
        _context_broker = _broker[1]
        _link = _broker[2]
        _multitenancy = _broker[3]
        _ngsi_version = _broker[4]
        _service = _broker[5]
        _service_path = _broker[6]
        _status = _broker[7]
        _header = ""

        if _multitenancy:
            _header = "{'Fiware-Service': '"+_service+"'}"
        if _ngsi_version == "v2":
            _link += "/v2/entities"
            _analyzed_payloads = _ingestor.open_link(_link, header=_header)
            _ingestor.analize_results(_analyzed_payloads, _context_broker, _multitenancy, _service, _service_path, _organization, _config["prefix_rules"])'''
