
CREATE TABLE platform (
  platform_id int(11) NOT NULL AUTO_INCREMENT,
  platform_name varchar(255) NOT NULL,
  platform_icon_url VARCHAR(255) NOT NULL,
  platform_deleted int(1) NOT NULL,
  PRIMARY KEY (platform_id)
);

INSERT INTO platform (platform_id, platform_name, platform_icon_url, platform_deleted) VALUES ('1', 'Windows', 'images/platforms/windows.png', 0);
INSERT INTO platform (platform_id, platform_name, platform_icon_url, platform_deleted) VALUES ('2', 'macOS', 'images/platforms/mac.png', 0);
INSERT INTO platform (platform_id, platform_name, platform_icon_url, platform_deleted) VALUES ('3', 'Linux', 'images/platforms/linux.png', 0);
INSERT INTO platform (platform_id, platform_name, platform_icon_url, platform_deleted) VALUES ('4', 'Android', 'images/platforms/android.png', 0);
INSERT INTO platform (platform_id, platform_name, platform_icon_url, platform_deleted) VALUES ('5', 'iOS', 'images/platforms/ios.png', 0);
INSERT INTO platform (platform_id, platform_name, platform_icon_url, platform_deleted) VALUES ('6', 'Web', 'images/platforms/web.png', 0);
INSERT INTO platform (platform_id, platform_name, platform_icon_url, platform_deleted) VALUES ('7', 'Other Platform', 'images/platforms/custom.png', 0);
INSERT INTO platform (platform_id, platform_name, platform_icon_url, platform_deleted) VALUES ('8', 'Source Code', 'images/platforms/source.png', 0);

CREATE TABLE platform_entry (
  platformentry_id INT NOT NULL AUTO_INCREMENT,
  platformentry_entry_id INT NULL,
  platformentry_platform_id INT NULL,
  platformentry_url varchar(255) NOT NULL,
  PRIMARY KEY (platformentry_id)
);

SET @row_number = 0;

INSERT INTO platform_entry
SELECT 
	(@row_number := @row_number + 1) AS platformentry_id, 
	entry_id AS platformentry_entry_id, 
    1 AS platformentry_platform_id, 
	entry_url_windows  AS platformentry_url
FROM entry 
WHERE entry_url_windows != "";

INSERT INTO platform_entry
SELECT 
	(@row_number := @row_number + 1) AS platformentry_id, 
	entry_id AS platformentry_entry_id, 
    2 AS platformentry_platform_id, 
	entry_url_mac  AS platformentry_url
FROM entry 
WHERE entry_url_mac != "";

INSERT INTO platform_entry
SELECT 
	(@row_number := @row_number + 1) AS platformentry_id, 
	entry_id AS platformentry_entry_id, 
    3 AS platformentry_platform_id, 
	entry_url_linux  AS platformentry_url
FROM entry 
WHERE entry_url_linux != "";

INSERT INTO platform_entry
SELECT 
	(@row_number := @row_number + 1) AS platformentry_id, 
	entry_id AS platformentry_entry_id, 
    4 AS platformentry_platform_id, 
	entry_url_android  AS platformentry_url
FROM entry 
WHERE entry_url_android != "";

INSERT INTO platform_entry
SELECT 
	(@row_number := @row_number + 1) AS platformentry_id, 
	entry_id AS platformentry_entry_id, 
    5 AS platformentry_platform_id, 
	entry_url_ios  AS platformentry_url
FROM entry 
WHERE entry_url_ios != "";

INSERT INTO platform_entry
SELECT 
	(@row_number := @row_number + 1) AS platformentry_id, 
	entry_id AS platformentry_entry_id, 
    6 AS platformentry_platform_id, 
	entry_url_web  AS platformentry_url
FROM entry 
WHERE entry_url_web != "";

INSERT INTO platform_entry
SELECT 
	(@row_number := @row_number + 1) AS platformentry_id, 
	entry_id AS platformentry_entry_id, 
    7 AS platformentry_platform_id, 
	entry_url  AS platformentry_url
FROM entry 
WHERE entry_url != "";

INSERT INTO platform_entry
SELECT 
	(@row_number := @row_number + 1) AS platformentry_id, 
	entry_id AS platformentry_entry_id, 
    8 AS platformentry_platform_id, 
	entry_url_source  AS platformentry_url
FROM entry 
WHERE entry_url_source != "";

ALTER TABLE entry
DROP COLUMN entry_url_source,
DROP COLUMN entry_url_ios,
DROP COLUMN entry_url_android,
DROP COLUMN entry_url_mac,
DROP COLUMN entry_url_linux,
DROP COLUMN entry_url_windows,
DROP COLUMN entry_url_web,
DROP COLUMN entry_url;