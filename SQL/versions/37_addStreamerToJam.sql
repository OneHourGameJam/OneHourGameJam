ALTER TABLE jam
ADD COLUMN jam_streamer_user_id INT NULL AFTER jam_start_datetime,
ADD COLUMN jam_streamer_twitch_username VARCHAR(45) NULL AFTER jam_streamer_user_id;

DELETE FROM config
WHERE config_key = "STREAMER_TWITCH_NAME";

INSERT INTO config
(config_key, config_value, config_category, config_description, config_type, config_options, config_editable, config_required, config_added_to_dictionary) 
VALUES 
('TWITCH_CHECK_STREAM_AFTER_JAM_END_MINUTES', '3600', 'STREAM', 'How long after a jam ends should the streamer\'s Twitch account be checked and shown on the site?', 'NUMBER', '[]', '1', '1', '1');

ALTER TABLE user
ADD COLUMN user_twitch VARCHAR(255) NULL AFTER user_twitter;
