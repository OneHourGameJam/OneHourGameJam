<?php

//Logs out the current user by setting their sessionID cookie to blank and expiring it.
//TODO: Clear session from on-server session data
function LogOut(){
	global $dbConn, $config;

	// Delete the session out of our DB
	$sessionID = "".$_COOKIE["sessionID"];
	$pepper = isset($config["PEPPER"]["VALUE"]) ? $config["PEPPER"]["VALUE"] : "BetterThanNothing";
	$sessionIDHash = HashPassword($sessionID, $pepper, $config["SESSION_PASSWORD_ITERATIONS"]["VALUE"]);

	$sql = "
		DELETE FROM session
		WHERE session_id = '$sessionIDHash';
	";

	mysqli_query($dbConn, $sql) ;
	$sql = "";

	// Clear the cookie
	setcookie("sessionID", "", time());
	$_COOKIE["sessionID"] = "";
}

$loginChecked = false;
LogOut();
$page = "main";

?>