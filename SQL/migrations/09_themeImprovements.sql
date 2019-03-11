UPDATE config SET config_value = '8' WHERE (config_key = 'DATABASE_VERSION');

INSERT INTO config
    (config_id, config_lastedited, config_lasteditedby, config_key, config_value, config_category, config_description, config_type, config_options, config_editable, config_required, config_added_to_dictionary)
VALUES (null,Now(), '1', 'JAM_THEMES_CONSIDERED_RECENT', '26', 'JAM_SETTINGS', 'Minimum number of jams to pass before allowing suggestion of a recently used theme.', 'NUMBER', '[]', '1', '1', '1')