ALTER TABLE jam 
ADD COLUMN jam_selected_theme_id INT NULL AFTER jam_jam_number;

UPDATE jam
SET jam_selected_theme_id = 0;