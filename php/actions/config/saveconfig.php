<?php

function SaveConfig($key, $newValue){
	global $config, $config, $dictionary, $loggedInUser;

	if(IsAdmin($loggedInUser) === false){
		return "NOT_AUTHORIZED";
	}

	if (!isset($config->ConfigModels[$key])) {
		//Invalid configuration key
		return;
	}

	if ($config->ConfigModels[$key]->Editable != true) {
		//Some configuration settings cannot be set via this interface for security reasons.
		return;
	}

	if ($newValue == $config->ConfigModels[$key]->Value) {
		return;
	}

	$config->UpdateConfig($config->ConfigModels, $key, $newValue, $loggedInUser->Id, $loggedInUser->Username);
	return "SUCCESS";
}

function PerformAction(&$loggedInUser){
	global $_POST;
	
	if(IsAdmin($loggedInUser) !== false){
		$overallActionResult = "NO_CHANGE";
		foreach($_POST as $key => $value){
			$actionResult = SaveConfig($key, $value);
			if($actionResult != ""){
				$overallActionResult = $actionResult;
			}
		}
		return $overallActionResult;
	}
}

?>