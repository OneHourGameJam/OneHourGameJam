ALTER TABLE jam
ADD COLUMN jam_default_icon_url TEXT NULL AFTER jam_colors;

UPDATE jam
SET jam_default_icon_url = "logo.png";

INSERT INTO config 
(config_lastedited, config_lasteditedby, config_key, config_value, config_category, config_description, config_type, config_options, config_editable, config_required, config_added_to_dictionary)
VALUES 
(Now(), '-1', 'DEFAULT_GAME_ICON_URL', 'logo.png', 'NEW_JAM_DEFAULTS', 'URL to the default entry icon for entries without a custom icon', 'TEXT', '[]', '1', '1', '1');