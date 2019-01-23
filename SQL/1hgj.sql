-- MySQL dump 10.16  Distrib 10.1.37-MariaDB, for Win64 (AMD64)
--
-- ------------------------------------------------------
-- Server version	10.1.37-MariaDB-cll-lve

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
-- Table structure for table `admin_log`
--

DROP TABLE IF EXISTS `admin_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admin_log` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `log_datetime` datetime DEFAULT NULL,
  `log_ip` varchar(45) DEFAULT NULL,
  `log_user_agent` varchar(255) DEFAULT NULL,
  `log_admin_username` varchar(255) DEFAULT NULL,
  `log_subject_username` varchar(255) DEFAULT NULL,
  `log_type` varchar(45) DEFAULT NULL,
  `log_content` text,
  PRIMARY KEY (`log_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `admin_vote`
--

DROP TABLE IF EXISTS `admin_vote`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admin_vote` (
  `vote_id` int(11) NOT NULL AUTO_INCREMENT,
  `vote_datetime` datetime DEFAULT NULL,
  `vote_ip` varchar(45) DEFAULT NULL,
  `vote_user_agent` varchar(255) DEFAULT NULL,
  `vote_voter_username` varchar(255) DEFAULT NULL,
  `vote_subject_username` varchar(255) DEFAULT NULL,
  `vote_type` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`vote_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

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

-- --------------------------------------------------------

--
-- Table structure for table `config`
--

DROP TABLE IF EXISTS `config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `config` (
  `config_id` int(11) NOT NULL AUTO_INCREMENT,
  `config_lastedited` datetime NOT NULL,
  `config_lasteditedby` varchar(45) COLLATE utf8mb4_bin NOT NULL,
  `config_key` varchar(100) COLLATE utf8mb4_bin NOT NULL,
  `config_value` mediumtext COLLATE utf8mb4_bin NOT NULL,
  `config_category` varchar(255) COLLATE utf8mb4_bin NOT NULL,
  `config_description` varchar(255) COLLATE utf8mb4_bin NOT NULL,
  `config_type` mediumtext COLLATE utf8mb4_bin NOT NULL,
  `config_options` mediumtext COLLATE utf8mb4_bin NOT NULL,
  `config_editable` tinyint(1) NOT NULL,
  `config_required` tinyint(1) NOT NULL,
  `config_added_to_dictionary` tinyint(1) NOT NULL,
  PRIMARY KEY (`config_id`),
  UNIQUE KEY `config_key_UNIQUE` (`config_key`)
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
  `entry_url_source` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL,
  `entry_screenshot_url` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL,
  `entry_color` varchar(45) COLLATE utf8mb4_bin DEFAULT 'FFFFFF',
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
/*!50003 CREATE*/ /*!50017 DEFINER=`errora14`@`localhost`*/ /*!50003 TRIGGER `entry_insert` AFTER INSERT ON `entry` FOR EACH ROW BEGIN
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
		`entry_url_source`,
		`entry_screenshot_url`,
		`entry_color`,
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
		NEW.entry_url_source,
		NEW.entry_screenshot_url,
		NEW.entry_color,
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
/*!50003 CREATE*/ /*!50017 DEFINER=`errora14`@`localhost`*/ /*!50003 TRIGGER `entry_update` BEFORE UPDATE ON `entry` FOR EACH ROW BEGIN
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
	 OLD.entry_url_source = NEW.entry_url_source OR
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
		`entry_url_source`,
		`entry_screenshot_url`,
		`entry_color`,
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
		NEW.entry_url_source,
		NEW.entry_screenshot_url,
		NEW.entry_color,
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
/*!50003 CREATE*/ /*!50017 DEFINER=`errora14`@`localhost`*/ /*!50003 TRIGGER `entry_delete` BEFORE DELETE ON `entry` FOR EACH ROW BEGIN
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
		`entry_url_source`,
		`entry_screenshot_url`,
		`entry_color`,
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
		old.entry_url_source,
		old.entry_screenshot_url,
		old.entry_color,
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
  `entry_url_source` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL,
  `entry_screenshot_url` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL,
  `entry_color` varchar(45) COLLATE utf8mb4_bin DEFAULT NULL,
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
  `jam_username` varchar(255) COLLATE utf8mb4_bin NOT NULL,
  `jam_jam_number` int(11) DEFAULT NULL,
  `jam_theme` mediumtext COLLATE utf8mb4_bin,
  `jam_start_datetime` datetime DEFAULT NULL,
  `jam_colors` text COLLATE utf8mb4_bin,
  `jam_deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`jam_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `poll`
--

DROP TABLE IF EXISTS `poll`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `poll` (
  `poll_id` int(11) NOT NULL AUTO_INCREMENT,
  `poll_question` mediumtext COLLATE utf8mb4_bin,
  `poll_type` varchar(45) COLLATE utf8mb4_bin DEFAULT NULL,
  `poll_start_datetime` datetime DEFAULT NULL,
  `poll_end_datetime` datetime DEFAULT NULL,
  `poll_deleted` tinyint(4) DEFAULT '0',
  PRIMARY KEY (`poll_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `poll_option`
--

DROP TABLE IF EXISTS `poll_option`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `poll_option` (
  `option_id` int(11) NOT NULL AUTO_INCREMENT,
  `option_poll_id` int(11) DEFAULT NULL,
  `option_poll_text` mediumtext COLLATE utf8mb4_bin,
  PRIMARY KEY (`option_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `poll_vote`
--

DROP TABLE IF EXISTS `poll_vote`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `poll_vote` (
  `vote_id` int(11) NOT NULL AUTO_INCREMENT,
  `vote_option_id` int(11) DEFAULT NULL,
  `vote_username` varchar(45) COLLATE utf8mb4_bin DEFAULT NULL,
  `vote_deleted` tinyint(4) DEFAULT '0',
  PRIMARY KEY (`vote_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `satisfaction`
--

DROP TABLE IF EXISTS `satisfaction`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `satisfaction` (
  `satisfaction_id` int(11) NOT NULL AUTO_INCREMENT,
  `satisfaction_datetime` datetime NOT NULL,
  `satisfaction_ip` varchar(45) NOT NULL,
  `satisfaction_user_agent` varchar(255) NOT NULL,
  `satisfaction_question_id` varchar(255) NOT NULL,
  `satisfaction_username` varchar(45) NOT NULL,
  `satisfaction_score` int(11) NOT NULL,
  PRIMARY KEY (`satisfaction_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
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
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
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

/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

--
-- Dumping data for table `config`
--

LOCK TABLES `config` WRITE;
/*!40000 ALTER TABLE `config` DISABLE KEYS */;
INSERT INTO `config` 
VALUES 
(null,Now(),'-1','DEFAULT_SATURATION','160','NEW_JAM_DEFAULTS','Default saturation (0..255)','NUMBER','[]',1,1,1),
(null,Now(),'-1','DEFAULT_NUMBER_OF_COLORS','10','NEW_JAM_DEFAULTS','Default number of colors (0..16)','NUMBER','[]',1,1,1),
(null,Now(),'-1','TWITTER_ACCOUNT','','SOCIAL_MEDIA','Game Jam\'s twitter account, appears in the left menu.','TEXT','[]',1,0,1),(55,'2018-10-24 02:29:45','-1','TWITCH_ACCOUNT','','SOCIAL_MEDIA','Game Jam\'s twitch account, appears in the left menu.','TEXT','[]',1,0,1),
(null,Now(),'-1','IRC_ADDRESS','','SOCIAL_MEDIA','IRC address','TEXT','[]',1,0,1),
(null,Now(),'-1','IRC_CHANNEL','','SOCIAL_MEDIA','IRC Channel','TEXT','[]',1,0,1),
(null,Now(),'-1','IRC_CHAT_IN_BROWSER','','SOCIAL_MEDIA','IRC Chat in browser URL','TEXT','[]',1,0,1),
(null,Now(),'-1','DISCORD_INVITE_URL','','SOCIAL_MEDIA','Discord invite URL','TEXT','[]',1,0,1),
(null,Now(),'-1','TWITCH_API_STREAM_UPDATE_FREQUENCY','30','STREAM','The minimum number of seconds that have to pass between subsequent checks as to whether the stream is online on Twitch or not.','NUMBER','[]',1,1,0),
(null,Now(),'-1','THEME_DAYS_MARK_AS_OLD','90','THEME_SELECTION','How many days a theme can be on the list before it is marked as old.','NUMBER','[]',1,1,0),
(null,Now(),'-1','THEME_MIN_VOTES_TO_SCORE','10','THEME_SELECTION','Minimum number of votes a theme must receive for it to be considered rated.','NUMBER','[]',1,1,0),
(null,Now(),'-1','THEME_NUMBER_TO_MARK_TOP','5','THEME_SELECTION','Number of best voted themes to mark as \"top\".','NUMBER','[]',1,1,0),
(null,Now(),'-1','THEME_NUMBER_TO_MARK_KEEP','20','THEME_SELECTION','Number of best voted themes to keep for the next jam.','NUMBER','[]',1,1,0),
(null,Now(),'-1','JAMS_CONSIDERED_RECENT','10','THEME_SELECTION','Number of jams which are considered \'recent\' when calculating recent jam participation.','NUMBER','[]',1,1,0),
(null,Now(),'-1','SATISFACTION_RATINGS_TO_SHOW_SCORE','5','SATISFACTION','Total number of satisfaction ratings needed for them to become publicly visible.','NUMBER','[]',1,1,0),
(null,Now(),'-1','PEPPER','','SECURITY','Sitewide Pepper (used in password hashing), for security reasons this can only be changed manually in the config file.','TEXT','[]',0,1,0),
(null,Now(),'-1','SESSION_PASSWORD_ITERATIONS','','SECURITY','Number of hashing iterations for session IDs, for security reasons this can only be changed manually in the config file.','NUMBER','[]',0,1,0),
(null,Now(),'-1','STREAMER_TWITCH_NAME','','STREAM','Twitch name for the streamer for this jam','TEXT','[]',1,1,0),
(null,Now(),'-1','TWITCH_CLIENT_ID','','STREAM','Twitch client id for the API','TEXT','[]',0,1,0),
(null,Now(),'-1','RULES','<h3>When?</h3><p>Every <a href=\'https://www.google.com/search?q=20%3A00+UTC\' target=\'_BLANK\'>Saturday at 20:00 UTC</a>. The time in your local time and a countdown-timer should be in the top of the site though.</p><h3>Where?</h3><p>Right here! <a href=\'https://discord.gg/J86uTu9\' target=\'_BLANK\'>Joining our Discord server</a> is however a good idea.</p><h3>Is there a theme?</h3><p>Yes! At the start of the hour, the theme is announced on the site and <a href=\'https://discord.gg/J86uTu9\' target=\'_BLANK\'>on Discord</a>. Themes are suggested and voted by the community on the <a href=\'?page=themes\'>Theme Voting page</a>.</p><h3>How long do I have to finish?</h3><p>:D</p><h3>Do I have to submit within the hour?</h3><p>No. Keep working on your game until it\'s done, then submit.</p><h3>Can I use premade assets?</h3><p>You are free to use premade assets but it is recommended that you make at least one game from scratch. Some assets are provided on the <a href=\'?page=assets\'>Assets page</a> but you do not have to use them.</p><h3>Do I have to host the games myself?</h3><p>Yes, you\'ll need to host the game and submit the links here. We host the thumbnail though. If you don\'t have a website yourself, use something like dropbox / google drive, or itch.io / newgrounds. Ask on Discord for more suggestions.</p><h3>What happens after the jam?</h3><p>About 30 minutes after the jam ends, we host a stream on Twitch where all submitted games are played. The stream usually lasts about 2 hours. It\'s not always the same people streaming though, so keep an eye on Discord to see who\'s streaming.</p><h3>What if I don\'t finish in time?</h3><p>If you finish and submit your game before the stream ends, we\'ll play it on stream, even if it took you more than an hour to finish.</p><h3>Can I participate after the jam has ended?</h3><p>If you come late you can still participate. If the stream is over, then your game won\'t be played on-stream, obviously, but we\'ll still keep it on the site!</p><h3>Copyright / licenses / future development / intellectual property / ...?</h3><p>You retain full ownership / copyright / yadda yadda. If you get a BAFTA make sure to mention us in your acceptance speech though :D</p>','RULES','Jam rules, displayed on the rules page','TEXTAREA','[]',1,1,1),
(null,Now(),'-1','NOTIFICATION_URL','','NOTIFICATION','Notification Link URL','TEXT','[]',1,0,1),
(null,Now(),'-1','NOTIFICATION_IMAGE','','NOTIFICATION','Notification Image URL','TEXT','[]',1,0,1),
(null,Now(),'-1','NOTIFICATION','','NOTIFICATION','Notification text','TEXT','[]',1,0,1),
(null,Now(),'-1','JAM_TIME','20','JAM_SETTINGS','The hour the jam starts on','ENUM','[{\"VALUE\":24,\"TEXT\":\"Midnight\"},{\"VALUE\":23,\"TEXT\":\"23:00\"},{\"VALUE\":22,\"TEXT\":\"22:00\"},{\"VALUE\":21,\"TEXT\":\"21:00\"},{\"VALUE\":20,\"TEXT\":\"20:00\"},{\"VALUE\":19,\"TEXT\":\"19:00\"},{\"VALUE\":18,\"TEXT\":\"18:00\"},{\"VALUE\":17,\"TEXT\":\"17:00\"},{\"VALUE\":16,\"TEXT\":\"16:00\"},{\"VALUE\":15,\"TEXT\":\"15:00\"},{\"VALUE\":14,\"TEXT\":\"14:00\"},{\"VALUE\":13,\"TEXT\":\"13:00\"},{\"VALUE\":12,\"TEXT\":\"12:00\"},{\"VALUE\":11,\"TEXT\":\"11:00\"},{\"VALUE\":10,\"TEXT\":\"10:00\"},{\"VALUE\":9,\"TEXT\":\"9:00\"},{\"VALUE\":8,\"TEXT\":\"8:00\"},{\"VALUE\":7,\"TEXT\":\"7:00\"},{\"VALUE\":6,\"TEXT\":\"6:00\"},{\"VALUE\":5,\"TEXT\":\"5:00\"},{\"VALUE\":4,\"TEXT\":\"4:00\"},{\"VALUE\":3,\"TEXT\":\"3:00\"},{\"VALUE\":2,\"TEXT\":\"2:00\"},{\"VALUE\":1,\"TEXT\":\"1:00\"}]',1,1,1),
(null,Now(),'-1','JAM_DAY','6','JAM_SETTINGS','Jam start day of the week','ENUM','[{\"VALUE\":0,\"TEXT\":\"Sunday\"},{\"VALUE\":1,\"TEXT\":\"Monday\"},{\"VALUE\":2,\"TEXT\":\"Tuesday\"},{\"VALUE\":3,\"TEXT\":\"Wednesday\"},{\"VALUE\":4,\"TEXT\":\"Thursday\"},{\"VALUE\":5,\"TEXT\":\"Friday\"},{\"VALUE\":6,\"TEXT\":\"Saturday\"}]',1,1,1),
(null,Now(),'-1','JAMNAME','One Hour Game Jam','JAM_SETTINGS','Jam name, displayed in the page header','TEXT','[]',1,1,1),
(null,Now(),'-1','JAMDESC','Every Saturday at 20:00 UTC','JAM_SETTINGS','Jam description, displayed in the page header','TEXT','[]',1,1,1),
(null,Now(),'-1','DEFAULT_BRIGHTNESS','180','NEW_JAM_DEFAULTS','Default brightness (0..255)','NUMBER','[]',1,1,1),
(null,Now(),'-1','DEFAULT_HUE_MIN','0','NEW_JAM_DEFAULTS','Default minimum hue (0..255)','NUMBER','[]',1,1,1),
(null,Now(),'-1','DEFAULT_HUE_MAX','200','NEW_JAM_DEFAULTS','Default maximum hue (0..255)','NUMBER','[]',1,1,1),
(null,Now(),'-1','ADMIN_ACTIVITY_JAMS_SINCE_LAST_PARTICIPATION_WARNING','20','ADMIN_SUGGESTIONS','Inactive administrator warning: Number of jams since last participation to mark inactive','NUMBER','[]',0,1,1),
(null,Now(),'-1','ADMIN_SUGGESTION_TOTAL_PARTICIPATION','10','ADMIN_SUGGESTIONS','New administrator suggestion: Minimum total participation to suggest','NUMBER','[]',1,1,1),
(null,Now(),'-1','ADMIN_SUGGESTION_RECENT_PARTICIPATION','50','ADMIN_SUGGESTIONS','New administrator suggestion: Minimum recent participation percentage to suggest (0 - 100)','NUMBER','[]',1,1,1),
(null,Now(),'-1','ADMIN_ACTIVITY_DAYS_SINCE_LAST_ADMIN_ACTION_WARNING','30','ADMIN_SUGGESTIONS','Inactive administrator warning: Days since last admin action to mark inactive','NUMBER','[]',0,1,1),
(null,Now(),'-1','ADMIN_ACTIVITY_DAYS_SINCE_LAST_LOGIN_GOOD','7','ADMIN_SUGGESTIONS','Inactive administrator warning: Days since last participation to mark highly active','NUMBER','[]',0,1,1),
(null,Now(),'-1','ADMIN_ACTIVITY_JAMS_SINCE_LAST_PARTICIPATION_GOOD','5','ADMIN_SUGGESTIONS','Inactive administrator warning: Number of jams since last participation to mark high activity','NUMBER','[]',0,1,1),
(null,Now(),'-1','ADMIN_ACTIVITY_DAYS_SINCE_LAST_LOGIN_WARNING','21','ADMIN_SUGGESTIONS','Inactive administrator warning: Days since last participation to mark inactive','NUMBER','[]',0,1,1),
(null,Now(),'-1','ADMIN_ACTIVITY_DAYS_SINCE_LAST_ADMIN_ACTION_GOOD','14','ADMIN_SUGGESTIONS','Inactive administrator warning: Days since last admin action to mark highly active','NUMBER','[]',0,1,1),
(null,Now(),'-1','REDIRECT_TO_HTTPS','0','SECURITY','Automatically redirect all users to the HTTPS variant of the site?','ENUM','[{\"VALUE\":0,\"TEXT\":\"No\"},{\"VALUE\":1,\"TEXT\":\"Yes\"}]',0,1,0),
(null,Now(),'-1','JAM_DURATION', '60', 'JAM_SETTINGS', 'How many minutes do participants have to make their game?', 'NUMBER', '[]', '1', '1', '1'),
(null,Now(),'-1','MINIMUM_PASSWORD_LENGTH', '8', 'SECURITY', 'Shortest password length', 'NUMBER', '[]', '0', '1', '1'),
(null,Now(),'-1','MAXIMUM_PASSWORD_LENGTH', '128', 'SECURITY', 'Longest password length', 'NUMBER', '[]', '0', '1', '1'),
(null,Now(),'-1','MINIMUM_PASSWORD_HASH_ITERATIONS', '10000', 'SECURITY', 'Minimum hash iterations for a user\'s password', 'NUMBER', '[]', '0', '1', '0'),
(null,Now(),'-1','MAXIMUM_PASSWORD_HASH_ITERATIONS', '20000', 'SECURITY', 'Maximum hash iterations for a user\'s password', 'NUMBER', '[]', '0', '1', '0'),
(null,Now(),'-1','MINIMUM_USERNAME_LENGTH', '2', 'USERS', 'Shortest username length', 'NUMBER', '[]', '0', '1', '1'),
(null,Now(),'-1','MAXIMUM_USERNAME_LENGTH', '20', 'USERS', 'Longest username length', 'NUMBER', '[]', '0', '1', '1'),
(null,Now(),'-1','DAYS_TO_KEEP_LOGGED_IN', '30', 'SECURITY', 'Number of dayts after the last visit that the user will be kept logged in', 'NUMBER', '[]', '1', '1', '1'),
(null,Now(),'-1','MAX_COLORS_FOR_JAM', '16', 'NEW_JAM_DEFAULTS', 'Maximum number of colors that will be available for each jam', 'NUMBER', '[]', '1', '1', '1'),
(null,Now(),'-1','MINIMUM_DISPLAY_NAME_LENGTH', '1', 'USERS', 'Shortest user display name', 'NUMBER', '[]', '1', '1', '1'),
(null,Now(),'-1','MAXIMUM_DISPLAY_NAME_LENGTH', '50', 'USERS', 'Longest user display name', 'NUMBER', '[]', '1', '1', '1'),
(null,Now(),'-1','MAX_SCREENSHOT_FILE_SIZE_IN_BYTES', '5000000', 'JAM_SETTINGS', 'Maximum screenshot file size in bytes', 'NUMBER', '[]', '0', '1', '1'),
(null,Now(),'-1','JAM_AUTO_SCHEDULER_MINUTES_BEFORE_JAM', '120', 'JAM_SETTINGS', 'How many minutes before the next jam should the jam autoscheduler schedule a jam?', 'NUMBER', '[]', '1', '1', '1'),
(null,Now(),'-1','JAM_AUTO_SCHEDULER_ENABLED', '0', 'JAM_SETTINGS', 'Should the jam autoscheduler automatically schedule jams?', 'ENUM', '[{\"VALUE\":0,\"TEXT\":\"No\"},{\"VALUE\":1,\"TEXT\":\"Yes\"}]', '1', '1', '1'),
(null,Now(),'-1','DATABASE_VERSION', '1', 'SYSTEM', 'The version of the database. Used to determine required database migration.', 'NUMBER', '[]', '0', '1', '1');


/*!40000 ALTER TABLE `config` ENABLE KEYS */;
UNLOCK TABLES;



-- Dump completed on 2018-12-30  2:47:46