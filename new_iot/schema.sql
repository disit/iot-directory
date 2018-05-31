-- MySQL dump 10.16  Distrib 10.1.31-MariaDB, for Linux (x86_64)
--
-- Host: localhost    Database: iotdb
-- ------------------------------------------------------
-- Server version	10.1.31-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `contextbroker`
--

DROP TABLE IF EXISTS `contextbroker`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contextbroker` (
  `name` varchar(20) NOT NULL,
  `protocol` varchar(20) NOT NULL,
  `ip` varchar(20) NOT NULL,
  `port` varchar(5) NOT NULL,
  `uri` varchar(100) DEFAULT NULL,
  `login` varchar(20) DEFAULT NULL,
  `password` varchar(20) DEFAULT NULL,
  `latitude` varchar(20) DEFAULT NULL,
  `longitude` varchar(20) DEFAULT NULL,
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`name`),
  UNIQUE KEY `ip` (`ip`,`port`),
  UNIQUE KEY `uri` (`uri`),
  KEY `protocol` (`protocol`),
  CONSTRAINT `contextbroker_ibfk_1` FOREIGN KEY (`protocol`) REFERENCES `protocols` (`name`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `data_types`
--

DROP TABLE IF EXISTS `data_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `data_types` (
  `data_type` varchar(30) NOT NULL,
  PRIMARY KEY (`data_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `defaultcontestbrokerpolicy`
--

DROP TABLE IF EXISTS `defaultcontestbrokerpolicy`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `defaultpolicy`
--

DROP TABLE IF EXISTS `defaultpolicy`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `devices`
--

DROP TABLE IF EXISTS `devices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `devices` (
  `contextBroker` varchar(20) NOT NULL,
  `id` varchar(120) NOT NULL,
  `uri` text,
  `devicetype` varchar(80) NOT NULL,
  `kind` set('sensor','actuator','','') NOT NULL DEFAULT 'sensor',
  `mandatoryproperties` tinyint(1) NOT NULL,
  `mandatoryvalues` tinyint(1) NOT NULL,
  `macaddress` varchar(20) DEFAULT NULL,
  `model` varchar(20) DEFAULT NULL,
  `producer` varchar(20) DEFAULT NULL,
  `longitude` varchar(20) DEFAULT NULL,
  `latitude` varchar(20) DEFAULT NULL,
  `protocol` varchar(20) NOT NULL,
  `format` varchar(20) NOT NULL,
  `visibility` set('public','private','','') NOT NULL DEFAULT 'public',
  `frequency` varchar(20) DEFAULT NULL,
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`,`contextBroker`),
  KEY `contextBroker` (`contextBroker`),
  KEY `protocol` (`protocol`),
  KEY `format` (`format`),
  CONSTRAINT `devices_ibfk_1` FOREIGN KEY (`contextBroker`) REFERENCES `contextbroker` (`name`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `devices_ibfk_2` FOREIGN KEY (`protocol`) REFERENCES `protocols` (`name`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `devices_ibfk_3` FOREIGN KEY (`format`) REFERENCES `formats` (`name`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `event_values`
--

DROP TABLE IF EXISTS `event_values`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
  PRIMARY KEY (`cb`,`device`,`value_name`),
  KEY `data_type` (`data_type`),
  KEY `value_type` (`value_type`),
  CONSTRAINT `event_values_ibfk_1` FOREIGN KEY (`data_type`) REFERENCES `data_types` (`data_type`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `event_values_ibfk_2` FOREIGN KEY (`value_type`) REFERENCES `value_types` (`value_type`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `event_values_ibfk_3` FOREIGN KEY (`cb`, `device`) REFERENCES `devices` (`contextBroker`, `id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `formats`
--

DROP TABLE IF EXISTS `formats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `formats` (
  `name` varchar(20) NOT NULL,
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `functionalities`
--

DROP TABLE IF EXISTS `functionalities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mainmenu`
--

DROP TABLE IF EXISTS `mainmenu`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `model`
--

DROP TABLE IF EXISTS `model`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `model` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `description` varchar(100) DEFAULT NULL,
  `devicetype` varchar(80) NOT NULL,
  `kind` set('sensor','actuator','','') NOT NULL DEFAULT 'sensor',
  `producer` varchar(20) DEFAULT NULL,
  `frequency` varchar(20) DEFAULT NULL,
  `policy` varchar(20) DEFAULT NULL,
  `attributes` text,
  `link` varchar(100) DEFAULT NULL,
  `k1` varchar(50) DEFAULT NULL,
  `k2` varchar(50) DEFAULT NULL,
  `kgenerator` set('normal','special','','') NOT NULL DEFAULT 'normal',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  UNIQUE KEY `id` (`id`),
  KEY `policy` (`policy`),
  CONSTRAINT `model_ibfk_1` FOREIGN KEY (`policy`) REFERENCES `defaultpolicy` (`policyname`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `protocols`
--

DROP TABLE IF EXISTS `protocols`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `protocols` (
  `name` varchar(20) NOT NULL,
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `value_types`
--

DROP TABLE IF EXISTS `value_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `value_types` (
  `value_type` varchar(30) NOT NULL,
  `value_unit_default` varchar(30) NOT NULL,
  PRIMARY KEY (`value_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2018-05-30 20:38:25
