<?php

function UndeletePlatform($platformId){
	global $loggedInUser, $_FILES, $ip, $userAgent, $platformData, $adminLogData, $platformDbInterface;

	$platformId = intval(trim($platformId));

	//Authorize user
	if($loggedInUser === false){
		return "NOT_LOGGED_IN";
	}

	if(IsAdmin($loggedInUser) === false){
		return "NOT_AUTHORIZED";
	}

	$iconUrl = "";
	$platformWithIdExists = false;
	foreach($platformData->PlatformModels as $i => $platformModel){
		if($platformModel->Id == $platformId){
			$platformWithIdExists = true;
			$iconUrl = $platformModel->IconUrl;
			break;
		}
	}

	if(!$platformWithIdExists){
		return "UNKNOWN_PLATFORM";
	}

	$platformDbInterface->RestoreSoftDeleted($platformId);
	
    $adminLogData->AddToAdminLog("PLATFORM_RESTORED", "Platform $platformId restored", "NULL", $loggedInUser->Id, "");

	return "SUCCESS_PLATFORM_RESTORED";
}

function PerformAction(&$loggedInUser){
	global $_POST;
	
	if($loggedInUser !== false){
		$platformId = (isset($_POST[FORM_UNDELETEPLATFORM_NAME])) ? $_POST[FORM_UNDELETEPLATFORM_NAME] : "";

		return UndeletePlatform($platformId);
	}
}

?>
