ALTER TABLE entry
ADD COLUMN entry_author_user_id INT NULL AFTER entry_author;

UPDATE entry
SET entry_author_user_id = (SELECT u.user_id FROM user u WHERE u.user_username = entry_author);

SELECT MAX(user_id) AS max_user_id into @maxUserId FROM user;

SET @row_number = @maxUserId + 1;

INSERT INTO user
SELECT 
	(@row_number:=@row_number + 1) AS user_id,
	entry_author AS user_username, 
    Now() AS user_datetime, 
    "MIGRATION" AS user_register_ip, 
    "MIGRATION" AS user_register_user_agent, 
    entry_author AS user_display_name, 
    "RESET_SALT" AS user_password_salt, 
    "RESET" AS user_password_hash,
    12345 AS user_password_iterations,
    "0000-00-00 00:00:00" AS user_last_login_datetime,
    "MIGRATION" AS user_last_ip,
    "MIGRATION" AS user_last_user_agent,
    "" AS user_email,
    "" AS user_twitter,
    "" AS user_bio,
    0 AS user_role,
    0 AS user_preferences
FROM entry
WHERE entry_author_user_id is null
GROUP BY user_username;

UPDATE entry
SET entry_author_user_id = (SELECT u.user_id FROM user u WHERE u.user_username = entry_author)
WHERE entry_author_user_id is null;

ALTER TABLE entry
DROP COLUMN entry_author;