<?php

$dbConfigPath = "config/dbconfig.php";
$baseDatabaseSqlFile = "SQL/versions/09_1hgj.sql";

function IsDatabaseConfigFilePresent(){
	global $dbConfigPath;

	return file_exists($dbConfigPath);
}

function RunInstallPage(&$dictionary) {
	global $dbConfigPath;

	// Safety check, make sure that this is a fresh installation
	if (file_exists($dbConfigPath)) {
		$dictionary['error_message'] = "Database has been configured already! Please delete the $dbConfigPath file to start the installation process.";
		$dictionary['template_file'] = $templateBasePath."install_fail.html";
		return;
	}

	// Check to see if this is a setup post request
	if(isset($_POST[FORM_POST_ACTION])) {
		if ($_POST[FORM_POST_ACTION] == "setup") {
			// Run the database setup, and then set the template file based on if there was an error or not.
			$errorMessage = RunSetupAction();
			$dictionary['error_message'] = $errorMessage;

			if ($errorMessage == "") {
				$dictionary['template_file'] = $templateBasePath."install_success.html";
				return;
			} else {
				$dictionary['template_file'] = $templateBasePath."install_fail.html";
				return;
			}
		}
	}
	
	// Assume that if we got here we want to display the form.
	$dictionary['template_file'] = $templateBasePath."install_form.html";
}

// Runs the setup and returns any errors encountered
function RunSetupAction() {
	global $dbConfigPath, $baseDatabaseSqlFile;

	if(!isset($_POST[FORM_INSTALL_DB_HOST]) || !isset($_POST[FORM_INSTALL_DB_USERNAME]) || !isset($_POST[FORM_INSTALL_DB_PASSWORD]) || !isset($_POST[FORM_INSTALL_DB_NAME])){
		die("Some setup parameters were not set up correctly");
	}

	$dbHost = $_POST[FORM_INSTALL_DB_HOST];
	$dbUser = $_POST[FORM_INSTALL_DB_USERNAME];
	$dbPassword = $_POST[FORM_INSTALL_DB_PASSWORD];
	$dbName = $_POST[FORM_INSTALL_DB_NAME];
	
	$initialiseDatabase = isset($_POST[FORM_INSTALL_INIT_DATABASE]);

	// Check connection
	$conn = @mysqli_connect($dbHost, $dbUser, $dbPassword);

	if (!$conn) {
		return "Connection failed: " . mysqli_connect_error();
	}

	// Create the database if needed
	$escapedDBName = mysqli_real_escape_string($conn, $dbName);
	$sql = "CREATE DATABASE IF NOT EXISTS $escapedDBName;";
	if (@mysqli_query($conn, $sql) == FALSE) {
		return "Unable to create/verify database: " . mysqli_error($conn);
	}
	$sql = "";

	if (mysqli_select_db($conn, $dbName) == FALSE) {
		return "Unable to select database: " . mysqli_error($conn);
	}
	
	// Run the installation script
	if($initialiseDatabase){

		$sql = "SELECT COUNT(DISTINCT table_name) AS 'tables_in_database' FROM information_schema.columns WHERE table_schema = '$escapedDBName'";
		$data = mysqli_query($conn, $sql);
		$sql = "";
	
		$databaseInfo = mysqli_fetch_array($data);
		if(!isset($databaseInfo["tables_in_database"]) || $databaseInfo["tables_in_database"] > 0){
			die("The provided information points to a database which is not empty, but initialisation is desired. This would delete all data currently stored in the database. Please first drop all tables from this database, point to a new location, or if the database is an existing one hour game jam database, untick 'Initialise database' on the <a href='install.php'>install page</a>.");
		}

		$sql = file_get_contents($baseDatabaseSqlFile);
		if(mysqli_multi_query($conn, $sql)) {
			do {
				mysqli_next_result($conn);
			}
			while(mysqli_more_results($conn));
		}
		$sql = "";
	}

	if(mysqli_errno($conn)) {
		return "Unable to run installation script: " . mysqli_error($conn);
	}
	
	
	// Save credentials in the config file
	$safeHost = addslashes($dbHost);
	$safeUser = addslashes($dbUser);
	$safePassword = addslashes($dbPassword);
	$safeName = addslashes($dbName);

	$configData = "<?php
\$dbAddress = \"$safeHost\";
\$dbUsername = \"$safeUser\";
\$dbPassword = \"$safePassword\";
\$dbDatabaseName = \"$safeName\";
?>";
	
	$result = file_put_contents($dbConfigPath, $configData);
	if ($result == FALSE) {
		return "Unable to write config file. Please manually fill it out (database has been initialized). It can be located at $dbConfigPath";
	}
	
	return "";
}