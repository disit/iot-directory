
-- ho introdotto il campo active per rappresentare le voci del menu che sono correntemente attive
-- ho modificato il valore di "privileges" in modo che sia un valore JSON

CREATE TABLE `mainmenu` (
  `id` int(11) NOT NULL,
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
  `pageTitle` varchar(200) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `mainmenu`
--

INSERT INTO `mainmenu` (`id`, `linkUrl`, `linkId`, `icon`, `text`, `privileges`, `userType`, `externalApp`, `openMode`, `iconColor`, `active`, `pageTitle`) VALUES
('1', 'mydevices.php', 'mydevicesLink', 'fa fa-microchip', 'My IOT Sensors and Actuators', '[\"AreaManager\",\"Manager\",\"ToolAdmin\", \"RootAdmin\"]', 'any', 'no', 'samePage', '#33cc33', '1', 'My IOT Sensors and Actuators'),
('2', 'value.php', 'valueLink', 'fa fa-podcast', 'IOT Sensors and Actuators', '[\"AreaManager\",\"Manager\",\"ToolAdmin\", \"RootAdmin\"]', 'any', 'no', 'samePage', '#f3cf58', '1', 'IOT Sensor and Actuators'),
('3', 'devices.php', 'devicesLink', 'fa fa-microchip', 'IOT Devices ', '[\"AreaManager\",\"ToolAdmin\", \"RootAdmin\"]', 'any', 'no', 'samePage', '#33cc33', '1', 'IOT Devices '),
('4', 'contextbroker.php', 'contextbrokerLink', 'fa fa-object-group', 'IOT Brokers', '[\"AreaManager\",\"ToolAdmin\", \"RootAdmin\"]', 'any', 'no', 'samePage', '#d84141', '1', 'IOT Brokers'),
('5', 'model.php', 'modelLink', 'fa fa-microchip', 'IOT Device Models', '[\"AreaManager\",\"ToolAdmin\", \"RootAdmin\"]', 'any', 'no', 'samePage', '#33cc33', '1', 'IOT Device Models'),
('6', 'alldevices.php', 'alldevicesLink', 'fa fa-microchip', 'IOT Devices Management ', '[\"ToolAdmin\", \"RootAdmin\"]', 'any', 'no', 'samePage', '#33cc33', '1', 'IOT Devices Management '),
('7', 'bulkDeviceUpdate.php', 'devicesBulkLink', 'fa fa-microchip', 'IOT Devices Bulk Registration', '[\"AreaManager\",\"ToolAdmin\", \"RootAdmin\"]', 'any', 'no', 'samePage', '#33cc33', '1', 'IOT Devices Bulk Registration'),
('8', 'associationRules.php', 'associationRulesLink', 'fa fa-microchip', 'IOT Broker Periodic Upload', '[\"ToolAdmin\", \"RootAdmin\"]', 'any', 'no', 'samePage', '#33cc33', '1', 'IOT Broker Periodic Upload'),
('9', 'extractionRules.php', 'extractionRulesLink', 'fa fa-microchip', 'IOT Orion Broker Mapping Rules', '[\"ToolAdmin\", \"RootAdmin\"]', 'any', 'no', 'samePage', '#33cc33', '1', 'IOT Orion Broker Mapping Rules'),
('10', 'accessLog.php', 'accessLogLink', 'fa fa-microchip', 'Auditing IOT Directory DATA', '[\"AreaManager\",\"Manager\",\"ToolAdmin\", \"RootAdmin\"]', 'any', 'no', 'samePage', '#33cc33', '1', 'Auditing IOT Directory DATA'),
('11', 'danglingDevices.php', 'danglingDevicesLink', 'fa fa-microchip', 'Dangling Device', '[\"ToolAdmin\", \"RootAdmin\"]', 'any', 'no', 'samePage', '#33cc33', '1', 'Dangling Device'),
('12', 'deviceDiscovery.php', NULL, 'fa fa-microchip', 'IOT Device Discovery', '[\"RootAdmin\"]', 'any', 'no', 'samePage', '#33cc33', '1', 'IOT Device Discovery'),
('100', 'setup.php', 'setupLink', 'fa fa-cogs', 'Settings', '[\"AreaManager\",\"ToolAdmin\", \"RootAdmin\"]', 'any', 'no', 'samePage', '#00e6e6', '1', 'IoT Directory: Setup');
