<?php

function SaveConfig($key, $newValue){
	global $config, $dictionary, $loggedInUser;

	if(!IsAdmin()){
		return; //Lacks permissions to make edits
	}

	$configCategoryID = "";
	$configEntryID = "";
	foreach($dictionary["CONFIG"] as $category => $entries){
		foreach($entries["ENTRIES"] as $entryIndex => $entry){
			if ($entry["KEY"] == $key) {
				$configCategoryID = $category;
				$configEntryID = $entryIndex;
			}
		}
	}

	// If entry not found
	if(strlen($configEntryID) == 0) {
		return;
	}

	if($dictionary["CONFIG"][$configCategoryID]["ENTRIES"][$configEntryID]["EDITABLE"] == FALSE){
		//Some configuration settings cannot be set via this interface for security reasons.
		return;
	}

	$key = str_replace("CONFIG_", "", $key);

	if ($newValue == $config[$key]) {
		return;
	}

	UpdateConfig($key, $newValue, $loggedInUser['id']);
}

if(IsAdmin()){
    foreach($_POST as $key => $value){
        SaveConfig($key, $value);
    }
    LoadConfig(); //reload config
}

?>