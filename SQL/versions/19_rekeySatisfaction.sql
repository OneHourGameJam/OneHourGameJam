ALTER TABLE satisfaction
ADD COLUMN satisfaction_user_id INT NULL AFTER satisfaction_username;

UPDATE satisfaction
SET satisfaction_user_id = (SELECT user_id FROM user WHERE user_username = satisfaction_username);

ALTER TABLE satisfaction
DROP COLUMN satisfaction_username;