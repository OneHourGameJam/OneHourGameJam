<?php

function SaveConfig($key, $newValue){
	global $config, $dictionary, $loggedInUser;

	if(!IsAdmin()){
		return; //Lacks permissions to make edits
	}

	if ($config[$key]["EDITABLE"] != true) {
		//Some configuration settings cannot be set via this interface for security reasons.
		return;
	}

	if ($newValue == $config[$key]) {
		return;
	}

	UpdateConfig($config, $key, $newValue, $loggedInUser['id']);
}

if(IsAdmin()){
    foreach($_POST as $key => $value){
        SaveConfig($key, $value);
    }
    LoadConfig(); //reload config
}

?>