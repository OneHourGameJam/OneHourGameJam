<?php

function NewPlatform($platformName){
	global $loggedInUser, $_FILES, $ip, $userAgent, $platformData, $adminLogData, $platformDbInterface;

	$platformName = trim($platformName);

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

	$platformWithNameExists = false;
	foreach($platformData->PlatformModels as $i => $platformModel){
		if($platformModel->Name == $platformName){
			return "DUPLICATE_PLATFORM_NAME";
		}
	}

	//Upload platform icon
	$platform_folder = "images/platforms";
	$iconUrl = "";
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

	if($iconUrl == ""){
		return "ICON_FAILED_TO_UPLOAD";
	}

	$platformDbInterface->Insert($platformName, $iconUrl);
	
    $adminLogData->AddToAdminLog("PLATFORM_ADDED", "Platform $platformName added (name: $platformName, icon url: $iconUrl)", "NULL", $loggedInUser->Id, "");

	return "SUCCESS_PLATFORM_ADDED";
}

function PerformAction(&$loggedInUser){
	global $_POST;
	
	if($loggedInUser !== false){
		$platformName = (isset($_POST["platformname"])) ? $_POST["platformname"] : "";

		return NewPlatform($platformName);
	}
}

?>
