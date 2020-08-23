<?php

function UndeletePlatform(MessageService &$messageService, $platformId){
	global $loggedInUser, $_FILES, $ip, $userAgent, $platformData, $platformDbInterface;

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

	$messageService->SendMessage(LogMessage::UserLogMessage(
		"PLATFORM_RESTORED", 
		"Platform $platformId restored", 
		$loggedInUser->Id)
	);

	return "SUCCESS_PLATFORM_RESTORED";
}

function PerformAction(MessageService &$messageService, &$loggedInUser){
	global $_POST;
	
	if($loggedInUser !== false){
		$platformId = (isset($_POST[FORM_UNDELETEPLATFORM_NAME])) ? $_POST[FORM_UNDELETEPLATFORM_NAME] : "";

		return UndeletePlatform($messageService, $platformId);
	}
}

?>
