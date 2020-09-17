<?php

function SubmitEntry($jamNumber, $gameName, $platforms, $description, $colorBackground, $colorText){
	global $loggedInUser, $_FILES, $ip, $userAgent, $jamData, $gameData, $configData, $gameDbInterface;

	$gameName = trim($gameName);
	$description = trim($description);
	$colorBackground = trim($colorBackground);
	$colorText = trim($colorText);

	//Authorize user
	if($loggedInUser === false){
		return "NOT_LOGGED_IN";
	}

	//Validate game name
	if(strlen($gameName) < 1){
		return "MISSING_GAME_NAME";
	}

	$aPlatformGameUrlIsNotBlank = false;
	foreach($platforms as $i => $platform){
		$platforms[$i]["url"] = trim(SanitizeURL(trim($platform["url"])));
		if($platforms[$i]["url"] != ""){
			$aPlatformGameUrlIsNotBlank = true;
		}
	}

	//Did at least one url pass validation?
	if(!$aPlatformGameUrlIsNotBlank){
		return "INVALID_GAME_URL";
	}

	//Validate description
	if(strlen($description) <= 0){
		return "INVALID_DESCRIPTION";
	}

	//Check that a jam exists
	if (!is_int($jamNumber)) {
		return "INVALID_JAM_NUMBER";
	}

	if(count($jamData->JamModels) == 0){
		return "NO_JAM_TO_SUBMIT_TO";
	}

	$jam = $jamData->GetJamByNumber($jamNumber);
	if($jam == null || $jam->JamNumber == 0){
		return "NO_JAM_TO_SUBMIT_TO";
	}

	if($jam->Deleted){
		return "NO_JAM_TO_SUBMIT_TO";
	}

	if(strtotime($jam->StartTime . " UTC") > time()){
		return "JAM_NOT_STARTED";
	}

	//Validate color
	if(!preg_match("/^#([A-Fa-f0-9]{6})$/", $colorBackground)){
		return "INVALID_COLOR";
	}
	if(!preg_match("/^#([A-Fa-f0-9]{6})$/", $colorText)){
		return "INVALID_COLOR";
	}

	$colorBackgroundWithoutHash = substr($colorBackground, 1, 6);
	$colorTextWithoutHash = substr($colorText, 1, 6);
	
	//Upload screenshot
	$screenshotURL = "";
	$jam_folder = "data/jams/jam_$jamNumber";
	if(isset($_FILES["screenshotfile"]) && $_FILES["screenshotfile"] != null && $_FILES["screenshotfile"]["size"] != 0){
		$imageFileType = strtolower(pathinfo($_FILES["screenshotfile"]["name"], PATHINFO_EXTENSION));
		$target_file = $jam_folder . "/".$loggedInUser->Username."." . $imageFileType;
		$is_image = getimagesize($_FILES["screenshotfile"]["tmp_name"]) !== false;

		if(!$is_image) {
			return "SCREENSHOT_NOT_AN_IMAGE";
		}

		if($_FILES["screenshotfile"]["size"] > $configData->ConfigModels[CONFIG_MAX_SCREENSHOT_FILE_SIZE_IN_BYTES]->Value) {
			return "SCREENSHOT_TOO_BIG";
		}

		if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
		&& $imageFileType != "gif" ) {
			return "SCREENSHOT_WRONG_FILE_TYPE";
		}

		if(!file_exists($jam_folder)){
			mkdir($jam_folder);
			file_put_contents($jam_folder."/.htaccess", "Order allow,deny\nAllow from all");
		}
		move_uploaded_file($_FILES["screenshotfile"]["tmp_name"], $target_file);
		$screenshotURL = $target_file;
	}

	//Default screenshot URL
	if($screenshotURL == ""){
		$screenshotURL = $jam->DefaultIconUrl;
	}

	//Create or update entry
	foreach($gameData->GameModels as $i => $gameModel){
		if($gameModel->Deleted){
			continue;
		}

		if($gameModel->JamNumber != $jamNumber){
			continue;
		}

		if($gameModel->AuthorUserId != $loggedInUser->Id){
			continue;
		}

		//Updating existing entry
		$existingScreenshot = $gameModel->UrlScreenshot;
		if($screenshotURL == $jam->DefaultIconUrl){
			if($existingScreenshot != "" && $existingScreenshot != $jam->DefaultIconUrl){
				$screenshotURL = $existingScreenshot;
			}
		}

		$gameDbInterface->Update($jamNumber, $gameModel->AuthorUserId, $gameName, $screenshotURL, $description, $colorBackgroundWithoutHash, $colorTextWithoutHash);

		foreach($platforms as $i => $platform){
			if($platform["url"] != ""){
				SubmitPlatformGame($gameModel->Id, $platform["platform_id"], $platform["url"]);
			}else{
				DeletePlatformGame($gameModel->Id, $platform["platform_id"]);
			}
		}

		return "SUCCESS_ENTRY_UPDATED";
	}

	$currentJamData = GetCurrentJamNumberAndId();

	if($configData->ConfigModels[CONFIG_CAN_SUBMIT_TO_PAST_JAMS]->Value == 0){
		if ($jamNumber != $currentJamData["NUMBER"]) {
			return "CANNOT_SUBMIT_TO_PAST_JAM";
		}
	}

	$gameDbInterface->Insert($ip, $userAgent, $jam->Id, $jam->JamNumber, $gameName, $description, $loggedInUser->Id, $screenshotURL, $colorBackgroundWithoutHash, $colorTextWithoutHash);

	$data = $gameDbInterface->SelectSingleEntryId($jam->Id, $loggedInUser->Id);

	if($info = mysqli_fetch_array($data)){
		$gameId = $info["entry_id"];

		foreach($platforms as $i => $platform){
			if($platform["url"] != ""){
				SubmitPlatformGame($gameId, $platform["platform_id"], $platform["url"]);
			}else{
				DeletePlatformGame($gameId, $platform["platform_id"]);
			}
		}
	}else{
		return "ENTRY_NOT_ADDED";
	}
	

	return "SUCCESS_ENTRY_ADDED";
}

