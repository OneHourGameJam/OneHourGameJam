<?php

//Logs out the current user by setting their sessionID cookie to blank and expiring it.
//TODO: Clear session from on-server session data
function LogOut(){
	global $configData, $sessionDbInterface;

	// Delete the session out of our DB
	$sessionId = "".$_COOKIE["sessionID"];
	$pepper = isset($configData->ConfigModels["PEPPER"]->Value) ? $configData->ConfigModels["PEPPER"]->Value : "BetterThanNothing";
	$sessionIdHash = HashPassword($sessionId, $pepper, $configData->ConfigModels["SESSION_PASSWORD_ITERATIONS"]->Value, $configData);

	$sessionDbInterface->Delete($sessionIdHash);

	// Clear the cookie
	setcookie("sessionID", "", time());
	$_COOKIE["sessionID"] = "";
	
	return "SUCCESS";
}

function PerformAction(&$loggedInUser){
	return LogOut();
}

?>