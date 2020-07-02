ALTER TABLE jam
ADD COLUMN jam_user_id INT NULL AFTER jam_username;

UPDATE jam
SET jam_user_id = -1
WHERE jam_username = "AUTOMATIC";

UPDATE jam
SET jam_user_id = -2
WHERE jam_username = "LEGACY";

UPDATE jam
SET jam_user_id = (SELECT user_id FROM user WHERE user_username = jam_username)
WHERE jam_user_id IS NULL;

ALTER TABLE jam
DROP COLUMN jam_username;