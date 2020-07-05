ALTER TABLE themevote
ADD COLUMN themevote_user_id INT NULL AFTER themevote_username;

UPDATE themevote
SET themevote_user_id = (SELECT user_id FROM user WHERE user_username = themevote_username);

ALTER TABLE themevote
DROP COLUMN themevote_username;