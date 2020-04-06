-- Add admin_log and admin_vote tables

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
) ENGINE=InnoDB AUTO_INCREMENT=84 DEFAULT CHARSET=latin1;
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
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

-- configuration renames column "config_template" to "config_added_to_dictionary"
ALTER TABLE config 
CHANGE COLUMN config_template config_added_to_dictionary TINYINT(1) NOT NULL;

-- configuration entry ADMIN_WARNING_WEEKS_SINCE_LAST_JAM renamed to ADMIN_ACTIVITY_JAMS_SINCE_LAST_PARTICIPATION_WARNING
-- new configuration entries: REDIRECT_TO_HTTPS, ADMIN_ACTIVITY_DAYS_SINCE_LAST_ADMIN_ACTION_WARNING, ADMIN_ACTIVITY_DAYS_SINCE_LAST_LOGIN_WARNING, ADMIN_ACTIVITY_DAYS_SINCE_LAST_LOGIN_GOOD, ADMIN_ACTIVITY_JAMS_SINCE_LAST_PARTICIPATION_GOOD, ADMIN_ACTIVITY_DAYS_SINCE_LAST_ADMIN_ACTION_GOOD
-- The following entries are no longer editable in configuration: ADMIN_ACTIVITY_JAMS_SINCE_LAST_PARTICIPATION_WARNING
DELETE FROM config WHERE config_key = 'ADMIN_WARNING_WEEKS_SINCE_LAST_JAM'

INSERT INTO config 
VALUES 
(null,'2018-10-24 02:29:45','-1','ADMIN_ACTIVITY_JAMS_SINCE_LAST_PARTICIPATION_WARNING','20','ADMIN_SUGGESTIONS','Inactive administrator warning: Number of jams since last participation to mark inactive','NUMBER','[]',0,1,1),
(null,'0000-00-00 00:00:00','-1','ADMIN_ACTIVITY_JAMS_SINCE_LAST_PARTICIPATION_GOOD','5','ADMIN_SUGGESTIONS','Inactive administrator warning: Number of jams since last participation to mark high activity','NUMBER','[]',0,1,1),
(null,'0000-00-00 00:00:00','-1','ADMIN_ACTIVITY_DAYS_SINCE_LAST_ADMIN_ACTION_WARNING','30','ADMIN_SUGGESTIONS','Inactive administrator warning: Days since last admin action to mark inactive','NUMBER','[]',0,1,1),
(null,'0000-00-00 00:00:00','-1','ADMIN_ACTIVITY_DAYS_SINCE_LAST_ADMIN_ACTION_GOOD','14','ADMIN_SUGGESTIONS','Inactive administrator warning: Days since last admin action to mark highly active','NUMBER','[]',0,1,1),
(null,'0000-00-00 00:00:00','-1','ADMIN_ACTIVITY_DAYS_SINCE_LAST_LOGIN_WARNING','21','ADMIN_SUGGESTIONS','Inactive administrator warning: Days since last participation to mark inactive','NUMBER','[]',0,1,1),
(null,'0000-00-00 00:00:00','-1','ADMIN_ACTIVITY_DAYS_SINCE_LAST_LOGIN_GOOD','7','ADMIN_SUGGESTIONS','Inactive administrator warning: Days since last participation to mark highly active','NUMBER','[]',0,1,1),
