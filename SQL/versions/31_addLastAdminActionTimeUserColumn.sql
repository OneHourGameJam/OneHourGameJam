ALTER TABLE user
ADD COLUMN user_last_admin_action_datetime DATETIME NULL AFTER user_preferences;

UPDATE user
SET user_last_admin_action_datetime = (SELECT max(log_datetime) FROM admin_log WHERE log_admin_user_id = user_id)