<?php

//Creates or updates a jam entry. $jam_number is a mandatory jam number to submit to.
//All other parameters are strings: $gameName and $gameURL must be non-blank
//$gameURL must be a valid URL, $screenshotURL can either be blank or a valid URL.
//If blank, a default image is used instead. description must be non-blank.
//Function also authorizes the user (must be logged in)
function SubmitEntry($jam_number, $gameName, $platforms, $screenshotURL, $description, $jamColorNumber){
	global $loggedInUser, $_FILES, $dbConn, $ip, $userAgent, $jamData, $gameData, $configData;

	$gameName = trim($gameName);
	$screenshotURL = trim($screenshotURL);
	$description = trim($description);
	$jamColorNumber = intval(trim($jamColorNumber));

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
	if (!is_int($jam_number)) {
		return "INVALID_JAM_NUMBER";
	}

	$jam = $jamData->GetJamByNumber($jam_number);
	if($jam == null || $jam->JamNumber == 0){
		return "NO_JAM_TO_SUBMIT_TO";
	}

	if(count($jamData->JamModels) == 0){
		return "NO_JAM_TO_SUBMIT_TO";
	}

	//Validate color
	if($jamColorNumber < 0 || count($jam->Colors) <= $jamColorNumber){
		return "INVALID_COLOR";
	}
	$color = $jam->Colors[$jamColorNumber];

	//Upload screenshot
	$jam_folder = "data/jams/jam_$jam_number";
	if(isset($_FILES["screenshotfile"]) && $_FILES["screenshotfile"] != null && $_FILES["screenshotfile"]["size"] != 0){
		$imageFileType = strtolower(pathinfo($_FILES["screenshotfile"]["name"], PATHINFO_EXTENSION));
		$target_file = $jam_folder . "/".$loggedInUser->Username."." . $imageFileType;
		$is_image = getimagesize($_FILES["screenshotfile"]["tmp_name"]) !== false;

		if(!$is_image) {
			return "SCREENSHOT_NOT_AN_IMAGE";
		}

		if($_FILES["screenshotfile"]["size"] > $configData->ConfigModels["MAX_SCREENSHOT_FILE_SIZE_IN_BYTES"]->Value) {
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
		$screenshotURL = "logo.png";
	}

	//Create or update entry
	foreach($gameData->GameModels as $i => $gameModel){
		if($gameModel->Deleted){
			continue;
		}

		if($gameModel->JamNumber != $jam_number){
			continue;
		}

		if($gameModel->AuthorUserId != $loggedInUser->Id){
			continue;
		}

		//Updating existing entry
		$existingScreenshot = $gameModel->UrlScreenshot;
		if($screenshotURL == "logo.png"){
			if($existingScreenshot != "" && $existingScreenshot != "logo.png"){
				$screenshotURL = $existingScreenshot;
			}
		}

		$escapedGameName = mysqli_real_escape_string($dbConn, $gameName);
		$escapedScreenshotURL = mysqli_real_escape_string($dbConn, $screenshotURL);
		$escapedDescription = mysqli_real_escape_string($dbConn, $description);
		$escapedAuthorUserId = mysqli_real_escape_string($dbConn, $gameModel->AuthorUserId);
		$escaped_jamNumber = mysqli_real_escape_string($dbConn, $jam_number);
		$escaped_color = mysqli_real_escape_string($dbConn, $color);

		$sql = "
		UPDATE entry
		SET
			entry_title = '$escapedGameName',
			entry_screenshot_url = '$escapedScreenshotURL',
			entry_description = '$escapedDescription',
			entry_color = '$escaped_color'
		WHERE
			entry_author_user_id = $escapedAuthorUserId
		AND entry_jam_number = $escaped_jamNumber
		AND entry_deleted = 0;
		";
		$data = mysqli_query($dbConn, $sql);
		$sql = "";

		foreach($platforms as $i => $platform){
			if($platform["url"] != ""){
				SubmitPlatformGame($gameModel->Id, $platform["platform_id"], $platform["url"]);
			}else{
				DeletePlatformGame($gameModel->Id, $platform["platform_id"]);
			}
		}

		return "SUCCESS_ENTRY_UPDATED";
	}

	$currentJamData = GetCurrentJamNumberAndID();

	if ($jam_number != $currentJamData["NUMBER"]) {
		return "CANNOT_SUBMIT_TO_PAST_JAM";
	}

	$escaped_ip = mysqli_real_escape_string($dbConn, $ip);
	$escaped_userAgent = mysqli_real_escape_string($dbConn, $userAgent);
	$escaped_jamId = mysqli_real_escape_string($dbConn, $jam->Id);
	$escaped_jamNumber = mysqli_real_escape_string($dbConn, $jam->JamNumber);
	$escaped_gameName = mysqli_real_escape_string($dbConn, $gameName);
	$escaped_description = mysqli_real_escape_string($dbConn, $description);
	$escaped_author_user_id = mysqli_real_escape_string($dbConn, $loggedInUser->Id);
	$escaped_ssURL = mysqli_real_escape_string($dbConn, $screenshotURL);
	$escaped_color = mysqli_real_escape_string($dbConn, $color);

	$sql = "
		INSERT INTO entry
		(entry_id,
		entry_datetime,
		entry_ip,
		entry_user_agent,
		entry_jam_id,
		entry_jam_number,
		entry_title,
		entry_description,
		entry_author_user_id,
		entry_screenshot_url,
		entry_color)
		VALUES
		(null,
		Now(),
		'$escaped_ip',
		'$escaped_userAgent',
		$escaped_jamId,
		$escaped_jamNumber,
		'$escaped_gameName',
		'$escaped_description',
		$escaped_author_user_id,
		'$escaped_ssURL',
		'$escaped_color');
	";
	$data = mysqli_query($dbConn, $sql);
	$sql = "";

	$sql = "
		SELECT entry_id
		FROM entry
		WHERE entry_jam_id = $escaped_jamId
		  AND entry_author_user_id = $escaped_author_user_id
		  AND entry_deleted = 0
	";
	$data = mysqli_query($dbConn, $sql);
	$sql = "";

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
	global $dbConn;

	$escapedEntryId = mysqli_real_escape_string($dbConn, $entryId);
	$escapedPlatformId = mysqli_real_escape_string($dbConn, $platformId);
	$escapedUrl = mysqli_real_escape_string($dbConn, $url);

	$sql = "SELECT platformentry_id FROM platform_entry WHERE platformentry_entry_id = $escapedEntryId AND platformentry_platform_id = $escapedPlatformId;";
	$data = mysqli_query($dbConn, $sql);
	$sql = "";

	if($info = mysqli_fetch_array($data)){
		$platformEntryId = intval($info["platformentry_id"]);

		$sql = "UPDATE platform_entry SET platformentry_url = '$escapedUrl' WHERE platformentry_id = $platformEntryId;";
		$data = mysqli_query($dbConn, $sql);
		$sql = "";
		print "<br><b>PlatformEntry -> UPDATE ID $platformEntryId</b>";
	}else{
		$sql = "
			INSERT INTO platform_entry
			(platformentry_id, platformentry_entry_id, platformentry_platform_id, platformentry_url)
			VALUES
			(null, $escapedEntryId, $escapedPlatformId, '$escapedUrl');
		";
		$data = mysqli_query($dbConn, $sql);
		$sql = "";
		print "<br><b>PlatformEntry -> INSERT</b>";
	}
}

function DeletePlatformGame($entryId, $platformId){
	global $dbConn;

	$escapedEntryId = mysqli_real_escape_string($dbConn, $entryId);
	$escapedPlatformId = mysqli_real_escape_string($dbConn, $platformId);

	$sql = "DELETE FROM platform_entry WHERE platformentry_entry_id = $escapedEntryId AND platformentry_platform_id = $escapedPlatformId;";
	$data = mysqli_query($dbConn, $sql);
	$sql = "";
	print "<br><b>PlatformEntry -> DELETE</b>";
}

function PerformAction(&$loggedInUser){
	global $_POST, $satisfactionData, $platformData;
	
	if($loggedInUser !== false){
		$gameName = (isset($_POST["gamename"])) ? $_POST["gamename"] : "";
		$screenshotURL = (isset($_POST["screenshoturl"])) ? $_POST["screenshoturl"] : "";
		$description = (isset($_POST["description"])) ? $_POST["description"] : "";
		$jamNumber = (isset($_POST["jam_number"])) ? intval($_POST["jam_number"]) : -1;
		$jamColorNumber = (isset($_POST["colorNumber"])) ? intval($_POST["colorNumber"]) : 0;

		$platforms = Array();
		foreach($platformData->PlatformModels as $i => $platformModel){
			$platform = Array();
			$platform["platform_id"] = $platformModel->Id;
			$platform["url"] = (isset($_POST["gameurl".$platformModel->Id])) ? $_POST["gameurl".$platformModel->Id] : "";

			$platforms[] = $platform;
		}

		$satisfaction = (isset($_POST["satisfaction"])) ? intval($_POST["satisfaction"]) : 0;
		if($satisfaction != 0){
			$satisfactionData->SubmitSatisfaction($loggedInUser, "JAM_$jamNumber", $satisfaction);
		}

		return SubmitEntry($jamNumber, $gameName, $platforms, $screenshotURL, $description, $jamColorNumber);
	}
}

?>
