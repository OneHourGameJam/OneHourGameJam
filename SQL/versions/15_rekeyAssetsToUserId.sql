ALTER TABLE asset
ADD COLUMN asset_author_user_id INT NULL AFTER asset_author;

UPDATE asset
SET asset_author_user_id = (SELECT u.user_id FROM user u WHERE u.user_username = asset_author);

ALTER TABLE asset
DROP COLUMN asset_author;