ALTER TABLE entry 
CHANGE COLUMN entry_color entry_background_color VARCHAR(45) NULL DEFAULT 'FFFFFF',
ADD COLUMN entry_text_color VARCHAR(45) NULL DEFAULT '000000' AFTER entry_background_color;