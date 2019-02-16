<?php

function SaveConfig($key, $newValue){
	global $config, $dictionary, $loggedInUser, $actionResult;

	if(IsAdmin($loggedInUser) === false){
		$actionResult = "NOT_AUTHORIZED";
		return; //Lacks permissions to make edits
	}

	if (!isset($config[$key])) {
		//Invalid configuration key
		return;
	}

	if ($config[$key]["EDITABLE"] != true) {
		//Some configuration settings cannot be set via this interface for security reasons.
		return;
	}

	if ($newValue == $config[$key]) {
		return;
	}

	UpdateConfig($config, $key, $newValue, $loggedInUser['id'], $loggedInUser["username"]);
	$actionResult = "SUCCESS";
}

function PerformAction(&$loggedInUser){
	global $_POST;
	
	if(IsAdmin($loggedInUser) !== false){
		$actionResult = "NO_CHANGE";
		foreach($_POST as $key => $value){
			SaveConfig($key, $value);
		}
	}
}

?>