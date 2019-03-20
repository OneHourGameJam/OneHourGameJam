<?php

//Logs out the current user by setting their sessionID cookie to blank and expiring it.
//TODO: Clear session from on-server session data
function LogOut(){
	global $dbConn, $config;

	// Delete the session out of our DB
	$sessionID = "".$_COOKIE["sessionID"];
	$pepper = isset($config["PEPPER"]->Value) ? $config["PEPPER"]->Value : "BetterThanNothing";
	$sessionIDHash = HashPassword($sessionID, $pepper, $config["SESSION_PASSWORD_ITERATIONS"]->Value, $config);

	$sql = "
		DELETE FROM session
		WHERE session_id = '$sessionIDHash';
	";

	mysqli_query($dbConn, $sql) ;
	$sql = "";

	// Clear the cookie
	setcookie("sessionID", "", time());
	$_COOKIE["sessionID"] = "";
	
	return "SUCCESS";
}

function PerformAction(&$loggedInUser){
	return LogOut();
}

?>