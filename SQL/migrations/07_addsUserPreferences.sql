ALTER TABLE user 
ADD COLUMN user_preferences INT(11) NULL DEFAULT 0 AFTER user_role;

UPDATE config SET config_value = '7' WHERE (config_key = 'DATABASE_VERSION');
