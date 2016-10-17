-- MySQL dump 10.13  Distrib 5.6.23, for Win64 (x86_64)
--
-- ------------------------------------------------------
-- Server version	5.6.29-log

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
-- Table structure for table `asset`
--

DROP TABLE IF EXISTS `asset`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `asset` (
  `asset_id` int(11) NOT NULL AUTO_INCREMENT,
  `asset_datetime` datetime NOT NULL,
  `asset_ip` varchar(45) NOT NULL,
  `asset_user_agent` varchar(255) NOT NULL,
  `asset_author` varchar(255) NOT NULL,
  `asset_title` text NOT NULL,
  `asset_description` text NOT NULL,
  `asset_type` varchar(45) NOT NULL,
  `asset_content` text NOT NULL,
  `asset_deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`asset_id`)
) ENGINE=MyISAM AUTO_INCREMENT=16 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `entry`
--

DROP TABLE IF EXISTS `entry`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `entry` (
  `entry_id` int(11) NOT NULL AUTO_INCREMENT,
  `entry_datetime` datetime DEFAULT NULL,
  `entry_ip` varchar(45) DEFAULT NULL,
  `entry_user_agent` text,
  `entry_jam_id` int(11) DEFAULT NULL,
  `entry_jam_number` int(11) DEFAULT NULL,
  `entry_title` text,
  `entry_description` text,
  `entry_author` varchar(45) DEFAULT NULL,
  `entry_url` varchar(255) DEFAULT NULL,
  `entry_screenshot_url` varchar(255) DEFAULT NULL,
  `entry_deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`entry_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1246 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `jam`
--

DROP TABLE IF EXISTS `jam`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jam` (
  `jam_id` int(11) NOT NULL AUTO_INCREMENT,
  `jam_datetime` datetime DEFAULT NULL,
  `jam_ip` varchar(45) DEFAULT NULL,
  `jam_user_agent` text,
  `jam_jam_number` int(11) DEFAULT NULL,
  `jam_theme` text,
  `jam_start_datetime` datetime DEFAULT NULL,
  `jam_deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`jam_id`)
) ENGINE=MyISAM AUTO_INCREMENT=87 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `session`
--

DROP TABLE IF EXISTS `session`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `session` (
  `session_id` varchar(255) NOT NULL,
  `session_user_id` int(11) DEFAULT NULL,
  `session_datetime_started` datetime DEFAULT NULL,
  `session_datetime_last_used` datetime DEFAULT NULL,
  PRIMARY KEY (`session_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `theme`
--

DROP TABLE IF EXISTS `theme`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `theme` (
  `theme_id` int(11) NOT NULL AUTO_INCREMENT,
  `theme_datetime` datetime NOT NULL,
  `theme_ip` varchar(45) NOT NULL,
  `theme_user_agent` varchar(255) NOT NULL,
  `theme_text` varchar(255) NOT NULL,
  `theme_author` varchar(255) NOT NULL,
  `theme_banned` tinyint(1) DEFAULT '0',
  `theme_deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`theme_id`)
) ENGINE=MyISAM AUTO_INCREMENT=440 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `themevote`
--

DROP TABLE IF EXISTS `themevote`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `themevote` (
  `themevote_id` int(11) NOT NULL AUTO_INCREMENT,
  `themevote_datetime` datetime NOT NULL,
  `themevote_ip` varchar(45) NOT NULL,
  `themevote_user_agent` varchar(255) NOT NULL,
  `themevote_theme_id` int(11) NOT NULL,
  `themevote_username` varchar(255) NOT NULL,
  `themevote_type` int(11) NOT NULL,
  PRIMARY KEY (`themevote_id`)
) ENGINE=MyISAM AUTO_INCREMENT=6362 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_username` varchar(45) NOT NULL,
  `user_datetime` datetime DEFAULT NULL,
  `user_register_ip` varchar(45) DEFAULT NULL,
  `user_register_user_agent` varchar(255) DEFAULT NULL,
  `user_display_name` varchar(45) DEFAULT NULL,
  `user_password_salt` varchar(255) DEFAULT NULL,
  `user_password_hash` varchar(255) DEFAULT NULL,
  `user_password_iterations` int(11) DEFAULT NULL,
  `user_last_login_datetime` datetime DEFAULT NULL,
  `user_last_ip` varchar(45) DEFAULT NULL,
  `user_last_user_agent` varchar(255) DEFAULT NULL,
  `user_email` varchar(255) DEFAULT NULL,
  `user_twitter` varchar(255) DEFAULT NULL,
  `user_role` int(11) DEFAULT '0',
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `user_username_UNIQUE` (`user_username`)
) ENGINE=MyISAM AUTO_INCREMENT=524 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2016-10-17 13:28:39
