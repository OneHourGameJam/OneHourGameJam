INSERT INTO config (
	config_id,
	config_lastedited,
	config_lasteditedby,
	config_key,
	config_value,
	config_category,
	config_description,
	config_type,
	config_options,
	config_editable,
	config_required,
	config_added_to_dictionary
) VALUES (
	null,
	Now(),
	'-1',
	'TWITCH_CLIENT_SECRET',
	'',
	'STREAM',
	'Twitch client secret for the API',
	'TEXT',
	'[]',
	'0',
	'1',
	'0'
);
