ALTER TABLE jam 
ADD COLUMN jam_state VARCHAR(45) NULL AFTER jam_start_datetime;

UPDATE jam
SET jam_state = "COMPLETE";