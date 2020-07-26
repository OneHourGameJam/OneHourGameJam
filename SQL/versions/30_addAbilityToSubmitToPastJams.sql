INSERT INTO config (config_lastedited, config_lasteditedby, config_key, config_value, config_category, config_description, config_type, config_options, config_editable, config_required, config_added_to_dictionary) 
VALUES 
(Now(), -1, 'CAN_SUBMIT_TO_PAST_JAMS', '0', 'JAM_SETTINGS', "Can users submit entries to past jams?", 'ENUM', '[{"VALUE":0,"TEXT":"No"},{"VALUE":1,"TEXT":"Yes"}]', '1', '1', '1');
