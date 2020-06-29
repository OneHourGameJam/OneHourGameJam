<?php

include_once("config/dbconfig.php");

// This should match the latest migration ID that should be applied
$dbVersion = 13;

//Database connection
$dbConn = mysqli_connect($dbAddress, $dbUsername, $dbPassword, $dbDatabaseName);
if(!$dbConn ){
	die("Database connection error");
}
mysqli_select_db($dbConn, $dbDatabaseName) or die("Database selection failure");
mysqli_query($dbConn, 'SET character_set_results="utf8mb4"') or die("Database result type setting failure");
mysqli_set_charset ($dbConn, "utf8mb4");

$dbAddress = "";
$dbUsername = "";
$dbPassword = "";
$dbDatabaseName = "";


function GetTables(){
	global $dbConn;
	AddActionLog("GetTables");
	StartTimer("GetTables");

	$tables = Array();
	$data = mysqli_query($dbConn, "SHOW TABLES");
	while($info = mysqli_fetch_array($data)){
		$tables[] = $info[0];
	}

	StopTimer("GetTables");
	return $tables;
}

function GetColumnsForTable($tabName){
	global $dbConn;
	AddActionLog("GetColumnsForTable");
	StartTimer("GetColumnsForTable");

	$tabName = mysqli_real_escape_string($dbConn, $tabName);

	$columns = Array();
	$data = mysqli_query($dbConn, "SHOW COLUMNS IN $tabName");
	while($info = mysqli_fetch_array($data)){
		$columns[] = $info[0];
	}

	StopTimer("GetColumnsForTable");
	return $columns;
}

function GetDataForTable($tabName){
	global $dbConn;
	AddActionLog("GetDataForTable");
	StartTimer("GetDataForTable");

	$tabName = mysqli_real_escape_string($dbConn, $tabName);

	$columns = GetColumnsForTable($tabName);

	$tabData = Array();

	$data = mysqli_query($dbConn, "SELECT * FROM $tabName");
	while($info = mysqli_fetch_array($data)){
		$row = Array();
		foreach($columns as $i => $col){
			$row[$col] = $info[$col];
		}
		$tabData[] = $row;
	}

	StopTimer("GetDataForTable");
	return $tabData;
}

