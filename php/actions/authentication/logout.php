<?php

//Logs out the current user by setting their sessionID cookie to blank and expiring it.
//TODO: Clear session from on-server session data
function LogOut(){
	global $configData, $sessionDbInterface, $_COOKIE;

	// Delete the session out of our DB
	$sessionId = "".$_COOKIE[COOKIE_SESSION_ID];
	$pepper = isset($configData->ConfigModels[CONFIG_PEPPER]->Value) ? $configData->ConfigModels[CONFIG_PEPPER]->Value : "BetterThanNothing";
	$sessionIdHash = HashPassword($sessionId, $pepper, $configData->ConfigModels[CONFIG_SESSION_PASSWORD_ITERATIONS]->Value, $configData);

	$sessionDbInterface->Delete($sessionIdHash);

	// Clear the cookie
	setcookie(COOKIE_SESSION_ID, "", time());
	$_COOKIE[COOKIE_SESSION_ID] = "";
	
	return "SUCCESS";
}

function PerformAction(&$loggedInUser){
	return LogOut();
}

?>