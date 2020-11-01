ALTER TABLE notification 
ADD COLUMN notification_deleted TINYINT NULL DEFAULT 0 AFTER notification_end_datetime;
