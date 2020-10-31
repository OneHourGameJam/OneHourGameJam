ALTER TABLE user
ADD COLUMN user_permissions_allowlist INT NULL AFTER user_preferences,
ADD COLUMN user_permissions_denylist INT NULL AFTER user_permissions_allowlist;

INSERT INTO config
(config_key, config_value, config_category, config_description, config_type, config_options, config_editable, config_required, config_added_to_dictionary) 
VALUES 
('DEFAULT_PERMISSIONS', '1', 'USERS', 'Default User Permissions', 'NUMBER', '[]', '0', '1', '1');