function MigrateDatabase() {
	global $dbConn, $ip, $userAgent, $dbVersion;
	AddActionLog("MigrateDatabase");
	StartTimer("MigrateDatabase");

	$migrationsDir = "SQL/versions/";	//end dir with a /


	// Check our current database version
	$sql = "SELECT config_value FROM config WHERE config_key = 'DATABASE_VERSION';";
	$data = mysqli_query($dbConn, $sql);
	$sql = "";
	
	$config_result = mysqli_fetch_array($data);

	if ($config_result == NULL) {
		die("Unable to determine the database version. Please add the config value DATABASE_VERSION before running");
	}

	$currentDatabaseVersion = intval($config_result[0], 10);

	// Check and see if we need to migrate.
	if ($dbVersion < $currentDatabaseVersion) {
		die("Database version is newer than code version. Please update the site code to a version which corresponds to database version $dbVersion.");
	} elseif ($dbVersion == $currentDatabaseVersion) {
		StopTimer("MigrateDatabase");
		return;
	}
	
	// Check what migrations exist. $migrationFileNames is a map of Id => File pairs.
	// Each migration must have a file formatted as 08_Description.sql - this corresponds to the migration from database version 7 -> 8
	$migrationFileNames = array();
	
	$migrationDirFiles = scandir($migrationsDir);
	foreach ($migrationDirFiles as $migrationFile) {
		if($migrationFile == "." || $migrationFile == ".."){
			continue;
		}

		if(!is_file($migrationsDir.$migrationFile)){
			continue;
		}

		if(strtolower(pathinfo($migrationsDir.$migrationFile, PATHINFO_EXTENSION)) != "sql"){
			continue;
		}

		// Get the version ID from the filename. Files need to be formatted <versionID>_<randomName>.sql
		preg_match('/^(\d+)_.*\.sql$/', $migrationFile, $matches);

		// If the version ID is extracted from the file name, add it to the map of migrations
		if (isset($matches[1])) {
			$migrationId = intval($matches[1], 10);
			if(isset($migrationFileNames[$migrationId])){
				die("A duplicate migration script exists for database to version $migrationId - Aborting migration");
			}
			$migrationFileNames[$migrationId] = $migrationFile;
		}
	}

	$lastUpdateDatabaseVersion = $currentDatabaseVersion;

	// Go through all of the needed migrations and run them. Note that we can skip version IDs and this will work just fine.
	// We also only run the migrations that are needed to resolve the delta, nothing after the target versino and nothing before.
	for($newDatabaseVersion = $currentDatabaseVersion + 1; $newDatabaseVersion <= $dbVersion; $newDatabaseVersion++) {

		// Make sure that migration ID actually exists. This is so we can have version 10, 11, 14, (notice the missing 13?)
		if (!isset($migrationFileNames[$newDatabaseVersion])){
			die("Missing migration script to database version $newDatabaseVersion");
		}

		if (isset($migrationFileNames[$newDatabaseVersion])) {
			$newDatabaseVersionMigrationFile = $migrationFileNames[$newDatabaseVersion];

			// Update our adminlog so we can see what happened before the migration
			$escapedIP = mysqli_real_escape_string($dbConn, $ip);
			$escapedUserAgent = mysqli_real_escape_string($dbConn, $userAgent);
			$escapedLog = mysqli_real_escape_string($dbConn, "Running $newDatabaseVersionMigrationFile to bring DATABASE_VERSION up to $newDatabaseVersion");
			$sql = "
				INSERT INTO admin_log
				(log_id, log_datetime, log_ip, log_user_agent, log_admin_username, log_subject_username, log_type, log_content)
				VALUES
				(
					null,
					Now(),
					'$escapedIP',
					'$escapedUserAgent',
					'AUTOMATIC',
					'',
					'DB_MIGRATION',
					'$escapedLog'
				);";
		
			mysqli_query($dbConn, $sql) or die("Migration failed to log to admin log, please notify site admin");
			$sql = "";

			// Run the script
			$sql = file_get_contents($migrationsDir.$newDatabaseVersionMigrationFile);
			if(mysqli_multi_query($dbConn, $sql)) {
				do {
					mysqli_next_result($dbConn);
				}
				while(mysqli_more_results($dbConn));
			}
			$sql = "";
		
			if(mysqli_errno($dbConn)) {
				die("Migration failed to run migration script $newDatabaseVersion, please notify site admin.");
			}

			// Update our config DATABASE_VALUE
			$escapedNewDatabaseVersion = mysqli_real_escape_string($dbConn, $newDatabaseVersion);
			$sql = "
				UPDATE config
				SET config_value = '$escapedNewDatabaseVersion',
				config_lastedited = Now(),
				config_lasteditedby = 'AUTOMATIC_DATABASE_UPGRADE'
				WHERE config_key = 'DATABASE_VERSION';
			";
			mysqli_query($dbConn, $sql) or die("Migration failed to update DATABASE_VERSION, please notify site admin");
			$sql = "";

			$lastUpdateDatabaseVersion = $newDatabaseVersion;
		}
	}

	$currentUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; 
	die("Database successfully updated to version $lastUpdateDatabaseVersion. <a href='$currentUrl'>Continue</a>");

	StopTimer("MigrateDatabase");
}

function GetJSONDataForTable($tabName){
	global $dbConn;
	AddActionLog("GetJSONDataForTable");
	StartTimer("GetJSONDataForTable");

	$tabName = mysqli_real_escape_string($dbConn, $tabName);

	StopTimer("GetJSONDataForTable");
	return json_encode(GetDataForTable($tabName));
}

function GetJSONDataForAllTables(){
	global $dbConn;
	AddActionLog("GetJSONDataForAllTables");
	StartTimer("GetJSONDataForAllTables");
	
	$tables = GetTables();
	$allData = Array();

	foreach($tables as $i => $table){
		$allData[$table] = GetDataForTable($table);
	}

	StopTimer("GetJSONDataForAllTables");
	return json_encode($allData);
}

?>