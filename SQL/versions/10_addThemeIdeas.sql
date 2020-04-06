CREATE TABLE `theme_ideas` (
  `idea_id` int(11) NOT NULL AUTO_INCREMENT,
  `idea_datetime` datetime DEFAULT NULL,
  `idea_ip` varchar(45) DEFAULT NULL,
  `idea_user_agent` varchar(255) DEFAULT NULL,
  `idea_theme_id` int(11) DEFAULT NULL,
  `idea_user_id` int(11) DEFAULT NULL,
  `idea_ideas` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`idea_id`)
) DEFAULT CHARSET=utf8mb4;
