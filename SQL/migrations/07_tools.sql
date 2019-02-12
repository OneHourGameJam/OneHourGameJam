CREATE TABLE tool (
  tool_id INT NOT NULL AUTO_INCREMENT,
  tool_datetime DATETIME NULL,
  tool_user_agent VARCHAR(255) NULL,
  tool_ip VARCHAR(45) NULL,
  tool_username VARCHAR(255) NULL,
  tool_title TEXT NULL,
  tool_url VARCHAR(255) NULL,
  tool_category VARCHAR(255) NULL,
  tool_subcategory VARCHAR(255) NULL,
  PRIMARY KEY (tool_id));
