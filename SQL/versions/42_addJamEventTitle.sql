alter table jam
    add jam_event_name text null after jam_default_icon_url;

INSERT INTO config
(config_key, config_value, config_category, config_description, config_type, config_options, config_editable, config_required, config_added_to_dictionary, config_required_permission_read, config_required_permission_write)
VALUES
('CONFIG_DEFAULT_JAM_EVENT_NAME', '', 'NEW_JAM_DEFAULTS', 'Default event name (for commemorations or otherwise referencing an occasion with a jam)', 'TEXT', '[]', 1, 0, 1, 1, 1);