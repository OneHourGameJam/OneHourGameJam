<?php

function UndeletePlatform($platformId){
	global $loggedInUser, $_FILES, $dbConn, $ip, $userAgent, $platformData, $adminLogData;

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

	$escaped_platformId = mysqli_real_escape_string($dbConn, $platformId);

	$sql = "
		UPDATE platform
		SET
			platform_deleted = 0
		WHERE
			platform_id = $escaped_platformId;
	";
	$data = mysqli_query($dbConn, $sql);
	$sql = "";
	
    $adminLogData->AddToAdminLog("PLATFORM_RESTORED", "Platform $platformId restored", "NULL", $loggedInUser->Id, "");

	return "SUCCESS_PLATFORM_RESTORED";
}

function PerformAction(&$loggedInUser){
	global $_POST;
	
	if($loggedInUser !== false){
		$platformId = (isset($_POST["platformid"])) ? $_POST["platformid"] : "";

		return UndeletePlatform($platformId);
	}
}

?>
