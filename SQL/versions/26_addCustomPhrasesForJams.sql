INSERT INTO config (config_lastedited, config_lasteditedby, config_key, config_value, config_category, config_description, config_type, config_options, config_editable, config_required, config_added_to_dictionary) 
VALUES 
(Now(), -1, 'JAM_PHRASE_SINGULAR', 'Jam', 'JAM_SETTINGS', "Phrase (noun) for each jam - singular (e.g. Jam/Competition/Event/etc)", 'TEXT', '[]', '1', '1', '1'),
(Now(), -1, 'JAM_PHRASE_PLURAL', 'Jams', 'JAM_SETTINGS', "Phrase (noun) for each jam - plural (e.g. Jams/Competitions/Events/etc)", 'TEXT', '[]', '1', '1', '1');
