<?php

function EditPlatform(MessageService &$messageService, $platformId, $platformName){
	global $loggedInUser, $_FILES, $ip, $userAgent, $platformData, $platformDbInterface, $userData;

	$platformName = trim($platformName);
	$platformId = intval(trim($platformId));

	//Authorize user
	if($loggedInUser === false){
		return "NOT_LOGGED_IN";
	}

	if(IsAdmin($loggedInUser) === false){
		return "NOT_AUTHORIZED";
	}

	//Validate platform name
	if(strlen($platformName) < 1){
		return "MISSING_PLATFORM_NAME";
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
	
	foreach($platformData->PlatformModels as $i => $platformModel){
		if($platformModel->Id != $platformId){
			if($platformModel->Name == $platformName){
				return "DUPLICATE_PLATFORM_NAME";
			}
		}
	}

	//Upload platform icon
	$platform_folder = "images/platforms";
	if(isset($_FILES["platformFile"]) && $_FILES["platformFile"] != null && $_FILES["platformFile"]["size"] != 0){
		$imageFileType = strtolower(pathinfo($_FILES["platformFile"]["name"], PATHINFO_EXTENSION));
		$target_file = $platform_folder."/".$platformName.".".$imageFileType;
		$is_image = getimagesize($_FILES["platformFile"]["tmp_name"]) !== false;

		if(!$is_image) {
			return "ICON_NOT_AN_IMAGE";
		}

		if($_FILES["platformFile"]["size"] > 20000) {	//20kB
			return "ICON_TOO_BIG";
		}

		if($imageFileType != "png") {
			return "ICON_WRONG_FILE_TYPE";
		}

		move_uploaded_file($_FILES["platformFile"]["tmp_name"], "template/".$target_file);
		$iconUrl = $target_file;
	}

	$platformDbInterface->Update($platformId, $platformName, $iconUrl);

	$messageService->SendMessage(LogMessage::UserLogMessage(
		"PLATFORM_EDITED", 
		"Platform $platformId edited (name: $platformName, icon url: $iconUrl)", 
		$loggedInUser->Id)
	);
	$userData->LogAdminAction($loggedInUser->Id);

	return "SUCCESS_PLATFORM_EDITED";
}

function PerformAction(MessageService &$messageService, &$loggedInUser){
	global $_POST;
	
	if($loggedInUser !== false){
		$platformId = (isset($_POST[FORM_EDITPLATFORM_PLATFORM_ID])) ? $_POST[FORM_EDITPLATFORM_PLATFORM_ID] : "";
		$platformName = (isset($_POST[FORM_EDITPLATFORM_NAME])) ? $_POST[FORM_EDITPLATFORM_NAME] : "";

		return EditPlatform($messageService, $platformId, $platformName);
	}
}

?>
