ALTER TABLE user
ADD COLUMN user_auth_version INT NOT NULL AFTER user_password_hash;

UPDATE user
SET user_auth_version = 1;