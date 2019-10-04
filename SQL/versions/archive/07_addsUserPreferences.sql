ALTER TABLE user 
ADD COLUMN user_preferences INT(11) NULL DEFAULT 0 AFTER user_role;
