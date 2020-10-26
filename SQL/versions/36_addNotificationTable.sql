CREATE TABLE notification (
  notification_id int(11) NOT NULL AUTO_INCREMENT,
  notification_ip varchar(45) DEFAULT NULL,
  notification_user_agent text,
  notification_user_id int(11) DEFAULT NULL,
  notification_title text,
  notification_text text,
  notification_icon_image_url text,
  notification_icon_link_url text,
  notification_start_datetime datetime DEFAULT NULL,
  notification_end_datetime datetime DEFAULT NULL,
  PRIMARY KEY (notification_id)
);
