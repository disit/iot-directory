CREATE SCHEMA `iotdb` DEFAULT CHARACTER SET latin1;

USE `iotdb`;

CREATE TABLE `protocols` (
  `name` varchar(20) NOT NULL,
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `formats` (
  `name` varchar(20) NOT NULL,
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `data_types` (
  `data_type` varchar(30) NOT NULL,
  PRIMARY KEY (`data_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `value_types` (
  `value_type` varchar(30) NOT NULL,
  `value_unit_default` varchar(30) NOT NULL,
  PRIMARY KEY (`value_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `access_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `accessed_by` varchar(100) NOT NULL,
  `target_entity_type` varchar(50) NOT NULL,
  `access_type` varchar(50) NOT NULL,
  `entity_name` varchar(100) DEFAULT NULL,
  `notes` text,
  `result` set('success','faliure','') DEFAULT NULL,
  `organization` varchar(50) DEFAULT 'DISIT',
  PRIMARY KEY (`id`,`time`,`accessed_by`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `association_rules` (
  `input_data_type` varchar(30) DEFAULT NULL,
  `input_value_type` varchar(120) DEFAULT NULL,
  `input_context_broker` varchar(20) DEFAULT NULL,
  `input_device_type` varchar(80) DEFAULT NULL,
  `input_model` varchar(40) DEFAULT NULL,
  `input_protocol` varchar(20) DEFAULT NULL,
  `input_format` varchar(20) DEFAULT NULL,
  `input_value_unit` varchar(20) DEFAULT NULL,
  `output_data_type` varchar(30) DEFAULT NULL,
  `output_value_type` varchar(120) DEFAULT NULL,
  `output_value_unit` varchar(20) DEFAULT NULL,
  `output_context_broker` varchar(20) DEFAULT NULL,
  `output_device_type` varchar(80) DEFAULT NULL,
  `output_model` varchar(40) DEFAULT NULL,
  `output_protocol` varchar(20) DEFAULT NULL,
  `output_format` varchar(20) DEFAULT NULL,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `support` int(11) NOT NULL,
  `confidence` int(11) NOT NULL,
  `lift` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `bulkload_status` (
  `username` varchar(100) NOT NULL,
  `is_bulk_processing` tinyint(1) NOT NULL,
  `number_processed` int(11) NOT NULL,
  `totale` int(11) NOT NULL,
  `is_finished` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `contextbroker` (
  `name` varchar(40) NOT NULL,
  `protocol` varchar(20) NOT NULL,
  `ip` varchar(100) DEFAULT NULL,
  `port` varchar(5) NOT NULL,
  `uri` varchar(100) DEFAULT NULL,
  `login` varchar(20) DEFAULT NULL,
  `password` varchar(20) DEFAULT NULL,
  `latitude` varchar(20) DEFAULT NULL,
  `longitude` varchar(20) DEFAULT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `accesslink` varchar(100) NOT NULL,
  `accessport` varchar(5) NOT NULL,
  `sha` varchar(100) DEFAULT NULL,
  `organization` varchar(50) DEFAULT 'DISIT',
  `apikey` varchar(40) DEFAULT NULL,
  `visibility` set('public','private','') NOT NULL DEFAULT 'private',
  `version` varchar(50) DEFAULT NULL,
  `path` varchar(100) DEFAULT NULL,
  `kind` set('external','internal') DEFAULT NULL,
  PRIMARY KEY (`name`),
  UNIQUE KEY `ip` (`ip`,`port`),
  UNIQUE KEY `uri` (`uri`),
  KEY `protocol` (`protocol`),
  CONSTRAINT `contextbroker_ibfk_1` FOREIGN KEY (`protocol`) REFERENCES `protocols` (`name`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `defaultcontestbrokerpolicy` (
  `policyname` varchar(30) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `contextbroker` varchar(40) NOT NULL,
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
  `contextbroker` varchar(40) NOT NULL,
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
  `contextBroker` varchar(40) NOT NULL,
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
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
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
  `cb` varchar(40) NOT NULL,
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
  `contextBroker` varchar(40) NOT NULL,
  `id` varchar(120) NOT NULL,
  `uri` text,
  `devicetype` varchar(80) NOT NULL,
  `kind` set('sensor','actuator') NOT NULL DEFAULT 'sensor',
  `mandatoryproperties` tinyint(1) NOT NULL DEFAULT '0',
  `mandatoryvalues` tinyint(1) NOT NULL DEFAULT '0',
  `macaddress` varchar(20) DEFAULT NULL,
  `model` varchar(50) DEFAULT NULL,
  `producer` varchar(20) DEFAULT NULL,
  `longitude` varchar(20) DEFAULT NULL,
  `latitude` varchar(20) DEFAULT NULL,
  `protocol` varchar(20) NOT NULL,
  `format` varchar(20) NOT NULL,
  `visibility` set('public','private') NOT NULL DEFAULT 'public',
  `frequency` varchar(20) DEFAULT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
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
  `cb` varchar(40) NOT NULL,
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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
  `contextbroker` varchar(40) NOT NULL,
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `temporary_devices` (
  `username` varchar(100) CHARACTER SET latin1 DEFAULT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `contextBroker` varchar(40) NOT NULL,
  `id` varchar(120) NOT NULL,
  `uri` text,
  `devicetype` varchar(80) DEFAULT NULL,
  `kind` set('sensor','actuator','') DEFAULT NULL,
  `status` set('valid','invalid') DEFAULT 'invalid',
  `macaddress` varchar(20) DEFAULT NULL,
  `model` varchar(50) DEFAULT NULL,
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
  CONSTRAINT `temporary_devices_ibfk_1` FOREIGN KEY (`contextBroker`) REFERENCES `contextbroker` (`name`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `temporary_event_values` (
  `cb` varchar(40) NOT NULL,
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*
-- Query: SELECT * FROM iotdb.defaultpolicy
-- Date: 2019-10-28 18:45
*/
INSERT INTO `defaultpolicy` (`policyname`,`active`,`contextbroker`,`protocol`,`format`,`healthiness_criteria`,`healthiness_value`) VALUES ('advances',0,'mqttUNIFI','mqtt','csv','refresh_rate','300');
INSERT INTO `defaultpolicy` (`policyname`,`active`,`contextbroker`,`protocol`,`format`,`healthiness_criteria`,`healthiness_value`) VALUES ('basic',1,'orionUNIFI','ngsi','json','refresh_rate','300');

/*
-- Query: SELECT * FROM iotdb.formats
-- Date: 2019-10-28 18:46
*/
INSERT INTO `formats` (`name`) VALUES ('csv');
INSERT INTO `formats` (`name`) VALUES ('json');
INSERT INTO `formats` (`name`) VALUES ('xml');

/*
-- Query: SELECT * FROM iotdb.functionalities
-- Date: 2019-10-28 18:46
*/
INSERT INTO `functionalities` (`id`,`functionality`,`ToolAdmin`,`AreaManager`,`Manager`,`Public`,`link`,`view`,`class`,`RootAdmin`) VALUES (1,'View Sensors and Actuators',1,1,1,0,'value.php','view','#mainContentCnt',1);
INSERT INTO `functionalities` (`id`,`functionality`,`ToolAdmin`,`AreaManager`,`Manager`,`Public`,`link`,`view`,`class`,`RootAdmin`) VALUES (2,'Search Sensors and Actuators on the Map',1,1,1,0,'value.php','popup','#addMap1SA',1);
INSERT INTO `functionalities` (`id`,`functionality`,`ToolAdmin`,`AreaManager`,`Manager`,`Public`,`link`,`view`,`class`,`RootAdmin`) VALUES (3,'New Sensor/Actuator',1,1,0,0,'value.php','popup','#addValueBtn',1);
INSERT INTO `functionalities` (`id`,`functionality`,`ToolAdmin`,`AreaManager`,`Manager`,`Public`,`link`,`view`,`class`,`RootAdmin`) VALUES (4,'Modify Sensor/Actuator',1,1,0,0,'value.php','popup','.editDashBtn',1);
INSERT INTO `functionalities` (`id`,`functionality`,`ToolAdmin`,`AreaManager`,`Manager`,`Public`,`link`,`view`,`class`,`RootAdmin`) VALUES (5,'Delete Sensor/Actuator',1,1,0,0,'value.php','popup','.delDashBtn',1);
INSERT INTO `functionalities` (`id`,`functionality`,`ToolAdmin`,`AreaManager`,`Manager`,`Public`,`link`,`view`,`class`,`RootAdmin`) VALUES (6,'View Sensor/Actuator on Map',1,1,0,0,'value.php','popup','.addMapBtn',1);
INSERT INTO `functionalities` (`id`,`functionality`,`ToolAdmin`,`AreaManager`,`Manager`,`Public`,`link`,`view`,`class`,`RootAdmin`) VALUES (7,'View Devices',1,1,1,0,'devices.php','view','#mainContentCnt',1);
INSERT INTO `functionalities` (`id`,`functionality`,`ToolAdmin`,`AreaManager`,`Manager`,`Public`,`link`,`view`,`class`,`RootAdmin`) VALUES (8,'Search Devices on the Map',1,1,1,0,'devices.php','popup','#displayDevicesMap',1);
INSERT INTO `functionalities` (`id`,`functionality`,`ToolAdmin`,`AreaManager`,`Manager`,`Public`,`link`,`view`,`class`,`RootAdmin`) VALUES (9,'New Device',1,1,0,0,'devices.php','popup','#addDeviceBtn',1);
INSERT INTO `functionalities` (`id`,`functionality`,`ToolAdmin`,`AreaManager`,`Manager`,`Public`,`link`,`view`,`class`,`RootAdmin`) VALUES (10,'Modify Device',1,1,0,0,'devices.php','popup','.editDashBtn',1);
INSERT INTO `functionalities` (`id`,`functionality`,`ToolAdmin`,`AreaManager`,`Manager`,`Public`,`link`,`view`,`class`,`RootAdmin`) VALUES (11,'Delete Device',1,1,0,0,'devices.php','popup','.delDashBtn',1);
INSERT INTO `functionalities` (`id`,`functionality`,`ToolAdmin`,`AreaManager`,`Manager`,`Public`,`link`,`view`,`class`,`RootAdmin`) VALUES (12,'View Device on Map',1,1,1,0,'devices.php','popup','.addMapBtn',1);
INSERT INTO `functionalities` (`id`,`functionality`,`ToolAdmin`,`AreaManager`,`Manager`,`Public`,`link`,`view`,`class`,`RootAdmin`) VALUES (13,'View Context Brokers',1,0,0,0,'contextbroker.php','view','.mainContentCnt',1);
INSERT INTO `functionalities` (`id`,`functionality`,`ToolAdmin`,`AreaManager`,`Manager`,`Public`,`link`,`view`,`class`,`RootAdmin`) VALUES (14,'Search Context Brokers On the Map',1,0,0,0,'contextbroker.php','popup','#displayDevicesMapCB',1);
INSERT INTO `functionalities` (`id`,`functionality`,`ToolAdmin`,`AreaManager`,`Manager`,`Public`,`link`,`view`,`class`,`RootAdmin`) VALUES (15,'New Context Broker',1,0,0,0,'contextbroker.php','popup','#addContextBrokerBtn',1);
INSERT INTO `functionalities` (`id`,`functionality`,`ToolAdmin`,`AreaManager`,`Manager`,`Public`,`link`,`view`,`class`,`RootAdmin`) VALUES (16,'Modify Context Broker',1,0,0,0,'contextbroker.php','popup','.editDashBtn',1);
INSERT INTO `functionalities` (`id`,`functionality`,`ToolAdmin`,`AreaManager`,`Manager`,`Public`,`link`,`view`,`class`,`RootAdmin`) VALUES (17,'Delete Context Broker',1,0,0,0,'contextbroker.php','popup','.delDashBtn',1);
INSERT INTO `functionalities` (`id`,`functionality`,`ToolAdmin`,`AreaManager`,`Manager`,`Public`,`link`,`view`,`class`,`RootAdmin`) VALUES (18,'View Device Manager Board',0,0,1,0,'devices.php','view','#managerBoard',0);
INSERT INTO `functionalities` (`id`,`functionality`,`ToolAdmin`,`AreaManager`,`Manager`,`Public`,`link`,`view`,`class`,`RootAdmin`) VALUES (19,'Stub for gathering devices',1,0,0,0,'contextbroker.php','popup','.viewDashBtn',1);
INSERT INTO `functionalities` (`id`,`functionality`,`ToolAdmin`,`AreaManager`,`Manager`,`Public`,`link`,`view`,`class`,`RootAdmin`) VALUES (20,'View Syntesis',1,0,0,0,'devices.php','view','#synthesis',0);
INSERT INTO `functionalities` (`id`,`functionality`,`ToolAdmin`,`AreaManager`,`Manager`,`Public`,`link`,`view`,`class`,`RootAdmin`) VALUES (21,'View Syntesis',1,0,0,0,'value.php','view','#synthesis',0);
INSERT INTO `functionalities` (`id`,`functionality`,`ToolAdmin`,`AreaManager`,`Manager`,`Public`,`link`,`view`,`class`,`RootAdmin`) VALUES (22,'View Syntesis',1,0,0,0,'contextbroker.php','view','#synthesis',0);
INSERT INTO `functionalities` (`id`,`functionality`,`ToolAdmin`,`AreaManager`,`Manager`,`Public`,`link`,`view`,`class`,`RootAdmin`) VALUES (23,'View Devices',1,1,1,0,'mydevices.php','view','#mainContentCnt',1);
INSERT INTO `functionalities` (`id`,`functionality`,`ToolAdmin`,`AreaManager`,`Manager`,`Public`,`link`,`view`,`class`,`RootAdmin`) VALUES (24,'Search Devices on the Map',0,0,1,0,'mydevices.php','popup','#displayDevicesMap',0);
INSERT INTO `functionalities` (`id`,`functionality`,`ToolAdmin`,`AreaManager`,`Manager`,`Public`,`link`,`view`,`class`,`RootAdmin`) VALUES (25,'New Device',0,0,0,0,'mydevices.php','popup','#addDeviceBtn',0);
INSERT INTO `functionalities` (`id`,`functionality`,`ToolAdmin`,`AreaManager`,`Manager`,`Public`,`link`,`view`,`class`,`RootAdmin`) VALUES (26,'Modify Device',1,1,0,0,'mydevices.php','popup','.editDashBtn',1);
INSERT INTO `functionalities` (`id`,`functionality`,`ToolAdmin`,`AreaManager`,`Manager`,`Public`,`link`,`view`,`class`,`RootAdmin`) VALUES (27,'Delete Device',1,1,1,0,'mydevices.php','popup','.delDashBtn',1);
INSERT INTO `functionalities` (`id`,`functionality`,`ToolAdmin`,`AreaManager`,`Manager`,`Public`,`link`,`view`,`class`,`RootAdmin`) VALUES (28,'View Device on Map',1,1,1,0,'mydevices.php','popup','.addMapBtn',1);
INSERT INTO `functionalities` (`id`,`functionality`,`ToolAdmin`,`AreaManager`,`Manager`,`Public`,`link`,`view`,`class`,`RootAdmin`) VALUES (29,'View Device Manager Board',1,1,1,0,'mydevices.php','view','#managerBoard',1);
INSERT INTO `functionalities` (`id`,`functionality`,`ToolAdmin`,`AreaManager`,`Manager`,`Public`,`link`,`view`,`class`,`RootAdmin`) VALUES (30,'View Syntesis',0,0,0,0,'mydevices.php','view','#synthesis',0);
INSERT INTO `functionalities` (`id`,`functionality`,`ToolAdmin`,`AreaManager`,`Manager`,`Public`,`link`,`view`,`class`,`RootAdmin`) VALUES (31,'View Devices',1,0,0,0,'alldevices.php','view','#mainContentCnt',1);
INSERT INTO `functionalities` (`id`,`functionality`,`ToolAdmin`,`AreaManager`,`Manager`,`Public`,`link`,`view`,`class`,`RootAdmin`) VALUES (32,'Search Devices on the Map',1,0,0,0,'alldevices.php','popup','#displayDevicesMap',1);
INSERT INTO `functionalities` (`id`,`functionality`,`ToolAdmin`,`AreaManager`,`Manager`,`Public`,`link`,`view`,`class`,`RootAdmin`) VALUES (33,'New Device',1,0,0,0,'alldevices.php','popup','#addDeviceBtn',1);
INSERT INTO `functionalities` (`id`,`functionality`,`ToolAdmin`,`AreaManager`,`Manager`,`Public`,`link`,`view`,`class`,`RootAdmin`) VALUES (34,'Modify Device',1,0,0,0,'alldevices.php','popup','.editDashBtn',1);
INSERT INTO `functionalities` (`id`,`functionality`,`ToolAdmin`,`AreaManager`,`Manager`,`Public`,`link`,`view`,`class`,`RootAdmin`) VALUES (35,'View Device on Map',1,1,1,0,'alldevices.php','popup','.addMapBtn',1);
INSERT INTO `functionalities` (`id`,`functionality`,`ToolAdmin`,`AreaManager`,`Manager`,`Public`,`link`,`view`,`class`,`RootAdmin`) VALUES (36,'View Device Manager Board',0,0,1,0,'alldevices.php','view','#managerBoard',0);
INSERT INTO `functionalities` (`id`,`functionality`,`ToolAdmin`,`AreaManager`,`Manager`,`Public`,`link`,`view`,`class`,`RootAdmin`) VALUES (37,'View Syntesis',1,0,0,0,'alldevices.php','view','#synthesis',1);
INSERT INTO `functionalities` (`id`,`functionality`,`ToolAdmin`,`AreaManager`,`Manager`,`Public`,`link`,`view`,`class`,`RootAdmin`) VALUES (38,'Delete Device',1,0,0,0,'alldevices.php','popup','.delDashBtn',1);

/*
-- Query: SELECT * FROM iotdb.mainmenu
-- Date: 2019-10-28 18:47
*/
INSERT INTO `mainmenu` (`id`,`linkUrl`,`linkId`,`icon`,`text`,`privileges`,`userType`,`externalApp`,`openMode`,`iconColor`,`active`,`pageTitle`) VALUES (1,'value.php','valueLink','fa fa-podcast','Sensors&amp;Actuators','[\"AreaManager\",\"ToolAdmin\", \"RootAdmin\"]','any','no','samePage','#f3cf58',1,'IoT Directory: Sensors and Actuators');
INSERT INTO `mainmenu` (`id`,`linkUrl`,`linkId`,`icon`,`text`,`privileges`,`userType`,`externalApp`,`openMode`,`iconColor`,`active`,`pageTitle`) VALUES (2,'devices.php','devicesLink','fa fa-microchip','Devices','[\"AreaManager\",\"ToolAdmin\", \"RootAdmin\"]','any','no','samePage','#33cc33',1,'IoT Directory: Devices');
INSERT INTO `mainmenu` (`id`,`linkUrl`,`linkId`,`icon`,`text`,`privileges`,`userType`,`externalApp`,`openMode`,`iconColor`,`active`,`pageTitle`) VALUES (3,'contextbroker.php','contextbrokerLink','fa fa-object-group','Context Brokers','[\"ToolAdmin\", \"RootAdmin\"]','any','no','samePage','#d84141',1,'IoT Directory: Context Brokers');
INSERT INTO `mainmenu` (`id`,`linkUrl`,`linkId`,`icon`,`text`,`privileges`,`userType`,`externalApp`,`openMode`,`iconColor`,`active`,`pageTitle`) VALUES (5,'users.php','userLink','fa fa-user','List of Users','[\"ToolAdmin\", \"RootAdmin\"]','any','no','samePage','#FFFFFF',1,'IoT Directory: Users');
INSERT INTO `mainmenu` (`id`,`linkUrl`,`linkId`,`icon`,`text`,`privileges`,`userType`,`externalApp`,`openMode`,`iconColor`,`active`,`pageTitle`) VALUES (6,'account.php','accountManagementLink','fa fa-lock','Account','[\"AreaManager\",\"Manager\",\"ToolAdmin\", \"RootAdmin\"]','any','no','samePage','#ff9933',1,'IoT Directory: Account');
INSERT INTO `mainmenu` (`id`,`linkUrl`,`linkId`,`icon`,`text`,`privileges`,`userType`,`externalApp`,`openMode`,`iconColor`,`active`,`pageTitle`) VALUES (8,'bulkUpdate.php','bulkDUpdateLink','fa fa-microchip','Update Devices ','[]','any','no','samePage','#33cc33',0,'IoT Directory: Device Bulk Updates');
INSERT INTO `mainmenu` (`id`,`linkUrl`,`linkId`,`icon`,`text`,`privileges`,`userType`,`externalApp`,`openMode`,`iconColor`,`active`,`pageTitle`) VALUES (9,'bulkCBUpdate.php','bulkCBUpdateLink','fa fa-microchip','Update COntext Broker','[]','any','no','samePage','#33cc33',0,'IoT Directory: Device Bulk Updates');
INSERT INTO `mainmenu` (`id`,`linkUrl`,`linkId`,`icon`,`text`,`privileges`,`userType`,`externalApp`,`openMode`,`iconColor`,`active`,`pageTitle`) VALUES (10,'setup.php','setupLink','fa fa-cogs','Settings','[\"AreaManager\",\"ToolAdmin\", \"RootAdmin\"]','any','no','samePage','#00e6e6',1,'IoT Directory: Setup');
INSERT INTO `mainmenu` (`id`,`linkUrl`,`linkId`,`icon`,`text`,`privileges`,`userType`,`externalApp`,`openMode`,`iconColor`,`active`,`pageTitle`) VALUES (11,'mydevices.php','mydevicesLink','fa fa-microchip','Devices','[\"AreaManager\",\"Manager\",\"ToolAdmin\", \"RootAdmin\"]','any','no','samePage','#33cc33',1,'IoT Directory: Devices');
INSERT INTO `mainmenu` (`id`,`linkUrl`,`linkId`,`icon`,`text`,`privileges`,`userType`,`externalApp`,`openMode`,`iconColor`,`active`,`pageTitle`) VALUES (12,'alldevices.php','alldevicesLink','fa fa-microchip','Devices','[\"RootAdmin\"]','any','no','samePage','#33cc33',1,'IoT Directory: Devices');
INSERT INTO `mainmenu` (`id`,`linkUrl`,`linkId`,`icon`,`text`,`privileges`,`userType`,`externalApp`,`openMode`,`iconColor`,`active`,`pageTitle`) VALUES (13,'model.php','modelLink','fa fa-microchip','Models','[\"AreaManager\",\"ToolAdmin\", \"RootAdmin\"]','any','no','samePage','#33cc33',1,'IoT Directory: Models');

/*
-- Query: SELECT * FROM iotdb.protocols
-- Date: 2019-10-28 18:47
*/
INSERT INTO `protocols` (`name`) VALUES ('amqp');
INSERT INTO `protocols` (`name`) VALUES ('coap');
INSERT INTO `protocols` (`name`) VALUES ('mqtt');
INSERT INTO `protocols` (`name`) VALUES ('ngsi');
INSERT INTO `protocols` (`name`) VALUES ('sigfox');

/*
-- Query: SELECT * FROM iotdb.value_types
-- Date: 2019-10-28 18:48
*/
INSERT INTO `value_types` (`value_type`,`value_unit_default`) VALUES ('actuator_canceller','');
INSERT INTO `value_types` (`value_type`,`value_unit_default`) VALUES ('actuator_deleted','');
INSERT INTO `value_types` (`value_type`,`value_unit_default`) VALUES ('actuator_deletion_date','');
INSERT INTO `value_types` (`value_type`,`value_unit_default`) VALUES ('annual_PM10_exceedance_count','#');
INSERT INTO `value_types` (`value_type`,`value_unit_default`) VALUES ('audio','');
INSERT INTO `value_types` (`value_type`,`value_unit_default`) VALUES ('available_bikes','#');
INSERT INTO `value_types` (`value_type`,`value_unit_default`) VALUES ('average_vehicle_distance','m');
INSERT INTO `value_types` (`value_type`,`value_unit_default`) VALUES ('average_vehicle_speed','km/h');
INSERT INTO `value_types` (`value_type`,`value_unit_default`) VALUES ('average_vehicle_time','s');
INSERT INTO `value_types` (`value_type`,`value_unit_default`) VALUES ('battery_level','%');
INSERT INTO `value_types` (`value_type`,`value_unit_default`) VALUES ('benzene_concentration','ppm');
INSERT INTO `value_types` (`value_type`,`value_unit_default`) VALUES ('blue_code_count','#');
INSERT INTO `value_types` (`value_type`,`value_unit_default`) VALUES ('broken_bikes','#');
INSERT INTO `value_types` (`value_type`,`value_unit_default`) VALUES ('button','');
INSERT INTO `value_types` (`value_type`,`value_unit_default`) VALUES ('car_park_exit_rate','');
INSERT INTO `value_types` (`value_type`,`value_unit_default`) VALUES ('car_park_fill_rate','');
INSERT INTO `value_types` (`value_type`,`value_unit_default`) VALUES ('car_park_free_places','#');
INSERT INTO `value_types` (`value_type`,`value_unit_default`) VALUES ('car_park_occupancy','%');
INSERT INTO `value_types` (`value_type`,`value_unit_default`) VALUES ('car_park_occupied_places','#');
INSERT INTO `value_types` (`value_type`,`value_unit_default`) VALUES ('car_park_status','');
INSERT INTO `value_types` (`value_type`,`value_unit_default`) VALUES ('car_park_validity_status','');
INSERT INTO `value_types` (`value_type`,`value_unit_default`) VALUES ('charging_level','%');
INSERT INTO `value_types` (`value_type`,`value_unit_default`) VALUES ('charging_state','');
INSERT INTO `value_types` (`value_type`,`value_unit_default`) VALUES ('charging_station_state','');
INSERT INTO `value_types` (`value_type`,`value_unit_default`) VALUES ('CO2_concentration','ppm');
INSERT INTO `value_types` (`value_type`,`value_unit_default`) VALUES ('CO_concentration','ppm');
INSERT INTO `value_types` (`value_type`,`value_unit_default`) VALUES ('creation_date','');
INSERT INTO `value_types` (`value_type`,`value_unit_default`) VALUES ('current','A');
INSERT INTO `value_types` (`value_type`,`value_unit_default`) VALUES ('daily_O3_exceedance_count','#');
INSERT INTO `value_types` (`value_type`,`value_unit_default`) VALUES ('date','-');
INSERT INTO `value_types` (`value_type`,`value_unit_default`) VALUES ('datetime','-');
INSERT INTO `value_types` (`value_type`,`value_unit_default`) VALUES ('dew_point','°C');
INSERT INTO `value_types` (`value_type`,`value_unit_default`) VALUES ('duration','s');
INSERT INTO `value_types` (`value_type`,`value_unit_default`) VALUES ('electro_conductivity','mS/cm');
INSERT INTO `value_types` (`value_type`,`value_unit_default`) VALUES ('electro_valve_action','');
INSERT INTO `value_types` (`value_type`,`value_unit_default`) VALUES ('energy','KW/h');
INSERT INTO `value_types` (`value_type`,`value_unit_default`) VALUES ('entity_creator','');
INSERT INTO `value_types` (`value_type`,`value_unit_default`) VALUES ('entity_desc','');
INSERT INTO `value_types` (`value_type`,`value_unit_default`) VALUES ('fan','');
INSERT INTO `value_types` (`value_type`,`value_unit_default`) VALUES ('freeze','');
INSERT INTO `value_types` (`value_type`,`value_unit_default`) VALUES ('free_stalls','#');
INSERT INTO `value_types` (`value_type`,`value_unit_default`) VALUES ('fuel_price','euro');
INSERT INTO `value_types` (`value_type`,`value_unit_default`) VALUES ('fuel_type','');
INSERT INTO `value_types` (`value_type`,`value_unit_default`) VALUES ('glucose_percentage','');
INSERT INTO `value_types` (`value_type`,`value_unit_default`) VALUES ('green_code_count','#');
INSERT INTO `value_types` (`value_type`,`value_unit_default`) VALUES ('H2S_concentration','ppm');
INSERT INTO `value_types` (`value_type`,`value_unit_default`) VALUES ('hour_O3_max','');
INSERT INTO `value_types` (`value_type`,`value_unit_default`) VALUES ('humidity','%');
INSERT INTO `value_types` (`value_type`,`value_unit_default`) VALUES ('ir','');
INSERT INTO `value_types` (`value_type`,`value_unit_default`) VALUES ('lamp_level','%');
INSERT INTO `value_types` (`value_type`,`value_unit_default`) VALUES ('lamp_temperature','°C');
INSERT INTO `value_types` (`value_type`,`value_unit_default`) VALUES ('latitude','');
INSERT INTO `value_types` (`value_type`,`value_unit_default`) VALUES ('latitude_longitude','');
INSERT INTO `value_types` (`value_type`,`value_unit_default`) VALUES ('leaf_wetness','%');
INSERT INTO `value_types` (`value_type`,`value_unit_default`) VALUES ('light','lux');
INSERT INTO `value_types` (`value_type`,`value_unit_default`) VALUES ('longitude','');
INSERT INTO `value_types` (`value_type`,`value_unit_default`) VALUES ('max_temperature','°C');
INSERT INTO `value_types` (`value_type`,`value_unit_default`) VALUES ('min_temperature','°C');
INSERT INTO `value_types` (`value_type`,`value_unit_default`) VALUES ('monitor_status','-');
INSERT INTO `value_types` (`value_type`,`value_unit_default`) VALUES ('moonrise_time','');
INSERT INTO `value_types` (`value_type`,`value_unit_default`) VALUES ('moonset_time','');
INSERT INTO `value_types` (`value_type`,`value_unit_default`) VALUES ('motion_detection','');
INSERT INTO `value_types` (`value_type`,`value_unit_default`) VALUES ('NO2_concentration','ppm');
INSERT INTO `value_types` (`value_type`,`value_unit_default`) VALUES ('NO_concentration','ppm');
INSERT INTO `value_types` (`value_type`,`value_unit_default`) VALUES ('O3_concentration','ppm');
INSERT INTO `value_types` (`value_type`,`value_unit_default`) VALUES ('orientation','');
INSERT INTO `value_types` (`value_type`,`value_unit_default`) VALUES ('people_count','#');
INSERT INTO `value_types` (`value_type`,`value_unit_default`) VALUES ('perceived_temperature','°C');
INSERT INTO `value_types` (`value_type`,`value_unit_default`) VALUES ('PM10_concentration','ppm');
INSERT INTO `value_types` (`value_type`,`value_unit_default`) VALUES ('PM2.5_concentration','ppm');
INSERT INTO `value_types` (`value_type`,`value_unit_default`) VALUES ('pollen_concentration_level','');
INSERT INTO `value_types` (`value_type`,`value_unit_default`) VALUES ('pollen_concentration_trend','');
INSERT INTO `value_types` (`value_type`,`value_unit_default`) VALUES ('pollen_concentration_value','');
INSERT INTO `value_types` (`value_type`,`value_unit_default`) VALUES ('power','W');
INSERT INTO `value_types` (`value_type`,`value_unit_default`) VALUES ('power_meter_m','');
INSERT INTO `value_types` (`value_type`,`value_unit_default`) VALUES ('power_meter_s','');
INSERT INTO `value_types` (`value_type`,`value_unit_default`) VALUES ('precipitation_type','');
INSERT INTO `value_types` (`value_type`,`value_unit_default`) VALUES ('presence_detection_e','');
INSERT INTO `value_types` (`value_type`,`value_unit_default`) VALUES ('pressure','hPa');
INSERT INTO `value_types` (`value_type`,`value_unit_default`) VALUES ('rain','mm');
INSERT INTO `value_types` (`value_type`,`value_unit_default`) VALUES ('red_code_count','#');
INSERT INTO `value_types` (`value_type`,`value_unit_default`) VALUES ('road_condition','');
INSERT INTO `value_types` (`value_type`,`value_unit_default`) VALUES ('salt_concentration','%');
INSERT INTO `value_types` (`value_type`,`value_unit_default`) VALUES ('sittings_count','#');
INSERT INTO `value_types` (`value_type`,`value_unit_default`) VALUES ('snow','');
INSERT INTO `value_types` (`value_type`,`value_unit_default`) VALUES ('soil_humidity','%');
INSERT INTO `value_types` (`value_type`,`value_unit_default`) VALUES ('soil_temperature','°C');
INSERT INTO `value_types` (`value_type`,`value_unit_default`) VALUES ('soil_water_potential','cbar');
INSERT INTO `value_types` (`value_type`,`value_unit_default`) VALUES ('sound_lv','');
INSERT INTO `value_types` (`value_type`,`value_unit_default`) VALUES ('SO_concentration','ppm');
INSERT INTO `value_types` (`value_type`,`value_unit_default`) VALUES ('speed','');
INSERT INTO `value_types` (`value_type`,`value_unit_default`) VALUES ('state_count','');
INSERT INTO `value_types` (`value_type`,`value_unit_default`) VALUES ('state_time','');
INSERT INTO `value_types` (`value_type`,`value_unit_default`) VALUES ('status','-');
INSERT INTO `value_types` (`value_type`,`value_unit_default`) VALUES ('stop','');
INSERT INTO `value_types` (`value_type`,`value_unit_default`) VALUES ('sunrise_time','');
INSERT INTO `value_types` (`value_type`,`value_unit_default`) VALUES ('sunset_time','');
INSERT INTO `value_types` (`value_type`,`value_unit_default`) VALUES ('sun_max_height','');
INSERT INTO `value_types` (`value_type`,`value_unit_default`) VALUES ('sun_max_height_hour','');
INSERT INTO `value_types` (`value_type`,`value_unit_default`) VALUES ('temperature','°C');
INSERT INTO `value_types` (`value_type`,`value_unit_default`) VALUES ('time','-');
INSERT INTO `value_types` (`value_type`,`value_unit_default`) VALUES ('timestamp','');
INSERT INTO `value_types` (`value_type`,`value_unit_default`) VALUES ('transits_count','#');
INSERT INTO `value_types` (`value_type`,`value_unit_default`) VALUES ('uv','');
INSERT INTO `value_types` (`value_type`,`value_unit_default`) VALUES ('vdc','V');
INSERT INTO `value_types` (`value_type`,`value_unit_default`) VALUES ('vehicle_concentration','');
INSERT INTO `value_types` (`value_type`,`value_unit_default`) VALUES ('vehicle_flow','car/h');
INSERT INTO `value_types` (`value_type`,`value_unit_default`) VALUES ('vehicle_occupancy','');
INSERT INTO `value_types` (`value_type`,`value_unit_default`) VALUES ('vehicle_speed_percentile','');
INSERT INTO `value_types` (`value_type`,`value_unit_default`) VALUES ('vehicle_threshold_perc','');
INSERT INTO `value_types` (`value_type`,`value_unit_default`) VALUES ('velocity','');
INSERT INTO `value_types` (`value_type`,`value_unit_default`) VALUES ('voltage','V');
INSERT INTO `value_types` (`value_type`,`value_unit_default`) VALUES ('waste_filling_rate','%');
INSERT INTO `value_types` (`value_type`,`value_unit_default`) VALUES ('water_consumption','l/h');
INSERT INTO `value_types` (`value_type`,`value_unit_default`) VALUES ('water_film','µm');
INSERT INTO `value_types` (`value_type`,`value_unit_default`) VALUES ('water_flowing','');
INSERT INTO `value_types` (`value_type`,`value_unit_default`) VALUES ('water_level','m');
INSERT INTO `value_types` (`value_type`,`value_unit_default`) VALUES ('white_code_count','#');
INSERT INTO `value_types` (`value_type`,`value_unit_default`) VALUES ('wind','');
INSERT INTO `value_types` (`value_type`,`value_unit_default`) VALUES ('wind_direction','deg');
INSERT INTO `value_types` (`value_type`,`value_unit_default`) VALUES ('wind_gust_speed','m/s');
INSERT INTO `value_types` (`value_type`,`value_unit_default`) VALUES ('wind_speed','m/s');
INSERT INTO `value_types` (`value_type`,`value_unit_default`) VALUES ('yellow_code_count','#');

/*
-- Query: SELECT * FROM iotdb.data_types
-- Date: 2019-10-28 18:44
*/
INSERT INTO `data_types` (`data_type`) VALUES ('binary');
INSERT INTO `data_types` (`data_type`) VALUES ('boolean');
INSERT INTO `data_types` (`data_type`) VALUES ('button');
INSERT INTO `data_types` (`data_type`) VALUES ('collection');
INSERT INTO `data_types` (`data_type`) VALUES ('date');
INSERT INTO `data_types` (`data_type`) VALUES ('datetime');
INSERT INTO `data_types` (`data_type`) VALUES ('float');
INSERT INTO `data_types` (`data_type`) VALUES ('html');
INSERT INTO `data_types` (`data_type`) VALUES ('identifier');
INSERT INTO `data_types` (`data_type`) VALUES ('image');
INSERT INTO `data_types` (`data_type`) VALUES ('integer');
INSERT INTO `data_types` (`data_type`) VALUES ('json');
INSERT INTO `data_types` (`data_type`) VALUES ('path');
INSERT INTO `data_types` (`data_type`) VALUES ('set');
INSERT INTO `data_types` (`data_type`) VALUES ('shape');
INSERT INTO `data_types` (`data_type`) VALUES ('string');
INSERT INTO `data_types` (`data_type`) VALUES ('switch');
INSERT INTO `data_types` (`data_type`) VALUES ('time');
INSERT INTO `data_types` (`data_type`) VALUES ('timestamp');
INSERT INTO `data_types` (`data_type`) VALUES ('url');
INSERT INTO `data_types` (`data_type`) VALUES ('vector');
INSERT INTO `data_types` (`data_type`) VALUES ('webpage');
INSERT INTO `data_types` (`data_type`) VALUES ('wkt');
INSERT INTO `data_types` (`data_type`) VALUES ('xml');

/*
-- Query: SELECT * FROM iotdb.defaultcontestbrokerpolicy
-- Date: 2019-10-28 18:45
*/
INSERT INTO `defaultcontestbrokerpolicy` (`policyname`,`active`,`contextbroker`,`protocol`,`format`) VALUES ('advances',0,'mqttUNIFI','mqtt','csv');
INSERT INTO `defaultcontestbrokerpolicy` (`policyname`,`active`,`contextbroker`,`protocol`,`format`) VALUES ('basic',1,'orionUNIFI','ngsi','json');
