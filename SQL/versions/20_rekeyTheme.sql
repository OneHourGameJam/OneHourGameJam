ALTER TABLE theme
ADD COLUMN theme_author_user_id INT NULL AFTER theme_author;

UPDATE theme
SET theme_author_user_id = (SELECT user_id FROM user WHERE user_username = theme_author);

ALTER TABLE theme
DROP COLUMN theme_author;