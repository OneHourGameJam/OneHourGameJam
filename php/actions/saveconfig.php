<?php

function SaveConfig($key, $newValue){
	global $config, $dictionary, $loggedInUser, $actionResult;

	if(!IsAdmin()){
		$actionResult = "NOT_AUTHORIZED";
		return; //Lacks permissions to make edits
	}

	if ($config[$key]["EDITABLE"] != true) {
		//Some configuration settings cannot be set via this interface for security reasons.
		return;
	}

	if ($newValue == $config[$key]) {
		$actionResult = "SUCCESS";
		return;
	}

	UpdateConfig($config, $key, $newValue, $loggedInUser['id']);
}

if(IsAdmin()){
	$actionResult = "NO_CHANGE";
    foreach($_POST as $key => $value){
        SaveConfig($key, $value);
    }
    LoadConfig(); //reload config
}

?>