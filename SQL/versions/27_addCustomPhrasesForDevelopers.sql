INSERT INTO config (config_lastedited, config_lasteditedby, config_key, config_value, config_category, config_description, config_type, config_options, config_editable, config_required, config_added_to_dictionary) 
VALUES 
(Now(), -1, 'DEVELOPER_PHRASE_SINGULAR', 'Developer', 'JAM_SETTINGS', "Phrase (noun) for jam participants - singular (e.g. Developer/Author/Participant/etc)", 'TEXT', '[]', '1', '1', '1'),
(Now(), -1, 'DEVELOPER_PHRASE_PLURAL', 'Developers', 'JAM_SETTINGS', "Phrase (noun) for jam participants - plural (e.g. Developers/Authors/Participants/etc)", 'TEXT', '[]', '1', '1', '1');