function SubmitPlatformGame($entryId, $platformId, $url){
	global $platformGameDbInterface;

	$data = $platformGameDbInterface->SelectSinglePlatformEntryId($entryId, $platformId);

	if($info = mysqli_fetch_array($data)){
		$platformEntryId = intval($info["platformentry_id"]);

		$platformGameDbInterface->UpdateUrl($platformEntryId, htmlspecialchars_decode($url, ENT_HTML5));
	}else{
		$platformGameDbInterface->Insert($entryId, $platformId, htmlspecialchars_decode($url, ENT_HTML5));
	}
}

function DeletePlatformGame($entryId, $platformId){
	global $platformGameDbInterface;
	
	$data = $platformGameDbInterface->SelectSinglePlatformEntryId($entryId, $platformId);

	if($info = mysqli_fetch_array($data)){
		$platformEntryId = intval($info["platformentry_id"]);

		$platformGameDbInterface->Delete($platformEntryId);
	}
}

function PerformAction(MessageService &$messageService, &$loggedInUser){
	global $_POST, $satisfactionData, $platformData;
	
	if($loggedInUser !== false){
		$gameName = (isset($_POST[FORM_SUBMIT_NAME])) ? $_POST[FORM_SUBMIT_NAME] : "";
		$description = (isset($_POST[FORM_SUBMIT_DESCRIPTION])) ? $_POST[FORM_SUBMIT_DESCRIPTION] : "";
		$jamNumber = (isset($_POST[FORM_SUBMIT_JAM_NUMBER])) ? intval($_POST[FORM_SUBMIT_JAM_NUMBER]) : -1;
		$colorBackground = (isset($_POST[FORM_SUBMIT_BACKGROUND_COLOR])) ? $_POST[FORM_SUBMIT_BACKGROUND_COLOR] : "";
		$colorText = (isset($_POST[FORM_SUBMIT_TEXT_COLOR])) ? $_POST[FORM_SUBMIT_TEXT_COLOR] : "";

		$platforms = Array();
		foreach($platformData->PlatformModels as $i => $platformModel){
			$platform = Array();
			$platform["platform_id"] = $platformModel->Id;
			$platform["url"] = (isset($_POST[FORM_SUBMIT_URL.$platformModel->Id])) ? $_POST[FORM_SUBMIT_URL.$platformModel->Id] : "";

			$platforms[] = $platform;
		}

		$satisfaction = (isset($_POST[FORM_SUBMIT_SATISFACTION])) ? intval($_POST[FORM_SUBMIT_SATISFACTION]) : 0;
		if($satisfaction != 0){
			$satisfactionData->SubmitSatisfaction($loggedInUser, "JAM_$jamNumber", $satisfaction);
		}

		return SubmitEntry($jamNumber, $gameName, $platforms, $description, $colorBackground, $colorText);
	}
}

?>
