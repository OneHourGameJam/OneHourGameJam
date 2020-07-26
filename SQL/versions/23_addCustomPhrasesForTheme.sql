INSERT INTO config (config_lastedited, config_lasteditedby, config_key, config_value, config_category, config_description, config_type, config_options, config_editable, config_required, config_added_to_dictionary) 
VALUES 
(Now(), -1, 'THEME_PHRASE_SINGULAR', 'Theme', 'JAM_SETTINGS', "Phrase (noun) for each jam\'s prompt - singular (e.g. Theme/Prompt/Style/etc)", 'TEXT', '[]', '1', '1', '1'),
(Now(), -1, 'THEME_PHRASE_PLURAL', 'Themes', 'JAM_SETTINGS', "Phrase (noun) for each jam\'s prompt - plural (e.g. Themes/Prompts/Styles/etc)", 'TEXT', '[]', '1', '1', '1');
