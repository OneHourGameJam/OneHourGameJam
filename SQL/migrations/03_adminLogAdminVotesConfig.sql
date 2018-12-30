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
