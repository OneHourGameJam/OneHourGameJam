<?php

function EditPlatform($platformId, $platformName){
	global $loggedInUser, $_FILES, $dbConn, $ip, $userAgent, $platformData, $adminLogData;

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

	$escaped_platformId = mysqli_real_escape_string($dbConn, $platformId);
	$escaped_platformName = mysqli_real_escape_string($dbConn, $platformName);
	$escaped_iconUrl = mysqli_real_escape_string($dbConn, $iconUrl);

	$sql = "
		UPDATE platform
		SET
			platform_name = '$escaped_platformName',
			platform_icon_url = '$escaped_iconUrl'
		WHERE
			platform_id = $escaped_platformId;
	";
	$data = mysqli_query($dbConn, $sql);
	$sql = "";
	
    $adminLogData->AddToAdminLog("PLATFORM_EDITED", "Platform $platformId edited", "NULL", $loggedInUser->Id, "");

	return "SUCCESS_PLATFORM_EDITED";
}

function PerformAction(&$loggedInUser){
	global $_POST;
	
	if($loggedInUser !== false){
		$platformId = (isset($_POST["platformid"])) ? $_POST["platformid"] : "";
		$platformName = (isset($_POST["platformname"])) ? $_POST["platformname"] : "";

		return EditPlatform($platformId, $platformName);
	}
}

?>
