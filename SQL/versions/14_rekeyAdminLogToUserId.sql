ALTER TABLE admin_log
ADD COLUMN log_admin_user_id INT NULL AFTER log_admin_username,
ADD COLUMN log_subject_user_id INT NULL AFTER log_subject_username,
ADD COLUMN log_admin_username_override VARCHAR(255) NULL AFTER log_admin_username;

UPDATE admin_log
SET log_admin_user_id = (SELECT u.user_id FROM user u WHERE u.user_username = log_admin_username);

UPDATE admin_log
SET log_subject_user_id = (SELECT u.user_id FROM user u WHERE u.user_username = log_subject_username);

UPDATE admin_log l1 INNER JOIN admin_log l2 ON l1.log_id = l2.log_id
SET l1.log_admin_username_override = l2.log_admin_username
WHERE l1.log_admin_user_id IS NULL;

ALTER TABLE admin_log
DROP COLUMN log_subject_username,
DROP COLUMN log_admin_username;