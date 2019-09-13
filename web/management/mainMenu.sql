
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
(1, 'value.php', 'valueLink', 'fa fa-podcast', 'Sensors&amp;Actuators', '["AreaManager","Manager","ToolAdmin"]', 'any', 'no', 'samePage', '#f3cf58', 1, 'IoT Directory: Sensors and Actuators'),
(2, 'devices.php', 'devicesLink', 'fa fa-microchip', 'Devices', '["AreaManager","Manager","ToolAdmin"]', 'any', 'no', 'samePage', '#33cc33', 1, 'IoT Directory: Devices'),
(3, 'contextbroker.php', 'contextbrokerLink', 'fa fa-object-group', 'Context Brokers', '["ToolAdmin"]', 'any', 'no', 'samePage', '#d84141', 1, 'IoT Directory: Context Brokers'),
(5, 'users.php', 'userLink', 'fa fa-user', 'List of Users', '["ToolAdmin"]', 'any', 'no', 'samePage', '#FFFFFF', 1, 'IoT Directory: Users'),
(6, 'account.php', 'accountManagementLink', 'fa fa-lock', 'Account', '["AreaManager","Manager","ToolAdmin"]', 'any', 'no', 'samePage', '#ff9933', 1, 'IoT Directory: Account'),
(8, 'bulkUpdate.php', 'bulkDUpdateLink', 'fa fa-microchip', 'Update Devices ', '["ToolAdmin"]', 'any', 'no', 'samePage', '#33cc33', 0, 'IoT Directory: Device Bulk Updates'),
(10, 'setup.php', 'setupLink', 'fa fa-cogs', 'Settings', '["AreaManager","ToolAdmin"]', 'any', 'no', 'samePage', '#00e6e6', 1, 'IoT Directory: Setup'),
(9, 'bulkCBUpdate.php', 'bulkCBUpdateLink', 'fa fa-microchip', 'Update COntext Broker', '["ToolAdmin"]', 'any', 'no', 'samePage', '#33cc33', 0, 'IoT Directory: Device Bulk Updates');