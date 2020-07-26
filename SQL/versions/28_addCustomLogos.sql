INSERT INTO config (config_lastedited, config_lasteditedby, config_key, config_value, config_category, config_description, config_type, config_options, config_editable, config_required, config_added_to_dictionary) 
VALUES 
(Now(), -1, 'OVERRIDE_BRAND_LOGO', '', 'JAM_SETTINGS', "Overrides the brand logo to this URL. Leave blank for default logo.", 'TEXT', '[]', '1', '0', '1'),
(Now(), -1, 'OVERRIDE_MAIN_LOGO', '', 'JAM_SETTINGS', "Overrides the main logo to this URL. Leave blank for default logo.", 'TEXT', '[]', '1', '0', '1');
