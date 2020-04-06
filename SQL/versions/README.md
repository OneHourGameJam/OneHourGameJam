# Overview
This folder contains the schema of the 1hgj SQL database.

Each file is represents a step in reconstructing the database from any point in its history. Each file is proceeded by a version number. When the file is run by 1hgj's internal migration utility it will update the config variable "DATABASE_VERSION" with the newest version.

To change the database in a deploy you should add a new version, and update the $dbVersion in db.php. This will make 1hgj automatically update to the latest version on next page load.