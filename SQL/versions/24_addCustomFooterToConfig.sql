INSERT INTO config (config_lastedited, config_lasteditedby, config_key, config_value, config_category, config_description, config_type, config_options, config_editable, config_required, config_added_to_dictionary) 
VALUES 
(Now(), -1, 'SITE_FOOTER', CONCAT('Content posted to this website might be subject to Copyright, consult with content authors before use. <br />Established ',YEAR(CURDATE())), 'JAM_SETTINGS', "Site's footer", 'TEXTAREA', '[]', '1', '1', '1');
