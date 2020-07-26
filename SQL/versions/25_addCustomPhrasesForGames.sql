INSERT INTO config (config_lastedited, config_lasteditedby, config_key, config_value, config_category, config_description, config_type, config_options, config_editable, config_required, config_added_to_dictionary) 
VALUES 
(Now(), -1, 'GAME_PHRASE_SINGULAR', 'Game', 'JAM_SETTINGS', "Phrase (noun) for each jam\'s entry - singular (e.g. Entry/Game/Story/etc)", 'TEXT', '[]', '1', '1', '1'),
(Now(), -1, 'GAME_PHRASE_PLURAL', 'Games', 'JAM_SETTINGS', "Phrase (noun) for each jam\'s entry - plural (e.g. Entries/Games/Stories/etc)", 'TEXT', '[]', '1', '1', '1');
