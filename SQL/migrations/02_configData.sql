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
	config_template
) VALUES (
	null,
	Now(),
	'-1',
	'JAMNAME',
	'One Hour Game Jam',
	'JAM_SETTINGS',
	'Jam name, displayed in the page header',
	'TEXT',
	'[]',
	'1',
	'1',
	'1'
);

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
	config_template
) VALUES (
	null,
	Now(),
	'-1',
	'JAMDESC',
	'Every Saturday at 20:00 UTC',
	'JAM_SETTINGS',
	'Jam description, displayed in the page header',
	'TEXT',
	'[]',
	'1',
	'1',
	'1'
);

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
	config_template
) VALUES (
	null,
	Now(),
	'-1',
	'JAM_DAY',
	'6',
	'JAM_SETTINGS',
	'Jam start day of the week',
	'ENUM',
	'[{\"VALUE\":0,\"TEXT\":\"Sunday\"},{\"VALUE\":1,\"TEXT\":\"Monday\"},{\"VALUE\":2,\"TEXT\":\"Tuesday\"},{\"VALUE\":3,\"TEXT\":\"Wednesday\"},{\"VALUE\":4,\"TEXT\":\"Thursday\"},{\"VALUE\":5,\"TEXT\":\"Friday\"},{\"VALUE\":6,\"TEXT\":\"Saturday\"}]',
	'1',
	'1',
	'1'
);

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
	config_template
) VALUES (
	null,
	Now(),
	'-1',
	'JAM_TIME',
	'20',
	'JAM_SETTINGS',
	'The hour the jam starts on',
	'ENUM',
	'[{\"VALUE\":24,\"TEXT\":\"Midnight\"},{\"VALUE\":23,\"TEXT\":\"23:00\"},{\"VALUE\":22,\"TEXT\":\"22:00\"},{\"VALUE\":21,\"TEXT\":\"21:00\"},{\"VALUE\":20,\"TEXT\":\"20:00\"},{\"VALUE\":19,\"TEXT\":\"19:00\"},{\"VALUE\":18,\"TEXT\":\"18:00\"},{\"VALUE\":17,\"TEXT\":\"17:00\"},{\"VALUE\":16,\"TEXT\":\"16:00\"},{\"VALUE\":15,\"TEXT\":\"15:00\"},{\"VALUE\":14,\"TEXT\":\"14:00\"},{\"VALUE\":13,\"TEXT\":\"13:00\"},{\"VALUE\":12,\"TEXT\":\"12:00\"},{\"VALUE\":11,\"TEXT\":\"11:00\"},{\"VALUE\":10,\"TEXT\":\"10:00\"},{\"VALUE\":9,\"TEXT\":\"9:00\"},{\"VALUE\":8,\"TEXT\":\"8:00\"},{\"VALUE\":7,\"TEXT\":\"7:00\"},{\"VALUE\":6,\"TEXT\":\"6:00\"},{\"VALUE\":5,\"TEXT\":\"5:00\"},{\"VALUE\":4,\"TEXT\":\"4:00\"},{\"VALUE\":3,\"TEXT\":\"3:00\"},{\"VALUE\":2,\"TEXT\":\"2:00\"},{\"VALUE\":1,\"TEXT\":\"1:00\"}]',
	'1',
	'1',
	'1'
);

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
	config_template
) VALUES (
	null,
	Now(),
	'-1',
	'NOTIFICATION',
	'',
	'NOTIFICATION',
	'Notification text',
	'TEXT',
	'[]',
	'1',
	'0',
	'1'
);

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
	config_template
) VALUES (
	null,
	Now(),
	'-1',
	'NOTIFICATION_IMAGE',
	'',
	'NOTIFICATION',
	'Notification Image URL',
	'TEXT',
	'[]',
	'1',
	'0',
	'1'
);

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
	config_template
) VALUES (
	null,
	Now(),
	'-1',
	'NOTIFICATION_URL',
	'',
	'NOTIFICATION',
	'Notification Link URL',
	'TEXT',
	'[]',
	'1',
	'0',
	'1'
);

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
	config_template
) VALUES (
	null,
	Now(),
	'-1',
	'RULES',
	'<h3>When?</h3><p>Every <a href=\'https://www.google.com/search?q=20%3A00+UTC\' target=\'_BLANK\'>Saturday at 20:00 UTC</a>. The time in your local time and a countdown-timer should be in the top of the site though.</p><h3>Where?</h3><p>Right here! <a href=\'https://discord.gg/J86uTu9\' target=\'_BLANK\'>Joining our Discord server</a> is however a good idea.</p><h3>Is there a theme?</h3><p>Yes! At the start of the hour, the theme is announced on the site and <a href=\'https://discord.gg/J86uTu9\' target=\'_BLANK\'>on Discord</a>. Themes are suggested and voted by the community on the <a href=\'?page=themes\'>Theme Voting page</a>.</p><h3>How long do I have to finish?</h3><p>:D</p><h3>Do I have to submit within the hour?</h3><p>No. Keep working on your game until it\'s done, then submit.</p><h3>Can I use premade assets?</h3><p>You are free to use premade assets but it is recommended that you make at least one game from scratch. Some assets are provided on the <a href=\'?page=assets\'>Assets page</a> but you do not have to use them.</p><h3>Do I have to host the games myself?</h3><p>Yes, you\'ll need to host the game and submit the links here. We host the thumbnail though. If you don\'t have a website yourself, use something like dropbox / google drive, or itch.io / newgrounds. Ask on Discord for more suggestions.</p><h3>What happens after the jam?</h3><p>About 30 minutes after the jam ends, we host a stream on Twitch where all submitted games are played. The stream usually lasts about 2 hours. It\'s not always the same people streaming though, so keep an eye on Discord to see who\'s streaming.</p><h3>What if I don\'t finish in time?</h3><p>If you finish and submit your game before the stream ends, we\'ll play it on stream, even if it took you more than an hour to finish.</p><h3>Can I participate after the jam has ended?</h3><p>If you come late you can still participate. If the stream is over, then your game won\'t be played on-stream, obviously, but we\'ll still keep it on the site!</p><h3>Copyright / licenses / future development / intellectual property / ...?</h3><p>You retain full ownership / copyright / yadda yadda. If you get a BAFTA make sure to mention us in your acceptance speech though :D</p>',
	'RULES',
	'Jam rules, displayed on the rules page',
	'TEXTAREA',
	'[]',
	'1',
	'1',
	'1'
);

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
	config_template
) VALUES (
	null,
	Now(),
	'-1',
	'THEME_DAYS_MARK_AS_OLD',
	'90',
	'THEME_SELECTION',
	'How many days a theme can be on the list before it is marked as old.',
	'NUMBER',
	'[]',
	'1',
	'1',
	'0'
);

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
	config_template
) VALUES (
	null,
	Now(),
	'-1',
	'THEME_MIN_VOTES_TO_SCORE',
	'10',
	'THEME_SELECTION',
	'Minimum number of votes a theme must receive for it to be considered rated.',
	'NUMBER',
	'[]',
	'1',
	'1',
	'0'
);

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
	config_template
) VALUES (
	null,
	Now(),
	'-1',
	'THEME_NUMBER_TO_MARK_TOP',
	'5',
	'THEME_SELECTION',
	'Number of best voted themes to mark as \"top\".',
	'NUMBER',
	'[]',
	'1',
	'1',
	'0'
);

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
	config_template
) VALUES (
	null,
	Now(),
	'-1',
	'THEME_NUMBER_TO_MARK_KEEP',
	'20',
	'THEME_SELECTION',
	'Number of best voted themes to keep for the next jam.',
	'NUMBER',
	'[]',
	'1',
	'1',
	'0'
);

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
	config_template
) VALUES (
	null,
	Now(),
	'-1',
	'JAMS_CONSIDERED_RECENT',
	'10',
	'THEME_SELECTION',
	'Number of jams which are considered \'recent\' when calculating recent jam participation.',
	'NUMBER',
	'[]',
	'1',
	'1',
	'0'
);

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
	config_template
) VALUES (
	null,
	Now(),
	'-1',
	'SATISFACTION_RATINGS_TO_SHOW_SCORE',
	'5',
	'SATISFACTION',
	'Total number of satisfaction ratings needed for them to become publicly visible.',
	'NUMBER',
	'[]',
	'1',
	'1',
	'0'
);

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
	config_template
) VALUES (
	null,
	Now(),
	'-1',
	'PEPPER',
	'',
	'SECURITY',
	'Sitewide Pepper (used in password hashing), for security reasons this can only be changed manually in the config file.',
	'TEXT',
	'[]',
	'0',
	'1',
	'0'
);

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
	config_template
) VALUES (
	null,
	Now(),
	'-1',
	'SESSION_PASSWORD_ITERATIONS',
	'',
	'SECURITY',
	'Number of hashing iterations for session IDs, for security reasons this can only be changed manually in the config file.',
	'NUMBER',
	'[]',
	'0',
	'1',
	'0'
);

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
	config_template
) VALUES (
	null,
	Now(),
	'-1',
	'STREAMER_TWITCH_NAME',
	'',
	'STREAM',
	'Twitch name for the streamer for this jam',
	'TEXT',
	'[]',
	'1',
	'1',
	'0'
);

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
	config_template
) VALUES (
	null,
	Now(),
	'-1',
	'TWITCH_CLIENT_ID',
	'',
	'STREAM',
	'Twitch client id for the API',
	'TEXT',
	'[]',
	'0',
	'1',
	'0'
);

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
	config_template
) VALUES (
	null,
	Now(),
	'-1',
	'TWITCH_API_STREAM_UPDATE_FREQUENCY',
	'30',
	'STREAM',
	'The minimum number of seconds that have to pass between subsequent checks as to whether the stream is online on Twitch or not.',
	'NUMBER',
	'[]',
	'1',
	'1',
	'0'
);

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
	config_template
) VALUES (
	null,
	Now(),
	'-1',
	'TWITTER_ACCOUNT',
	'',
	'SOCIAL_MEDIA',
	'Game Jam\'s twitter account, appears in the left menu.',
	'TEXT',
	'[]',
	'1',
	'0',
	'1'
);

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
	config_template
) VALUES (
	null,
	Now(),
	'-1',
	'TWITCH_ACCOUNT',
	'',
	'SOCIAL_MEDIA',
	'Game Jam\'s twitch account, appears in the left menu.',
	'TEXT',
	'[]',
	'1',
	'0',
	'1'
);

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
	config_template
) VALUES (
	null,
	Now(),
	'-1',
	'IRC_ADDRESS',
	'',
	'SOCIAL_MEDIA',
	'IRC address',
	'TEXT',
	'[]',
	'1',
	'0',
	'1'
);

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
	config_template
) VALUES (
	null,
	Now(),
	'-1',
	'IRC_CHANNEL',
	'',
	'SOCIAL_MEDIA',
	'IRC Channel',
	'TEXT',
	'[]',
	'1',
	'0',
	'1'
);

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
	config_template
) VALUES (
	null,
	Now(),
	'-1',
	'IRC_CHAT_IN_BROWSER',
	'',
	'SOCIAL_MEDIA',
	'IRC Chat in browser URL',
	'TEXT',
	'[]',
	'1',
	'0',
	'1'
);

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
	config_template
) VALUES (
	null,
	Now(),
	'-1',
	'DISCORD_INVITE_URL',
	'',
	'SOCIAL_MEDIA',
	'Discord invite URL',
	'TEXT',
	'[]',
	'1',
	'0',
	'1'
);

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
	config_template
) VALUES (
	null,
	Now(),
	'-1',
	'GOOGLE_ANALYTICS_CODE',
	'',
	'ANALYTICS',
	'Google Analytics code for site. Will look something like this: UA-00000000-1. If blank, Google analytics code will not even be inserted.',
	'TEXT',
	'[]',
	'0',
	'0',
	'0'
);

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
	config_template
) VALUES (
	null,
	Now(),
	'-1',
	'DEFAULT_NUMBER_OF_COLORS',
	'10',
	'NEW_JAM_DEFAULTS',
	'Default number of colors (0..16)',
	'NUMBER',
	'[]',
	'1',
	'1',
	'1'
);

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
	config_template
) VALUES (
	null,
	Now(),
	'-1',
	'DEFAULT_SATURATION',
	'160',
	'NEW_JAM_DEFAULTS',
	'Default saturation (0..255)',
	'NUMBER',
	'[]',
	'1',
	'1',
	'1'
);

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
	config_template
) VALUES (
	null,
	Now(),
	'-1',
	'DEFAULT_BRIGHTNESS',
	'180',
	'NEW_JAM_DEFAULTS',
	'Default brightness (0..255)',
	'NUMBER',
	'[]',
	'1',
	'1',
	'1'
);

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
	config_template
) VALUES (
	null,
	Now(),
	'-1',
	'DEFAULT_HUE_MIN',
	'0',
	'NEW_JAM_DEFAULTS',
	'Default minimum hue (0..255)',
	'NUMBER',
	'[]',
	'1',
	'1',
	'1'
);

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
	config_template
) VALUES (
	null,
	Now(),
	'-1',
	'DEFAULT_HUE_MAX',
	'200',
	'NEW_JAM_DEFAULTS',
	'Default maximum hue (0..255)',
	'NUMBER',
	'[]',
	'1',
	'1',
	'1'
);

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
	config_template
) VALUES (
	null,
	Now(),
	'-1',
	'ADMIN_WARNING_WEEKS_SINCE_LAST_JAM',
	'20',
	'ADMIN_SUGGESTIONS',
	'Inactive administrator warning: Number of jams since last participation',
	'NUMBER',
	'[]',
	'1',
	'1',
	'0'
);

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
	config_template
) VALUES (
	null,
	Now(),
	'-1',
	'ADMIN_SUGGESTION_TOTAL_PARTICIPATION',
	'15',
	'ADMIN_SUGGESTIONS',
	'New administrator suggestion: Minimum total participation to suggest',
	'NUMBER',
	'[]',
	'1',
	'1',
	'0'
);

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
	config_template
) VALUES (
	null,
	Now(),
	'-1',
	'ADMIN_SUGGESTION_RECENT_PARTICIPATION',
	'50',
	'ADMIN_SUGGESTIONS',
	'New administrator suggestion: Minimum recent participation percentage to suggest (0 - 100)',
	'NUMBER',
	'[]',
	'1',
	'1',
	'0'
);
				
