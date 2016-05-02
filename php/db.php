<?php

//Database connection
$dbConn = mysqli_connect("<Address>", "<username>","<password>", "<db name>");
if(!$dbConn ){
	die("Database connection error");
}	
mysqli_select_db($dbConn, "<db name>") or die("Database selection failure");	
mysqli_query($dbConn, 'SET character_set_results="utf8"') or die("Database result type setting failure");
mysqli_set_charset ($dbConn, "utf8");


?>