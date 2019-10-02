CREATE TABLE `access_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `accessed_by` varchar(100) NOT NULL,
  `target_entity_type` varchar(50) NOT NULL,
  `access_type` varchar(30) NOT NULL,
  `entity_name` varchar(50) DEFAULT NULL,
  `notes` text,
  `result` set('success','faliure','') DEFAULT NULL,
  `organization` varchar(50) DEFAULT 'DISIT',
  PRIMARY KEY (`id`,`time`,`accessed_by`)
) ENGINE=InnoDB AUTO_INCREMENT=103132 DEFAULT CHARSET=latin1;

CREATE TABLE `bulkload_status` (
  `username` varchar(100) NOT NULL,
  `is_bulk_processing` tinyint(1) NOT NULL,
  `number_processed` int(11) NOT NULL,
  `totale` int(11) NOT NULL,
  `is_finished` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `contextbroker` (
  `name` varchar(20) NOT NULL,
  `protocol` varchar(20) NOT NULL,
  `ip` varchar(100) DEFAULT NULL,
  `port` varchar(5) NOT NULL,
  `uri` varchar(100) DEFAULT NULL,
  `login` varchar(20) DEFAULT NULL,
  `password` varchar(20) DEFAULT NULL,
  `latitude` varchar(20) DEFAULT NULL,
  `longitude` varchar(20) DEFAULT NULL,
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `accesslink` varchar(100) NOT NULL,
  `accessport` varchar(5) NOT NULL,
  `sha` varchar(100) DEFAULT NULL,
  `organization` varchar(50) DEFAULT 'DISIT',
  `apikey` varchar(40) DEFAULT NULL,
  `visibility` set('public','private','') NOT NULL DEFAULT 'private',
  `version` varchar(50) DEFAULT NULL,
  `path` varchar(100) DEFAULT NULL,
  `kind` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`name`),
  UNIQUE KEY `ip` (`ip`,`port`),
  UNIQUE KEY `uri` (`uri`),
  KEY `protocol` (`protocol`),
  CONSTRAINT `contextbroker_ibfk_1` FOREIGN KEY (`protocol`) REFERENCES `protocols` (`name`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `data_types` (
  `data_type` varchar(30) NOT NULL,
  PRIMARY KEY (`data_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `defaultcontestbrokerpolicy` (
  `policyname` varchar(30) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `contextbroker` varchar(20) NOT NULL,
  `protocol` varchar(20) NOT NULL,
  `format` varchar(20) NOT NULL,
  PRIMARY KEY (`policyname`),
  KEY `contextbroker` (`contextbroker`),
  KEY `protocol` (`protocol`),
  KEY `format` (`format`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `defaultpolicy` (
  `policyname` varchar(30) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `contextbroker` varchar(20) NOT NULL,
  `protocol` varchar(20) NOT NULL,
  `format` varchar(20) NOT NULL,
  `healthiness_criteria` set('refresh_rate','different_values','within_bounds','') NOT NULL DEFAULT 'refresh_rate',
  `healthiness_value` varchar(20) NOT NULL DEFAULT '300',
  PRIMARY KEY (`policyname`),
  KEY `contextbroker` (`contextbroker`),
  KEY `protocol` (`protocol`),
  KEY `format` (`format`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `deleted_devices` (
  `contextBroker` varchar(20) NOT NULL,
  `id` varchar(120) NOT NULL,
  `uri` text,
  `devicetype` varchar(80) NOT NULL,
  `kind` set('sensor','actuator') DEFAULT NULL,
  `mandatoryproperties` tinyint(1) NOT NULL,
  `mandatoryvalues` tinyint(1) NOT NULL,
  `macaddress` varchar(20) DEFAULT NULL,
  `model` varchar(50) DEFAULT NULL,
  `producer` varchar(20) DEFAULT NULL,
  `longitude` varchar(20) DEFAULT NULL,
  `latitude` varchar(20) DEFAULT NULL,
  `protocol` varchar(20) NOT NULL,
  `format` varchar(20) NOT NULL,
  `visibility` set('public','private') DEFAULT NULL,
  `frequency` varchar(20) DEFAULT NULL,
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted` date DEFAULT NULL,
  `privatekey` varchar(50) DEFAULT NULL,
  `certificate` varchar(50) DEFAULT NULL,
  `organization` varchar(50) DEFAULT 'DISIT',
  PRIMARY KEY (`id`,`contextBroker`),
  KEY `contextBroker` (`contextBroker`),
  KEY `protocol` (`protocol`),
  KEY `format` (`format`),
  CONSTRAINT `deleted_devices_ibfk_1` FOREIGN KEY (`contextBroker`) REFERENCES `contextbroker` (`name`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `deleted_devices_ibfk_2` FOREIGN KEY (`protocol`) REFERENCES `protocols` (`name`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `deleted_devices_ibfk_3` FOREIGN KEY (`format`) REFERENCES `formats` (`name`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `deleted_event_values` (
  `cb` varchar(20) NOT NULL,
  `device` varchar(120) NOT NULL,
  `value_name` varchar(120) NOT NULL,
  `data_type` varchar(30) NOT NULL,
  `value_type` varchar(120) DEFAULT NULL,
  `editable` tinyint(1) NOT NULL,
  `value_unit` varchar(30) DEFAULT NULL,
  `healthiness_criteria` set('refresh_rate','different_values','within_bounds','') DEFAULT NULL,
  `value_refresh_rate` int(11) DEFAULT NULL,
  `different_values` int(11) DEFAULT NULL,
  `value_bounds` varchar(10) DEFAULT NULL,
  `order` int(11) NOT NULL,
  PRIMARY KEY (`cb`,`device`,`value_name`),
  KEY `data_type` (`data_type`),
  KEY `value_type` (`value_type`),
  CONSTRAINT `deleted_event_values_ibfk_1` FOREIGN KEY (`data_type`) REFERENCES `data_types` (`data_type`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `deleted_event_values_ibfk_2` FOREIGN KEY (`value_type`) REFERENCES `value_types` (`value_type`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `deleted_event_values_ibfk_3` FOREIGN KEY (`cb`, `device`) REFERENCES `deleted_devices` (`contextBroker`, `id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `devices` (
  `contextBroker` varchar(20) NOT NULL,
  `id` varchar(120) NOT NULL,
  `uri` text,
  `devicetype` varchar(80) NOT NULL,
  `kind` set('sensor','actuator','') NOT NULL DEFAULT 'sensor',
  `mandatoryproperties` tinyint(1) NOT NULL,
  `mandatoryvalues` tinyint(1) NOT NULL,
  `macaddress` varchar(20) DEFAULT NULL,
  `model` varchar(50) DEFAULT NULL,
  `producer` varchar(20) DEFAULT NULL,
  `longitude` varchar(20) DEFAULT NULL,
  `latitude` varchar(20) DEFAULT NULL,
  `protocol` varchar(20) NOT NULL,
  `format` varchar(20) NOT NULL,
  `visibility` set('public','private','') NOT NULL DEFAULT 'public',
  `frequency` varchar(20) DEFAULT NULL,
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted` date DEFAULT NULL,
  `privatekey` varchar(100) DEFAULT NULL,
  `certificate` varchar(100) DEFAULT NULL,
  `organization` varchar(50) DEFAULT 'DISIT',
  PRIMARY KEY (`id`,`contextBroker`),
  KEY `contextBroker` (`contextBroker`),
  KEY `protocol` (`protocol`),
  KEY `format` (`format`),
  CONSTRAINT `devices_ibfk_1` FOREIGN KEY (`contextBroker`) REFERENCES `contextbroker` (`name`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `devices_ibfk_2` FOREIGN KEY (`protocol`) REFERENCES `protocols` (`name`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `devices_ibfk_3` FOREIGN KEY (`format`) REFERENCES `formats` (`name`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `edgegatewaytype` (
  `name` varchar(20) NOT NULL,
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `event_values` (
  `cb` varchar(20) NOT NULL,
  `device` varchar(120) NOT NULL,
  `value_name` varchar(120) NOT NULL,
  `data_type` varchar(30) NOT NULL,
  `value_type` varchar(120) DEFAULT NULL,
  `editable` tinyint(1) NOT NULL,
  `value_unit` varchar(30) DEFAULT NULL,
  `healthiness_criteria` set('refresh_rate','different_values','within_bounds','') DEFAULT NULL,
  `value_refresh_rate` int(11) DEFAULT NULL,
  `different_values` int(11) DEFAULT NULL,
  `value_bounds` varchar(10) DEFAULT NULL,
  `order` int(11) NOT NULL,
  `old_value_name` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`cb`,`device`,`value_name`),
  KEY `data_type` (`data_type`),
  KEY `value_type` (`value_type`),
  CONSTRAINT `event_values_ibfk_1` FOREIGN KEY (`data_type`) REFERENCES `data_types` (`data_type`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `event_values_ibfk_2` FOREIGN KEY (`value_type`) REFERENCES `value_types` (`value_type`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `event_values_ibfk_3` FOREIGN KEY (`cb`, `device`) REFERENCES `devices` (`contextBroker`, `id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `extractionRules` (
  `id` varchar(30) NOT NULL,
  `contextbroker` varchar(35) NOT NULL,
  `format` set('csv','json','xml','') NOT NULL DEFAULT 'json',
  `selector` text NOT NULL,
  `kind` set('value','property') DEFAULT NULL,
  `value_type` varchar(120) DEFAULT NULL,
  `value_unit` varchar(30) DEFAULT NULL,
  `data_type` varchar(30) DEFAULT NULL,
  `structure_flag` set('yes','no') DEFAULT 'no',
  `organization` varchar(30) DEFAULT NULL,
  `username` varchar(40) DEFAULT NULL,
  PRIMARY KEY (`id`,`contextbroker`),
  KEY `contextbroker_erfk_1` (`contextbroker`),
  CONSTRAINT `contextbroker_erfk_1` FOREIGN KEY (`contextbroker`) REFERENCES `contextbroker` (`name`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `fieldType` (
  `fieldName` varchar(80) NOT NULL,
  `menuType` varchar(40) DEFAULT NULL,
  `query` text,
  `fieldsHtml` text,
  `autocomplete` text,
  PRIMARY KEY (`fieldName`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `formats` (
  `name` varchar(20) NOT NULL,
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `functionalities` (
  `id` int(11) NOT NULL,
  `functionality` varchar(200) DEFAULT '0',
  `ToolAdmin` tinyint(1) NOT NULL DEFAULT '0',
  `AreaManager` tinyint(1) NOT NULL DEFAULT '0',
  `Manager` tinyint(1) NOT NULL DEFAULT '0',
  `Public` tinyint(1) NOT NULL DEFAULT '0',
  `link` varchar(200) DEFAULT NULL,
  `view` varchar(40) DEFAULT NULL,
  `class` varchar(200) DEFAULT NULL,
  `RootAdmin` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `mainmenu` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `linkUrl` varchar(200) NOT NULL,
  `linkId` varchar(200) DEFAULT NULL,
  `icon` varchar(200) DEFAULT NULL,
  `text` varchar(200) DEFAULT NULL,
  `privileges` text,
  `userType` varchar(45) DEFAULT 'any',
  `externalApp` varchar(3) DEFAULT 'no',
  `openMode` varchar(45) DEFAULT 'newTab',
  `iconColor` varchar(45) DEFAULT '#FFFFFF',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `pageTitle` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8;

CREATE TABLE `model` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `description` varchar(100) DEFAULT NULL,
  `devicetype` varchar(80) NOT NULL,
  `kind` set('sensor','actuator','') DEFAULT NULL,
  `producer` varchar(20) DEFAULT NULL,
  `frequency` varchar(20) DEFAULT NULL,
  `policy` varchar(20) DEFAULT NULL,
  `attributes` text,
  `link` varchar(100) DEFAULT NULL,
  `contextbroker` varchar(20) NOT NULL,
  `protocol` varchar(20) NOT NULL,
  `format` varchar(20) NOT NULL,
  `healthiness_criteria` set('refresh_rate','different_values','within_bounds','') NOT NULL DEFAULT 'refresh_rate',
  `healthiness_value` varchar(20) NOT NULL DEFAULT '300',
  `k1` varchar(50) DEFAULT NULL,
  `k2` varchar(50) DEFAULT NULL,
  `kgenerator` set('normal','special','authenticated','') DEFAULT NULL,
  `edgegateway_type` varchar(20) DEFAULT NULL,
  `organization` varchar(50) DEFAULT 'DISIT',
  `visibility` set('public','private') DEFAULT 'public',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  UNIQUE KEY `id` (`id`),
  KEY `policy` (`policy`),
  CONSTRAINT `model_ibfk_1` FOREIGN KEY (`policy`) REFERENCES `defaultpolicy` (`policyname`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8;

CREATE TABLE `protocols` (
  `name` varchar(20) NOT NULL,
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `temporary_devices` (
  `username` varchar(100) CHARACTER SET latin1 DEFAULT NULL,
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `contextBroker` varchar(20) NOT NULL,
  `id` varchar(120) NOT NULL,
  `uri` text,
  `devicetype` varchar(80) DEFAULT NULL,
  `kind` set('sensor','actuator','') DEFAULT NULL,
  `status` set('valid','invalid') DEFAULT 'invalid',
  `macaddress` varchar(20) DEFAULT NULL,
  `model` varchar(40) DEFAULT NULL,
  `longitude` varchar(20) DEFAULT NULL,
  `latitude` varchar(20) DEFAULT NULL,
  `protocol` varchar(20) DEFAULT NULL,
  `format` varchar(20) DEFAULT NULL,
  `frequency` varchar(20) DEFAULT NULL,
  `visibility` set('public','private','') NOT NULL DEFAULT 'private',
  `deleted` date DEFAULT NULL,
  `k1` varchar(40) DEFAULT NULL,
  `k2` varchar(40) DEFAULT NULL,
  `producer` varchar(20) DEFAULT NULL,
  `validity_msg` text,
  `edge_gateway_type` varchar(30) DEFAULT NULL,
  `edge_gateway_uri` text,
  `toDelete` varchar(10) DEFAULT NULL,
  `should_be_registered` set('yes','no') NOT NULL DEFAULT 'yes',
  `organization` varchar(50) DEFAULT 'DISIT',
  PRIMARY KEY (`id`,`contextBroker`),
  KEY `contextBroker` (`contextBroker`),
  KEY `protocol` (`protocol`),
  KEY `format` (`format`),
  KEY `id` (`id`),
  KEY `indTemporary` (`username`,`created`),
  CONSTRAINT `temporary_devices_ibfk_1` FOREIGN KEY (`contextBroker`) REFERENCES `contextbroker` (`name`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `temporary_event_values` (
  `cb` varchar(20) NOT NULL,
  `device` varchar(120) NOT NULL,
  `value_name` varchar(120) NOT NULL,
  `old_value_name` varchar(120) NOT NULL,
  `data_type` varchar(30) DEFAULT NULL,
  `value_type` varchar(120) DEFAULT NULL,
  `editable` varchar(5) DEFAULT NULL,
  `value_unit` varchar(30) DEFAULT NULL,
  `healthiness_criteria` set('refresh_rate','different_values','within_bounds','') DEFAULT NULL,
  `value_refresh_rate` int(11) DEFAULT NULL,
  `different_values` int(11) DEFAULT NULL,
  `value_bounds` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`cb`,`device`,`value_name`),
  KEY `data_type` (`data_type`),
  KEY `value_type` (`value_type`),
  KEY `temporary_event_values_ibfk_1` (`device`,`cb`),
  CONSTRAINT `temporary_event_values_ibfk_1` FOREIGN KEY (`device`, `cb`) REFERENCES `temporary_devices` (`id`, `contextBroker`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `users` (
  `IdUser` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) CHARACTER SET latin1 DEFAULT NULL,
  `password` varchar(32) CHARACTER SET latin1 DEFAULT NULL,
  `name` varchar(100) CHARACTER SET latin1 DEFAULT NULL,
  `surname` varchar(100) CHARACTER SET latin1 DEFAULT NULL,
  `organization` varchar(200) CHARACTER SET latin1 DEFAULT NULL,
  `email` varchar(200) CHARACTER SET latin1 DEFAULT NULL,
  `reg_data` timestamp NULL DEFAULT NULL,
  `status` int(11) DEFAULT NULL,
  `admin` varchar(32) NOT NULL DEFAULT 'Manager',
  `activationHash` varchar(32) CHARACTER SET latin1 DEFAULT NULL,
  PRIMARY KEY (`IdUser`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

CREATE TABLE `value_types` (
  `value_type` varchar(30) NOT NULL,
  `value_unit_default` varchar(30) NOT NULL,
  PRIMARY KEY (`value_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
