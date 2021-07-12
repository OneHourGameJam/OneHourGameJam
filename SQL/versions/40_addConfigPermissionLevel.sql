alter table config
	add config_required_permission_read int default 1 null,
	add config_required_permission_write int default 1 null;

update config
	set config_editable = 1,
	    config_required_permission_read = 2,
	    config_required_permission_write = 2
	where config_key = "PEPPER";

update config
	set config_editable = 1,
	    config_required_permission_read = 2,
	    config_required_permission_write = 2
	where config_key = "SESSION_PASSWORD_ITERATIONS";

update config
	set config_editable = 1,
	    config_required_permission_read = 2,
	    config_required_permission_write = 2
	where config_key = "TWITCH_CLIENT_ID";

update config
	set config_editable = 1,
	    config_required_permission_read = 1,
	    config_required_permission_write = 2
	where config_key = "ADMIN_ACTIVITY_DAYS_SINCE_LAST_LOGIN_GOOD";

update config
	set config_editable = 1,
	    config_required_permission_read = 1,
	    config_required_permission_write = 2
	where config_key = "ADMIN_ACTIVITY_DAYS_SINCE_LAST_LOGIN_WARNING";

update config
	set config_editable = 1,
	    config_required_permission_read = 1,
	    config_required_permission_write = 2
	where config_key = "ADMIN_ACTIVITY_JAMS_SINCE_LAST_PARTICIPATION_GOOD";

update config
	set config_editable = 1,
	    config_required_permission_read = 1,
	    config_required_permission_write = 2
	where config_key = "ADMIN_ACTIVITY_JAMS_SINCE_LAST_PARTICIPATION_WARNING";

update config
	set config_editable = 1,
	    config_required_permission_read = 1,
	    config_required_permission_write = 2
	where config_key = "ADMIN_ACTIVITY_DAYS_SINCE_LAST_ADMIN_ACTION_GOOD";

update config
	set config_editable = 1,
	    config_required_permission_read = 1,
	    config_required_permission_write = 2
	where config_key = "ADMIN_ACTIVITY_DAYS_SINCE_LAST_ADMIN_ACTION_WARNING";

update config
	set config_editable = 1,
	    config_required_permission_read = 1,
	    config_required_permission_write = 2
	where config_key = "REDIRECT_TO_HTTPS";

update config
	set config_editable = 1,
	    config_required_permission_read = 1,
	    config_required_permission_write = 2
	where config_key = "MINIMUM_PASSWORD_LENGTH";

update config
	set config_editable = 1,
	    config_required_permission_read = 1,
	    config_required_permission_write = 2
	where config_key = "MAXIMUM_PASSWORD_LENGTH";

update config
	set config_editable = 1,
	    config_required_permission_read = 1,
	    config_required_permission_write = 2
	where config_key = "MINIMUM_PASSWORD_HASH_ITERATIONS";

update config
	set config_editable = 1,
	    config_required_permission_read = 1,
	    config_required_permission_write = 2
	where config_key = "MAXIMUM_PASSWORD_HASH_ITERATIONS";

update config
	set config_editable = 1,
	    config_required_permission_read = 1,
	    config_required_permission_write = 2
	where config_key = "MINIMUM_USERNAME_LENGTH";

update config
	set config_editable = 1,
	    config_required_permission_read = 1,
	    config_required_permission_write = 2
	where config_key = "MAXIMUM_USERNAME_LENGTH";

update config
	set config_editable = 1,
	    config_required_permission_read = 1,
	    config_required_permission_write = 2
	where config_key = "MAX_ASSET_FILE_SIZE_IN_BYTES";

update config
	set config_editable = 1,
	    config_required_permission_read = 1,
	    config_required_permission_write = 2
	where config_key = "MAX_SCREENSHOT_FILE_SIZE_IN_BYTES";

update config
	set config_editable = 1,
	    config_required_permission_read = 2,
	    config_required_permission_write = 2
	where config_key = "TWITCH_CLIENT_SECRET";

update config
	set config_editable = 1,
	    config_required_permission_read = 1,
	    config_required_permission_write = 2
	where config_key = "DEFAULT_PERMISSIONS";