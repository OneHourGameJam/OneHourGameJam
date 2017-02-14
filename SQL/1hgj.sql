-- MySQL dump 10.13  Distrib 5.7.12, for Win64 (x86_64)
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
  `asset_ip` varchar(45) COLLATE utf8mb4_bin NOT NULL,
  `asset_user_agent` varchar(255) COLLATE utf8mb4_bin NOT NULL,
  `asset_author` varchar(255) COLLATE utf8mb4_bin NOT NULL,
  `asset_title` mediumtext COLLATE utf8mb4_bin NOT NULL,
  `asset_description` mediumtext COLLATE utf8mb4_bin NOT NULL,
  `asset_type` varchar(45) COLLATE utf8mb4_bin NOT NULL,
  `asset_content` mediumtext COLLATE utf8mb4_bin NOT NULL,
  `asset_deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`asset_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
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
  `entry_ip` varchar(45) COLLATE utf8mb4_bin DEFAULT NULL,
  `entry_user_agent` mediumtext COLLATE utf8mb4_bin,
  `entry_jam_id` int(11) DEFAULT NULL,
  `entry_jam_number` int(11) DEFAULT NULL,
  `entry_title` mediumtext COLLATE utf8mb4_bin,
  `entry_description` mediumtext COLLATE utf8mb4_bin,
  `entry_author` varchar(45) COLLATE utf8mb4_bin DEFAULT NULL,
  `entry_url` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL,
  `entry_url_web` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL,
  `entry_url_windows` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL,
  `entry_url_linux` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL,
  `entry_url_mac` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL,
  `entry_url_android` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL,
  `entry_url_ios` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL,
  `entry_screenshot_url` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL,
  `entry_deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`entry_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`errorage_onehour`@`%`*/ /*!50003 TRIGGER entry_insert AFTER INSERT ON `entry` FOR EACH ROW 
BEGIN
    INSERT INTO `entry_changelog`
		(`change_id`,
		`change_type`,
		`change_datetime`,
		`entry_id`,
		`entry_datetime`,
		`entry_ip`,
		`entry_user_agent`,
		`entry_jam_id`,
		`entry_jam_number`,
		`entry_title`,
		`entry_description`,
		`entry_author`,
		`entry_url`,
		`entry_url_web`,
		`entry_url_windows`,
		`entry_url_linux`,
		`entry_url_mac`,
		`entry_url_android`,
		`entry_url_ios`,
		`entry_screenshot_url`,
		`entry_deleted`)
	VALUES
		(null,
		"INSERT",
		Now(),
		NEW.entry_id,
		NEW.entry_datetime,
		NEW.entry_ip,
		NEW.entry_user_agent,
		NEW.entry_jam_id,
		NEW.entry_jam_number,
		NEW.entry_title,
		NEW.entry_description,
		NEW.entry_author,
		NEW.entry_url,
		NEW.entry_url_web,
		NEW.entry_url_windows,
		NEW.entry_url_linux,
		NEW.entry_url_mac,
		NEW.entry_url_android,
		NEW.entry_url_ios,
		NEW.entry_screenshot_url,
		NEW.entry_deleted);
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`errorage_onehour`@`%`*/ /*!50003 TRIGGER entry_update BEFORE UPDATE ON `entry` FOR EACH ROW 
BEGIN
  IF OLD.entry_id = NEW.entry_id OR
	 OLD.entry_datetime = NEW.entry_datetime OR
	 OLD.entry_ip = NEW.entry_ip OR
	 OLD.entry_user_agent = NEW.entry_user_agent OR
	 OLD.entry_jam_id = NEW.entry_jam_id OR
	 OLD.entry_jam_number = NEW.entry_jam_number OR
	 OLD.entry_title = NEW.entry_title OR
	 OLD.entry_description = NEW.entry_description OR
	 OLD.entry_author = NEW.entry_author OR
	 OLD.entry_url = NEW.entry_url OR
	 OLD.entry_url_web = NEW.entry_url_web OR
	 OLD.entry_url_windows = NEW.entry_url_windows OR
	 OLD.entry_url_linux = NEW.entry_url_linux OR
	 OLD.entry_url_mac = NEW.entry_url_mac OR
	 OLD.entry_url_android = NEW.entry_url_android OR
	 OLD.entry_url_ios = NEW.entry_url_ios OR
	 OLD.entry_screenshot_url = NEW.entry_screenshot_url OR
	 OLD.entry_deleted = NEW.entry_deleted
  THEN BEGIN
    INSERT INTO `entry_changelog`
		(`change_id`,
		`change_type`,
		`change_datetime`,
		`entry_id`,
		`entry_datetime`,
		`entry_ip`,
		`entry_user_agent`,
		`entry_jam_id`,
		`entry_jam_number`,
		`entry_title`,
		`entry_description`,
		`entry_author`,
		`entry_url`,
		`entry_url_web`,
		`entry_url_windows`,
		`entry_url_linux`,
		`entry_url_mac`,
		`entry_url_android`,
		`entry_url_ios`,
		`entry_screenshot_url`,
		`entry_deleted`)
	VALUES
		(null,
		"UPDATE",
		Now(),
		NEW.entry_id,
		NEW.entry_datetime,
		NEW.entry_ip,
		NEW.entry_user_agent,
		NEW.entry_jam_id,
		NEW.entry_jam_number,
		NEW.entry_title,
		NEW.entry_description,
		NEW.entry_author,
		NEW.entry_url,
		NEW.entry_url_web,
		NEW.entry_url_windows,
		NEW.entry_url_linux,
		NEW.entry_url_mac,
		NEW.entry_url_android,
		NEW.entry_url_ios,
		NEW.entry_screenshot_url,
		NEW.entry_deleted);

  END; END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`errorage_onehour`@`%`*/ /*!50003 TRIGGER entry_delete BEFORE DELETE ON `entry` FOR EACH ROW 
BEGIN
    INSERT INTO `entry_changelog`
		(`change_id`,
		`change_type`,
		`change_datetime`,
		`entry_id`,
		`entry_datetime`,
		`entry_ip`,
		`entry_user_agent`,
		`entry_jam_id`,
		`entry_jam_number`,
		`entry_title`,
		`entry_description`,
		`entry_author`,
		`entry_url`,
		`entry_url_web`,
		`entry_url_windows`,
		`entry_url_linux`,
		`entry_url_mac`,
		`entry_url_android`,
		`entry_url_ios`,
		`entry_screenshot_url`,
		`entry_deleted`)
	VALUES
		(null,
		"DELETE",
		Now(),
		old.entry_id,
		old.entry_datetime,
		old.entry_ip,
		old.entry_user_agent,
		old.entry_jam_id,
		old.entry_jam_number,
		old.entry_title,
		old.entry_description,
		old.entry_author,
		old.entry_url,
		old.entry_url_web,
		old.entry_url_windows,
		old.entry_url_linux,
		old.entry_url_mac,
		old.entry_url_android,
		old.entry_url_ios,
		old.entry_screenshot_url,
		old.entry_deleted);
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `entry_changelog`
--

DROP TABLE IF EXISTS `entry_changelog`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `entry_changelog` (
  `change_id` int(11) NOT NULL AUTO_INCREMENT,
  `change_type` varchar(45) COLLATE utf8mb4_bin NOT NULL,
  `change_datetime` datetime NOT NULL,
  `entry_id` int(11) NOT NULL,
  `entry_datetime` datetime DEFAULT NULL,
  `entry_ip` varchar(45) COLLATE utf8mb4_bin DEFAULT NULL,
  `entry_user_agent` mediumtext COLLATE utf8mb4_bin,
  `entry_jam_id` int(11) DEFAULT NULL,
  `entry_jam_number` int(11) DEFAULT NULL,
  `entry_title` mediumtext COLLATE utf8mb4_bin,
  `entry_description` mediumtext COLLATE utf8mb4_bin,
  `entry_author` varchar(45) COLLATE utf8mb4_bin DEFAULT NULL,
  `entry_url` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL,
  `entry_url_web` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL,
  `entry_url_windows` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL,
  `entry_url_linux` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL,
  `entry_url_mac` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL,
  `entry_url_android` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL,
  `entry_url_ios` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL,
  `entry_screenshot_url` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL,
  `entry_deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`change_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
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
  `jam_ip` varchar(45) COLLATE utf8mb4_bin DEFAULT NULL,
  `jam_user_agent` mediumtext COLLATE utf8mb4_bin,
  `jam_jam_number` int(11) DEFAULT NULL,
  `jam_theme` mediumtext COLLATE utf8mb4_bin,
  `jam_start_datetime` datetime DEFAULT NULL,
  `jam_deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`jam_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
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
  `theme_ip` varchar(45) COLLATE utf8mb4_bin NOT NULL,
  `theme_user_agent` varchar(255) COLLATE utf8mb4_bin NOT NULL,
  `theme_text` varchar(255) COLLATE utf8mb4_bin NOT NULL,
  `theme_author` varchar(255) COLLATE utf8mb4_bin NOT NULL,
  `theme_banned` tinyint(1) DEFAULT '0',
  `theme_deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`theme_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
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
  `themevote_ip` varchar(45) COLLATE utf8mb4_bin NOT NULL,
  `themevote_user_agent` varchar(255) COLLATE utf8mb4_bin NOT NULL,
  `themevote_theme_id` int(11) NOT NULL,
  `themevote_username` varchar(255) COLLATE utf8mb4_bin NOT NULL,
  `themevote_type` int(11) NOT NULL,
  PRIMARY KEY (`themevote_id`)
) ENGINE=MyISAM AUTO_INCREMENT=8659 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_username` varchar(45) COLLATE utf8mb4_bin NOT NULL,
  `user_datetime` datetime DEFAULT NULL,
  `user_register_ip` varchar(45) COLLATE utf8mb4_bin DEFAULT NULL,
  `user_register_user_agent` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL,
  `user_display_name` varchar(45) COLLATE utf8mb4_bin DEFAULT NULL,
  `user_password_salt` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL,
  `user_password_hash` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL,
  `user_password_iterations` int(11) DEFAULT NULL,
  `user_last_login_datetime` datetime DEFAULT NULL,
  `user_last_ip` varchar(45) COLLATE utf8mb4_bin DEFAULT NULL,
  `user_last_user_agent` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL,
  `user_email` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL,
  `user_twitter` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL,
  `user_bio` mediumtext COLLATE utf8mb4_bin,
  `user_role` int(11) DEFAULT '0',
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `user_username_UNIQUE` (`user_username`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping events for database 'errorage_onehourgamejam'
--

--
-- Dumping routines for database 'errorage_onehourgamejam'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2017-02-14  9:33:55
