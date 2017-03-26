<?php

include_once("config/dbconfig.php");

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
	$tables = Array();
	$data = mysqli_query($dbConn, "SHOW TABLES");
	while($info = mysqli_fetch_array($data)){
		$tables[] = $info[0];
	}
	return $tables;
}

function GetColumnsForTable($tabName){
	global $dbConn;
	
	$tabName = mysqli_real_escape_string($dbConn, $tabName);
	
	$columns = Array();
	$data = mysqli_query($dbConn, "SHOW COLUMNS IN $tabName");
	while($info = mysqli_fetch_array($data)){
		$columns[] = $info[0];
	}
	return $columns;
}

function GetDataForTable($tabName){
	global $dbConn;
	
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
	
	return $tabData;
}

function GetJSONDataForTable($tabName){
	global $dbConn;
	
	$tabName = mysqli_real_escape_string($dbConn, $tabName);
	
	return json_encode(GetDataForTable($tabName));
}

function GetJSONDataForAllTables(){
	global $dbConn;
	
	$tables = GetTables();
	$allData = Array();
	
	foreach($tables as $i => $table){
		$allData[$table] = GetDataForTable($table);
	}
	
	return json_encode($allData);
}

?>