<?php

function AddAsset($assetId, $author, $title, $description, $type){
	global $loggedInUser, $_FILES, $ip, $userAgent, $assetData, $userData, $configData, $adminLogData, $assetDbInterface;

	$assetId = trim($assetId);
	$author = trim($author);
	$title = trim($title);
	$description = trim($description);
	$type = trim($type);

	//Authorize user
	if(IsAdmin($loggedInUser) === false){
		return "NOT_AUTHORIZED";
	}

	//Validate author
	if(strlen($author) < 1){
		return "AUTHOR_EMPTY";
	}
	if(!isset($userData->UsernameToId[$author])){
		return "INVALID_AUTHOR";
	}

	//Validate title
	if(strlen($title) < 1){
		return "INVALID_TITLE";
	}

	//Validate description
	if(strlen($description) < 1){
		return "INVALID_DESCRIPTION";
	}

	//Validate type
	if(strlen($type) < 1){
		return "ASSET_TYPE_EMPTY";
	}
	switch($type){
		case "AUDIO":
		case "IMAGE":
		case "TEXT":
		case "LINK":
		case "FILE":
			//ok
		break;
		default:
			return "INVALID_ASSET_TYPE";
		break;
	}

	$assetExists = false;
	if(isset($assetId) && $assetId !== null && isset($assetData->AssetModels[$assetId])){
		$assetExists = true;
	}

	$authorUserId = $userData->UsernameToId[$author];

	$ext = pathinfo($_FILES["assetfile"]["name"], PATHINFO_EXTENSION);
	$fileNumber = -1;
	if(!file_exists("assets/$authorUserId")){
		mkdir("assets/$authorUserId");
	}
	for($i = 1; $i <= 100; $i++){
		if(!file_exists("assets/$authorUserId/$i.$ext")){
			$fileNumber = $i;
			break;
		}
	}
	if($fileNumber == -1){
		return "COULD_NOT_FIND_VALID_FILE_NAME";
	}

	//Upload asset
	$assetURL = "";
	$asset_folder = "assets/$authorUserId";
	$asset_name = "$fileNumber.$ext";
	if(isset($_FILES["assetfile"]) && $_FILES["assetfile"] != null && $_FILES["assetfile"]["size"] != 0){
		$target_file = $asset_folder ."/". $asset_name;

		if ($_FILES["assetfile"]["size"] > $configData->ConfigModels[CONFIG_MAX_ASSET_FILE_SIZE_IN_BYTES]->Value) {
			return "UNLOADED_ASSET_TOO_BIG";
		}

		if(!file_exists($asset_folder)){
			mkdir($asset_folder);
			file_put_contents($asset_folder."/.htaccess", "Order allow,deny\nAllow from all");
		}
		move_uploaded_file($_FILES["assetfile"]["tmp_name"], $target_file);
		$assetURL = $target_file;
	}

	if($assetURL == "" && !$assetExists){
		return "COULD_NOT_DETERMINE_URL";
	}

	//Create or update entry
	if($assetExists){
		$content = ($assetUrl != "") ? $assetURL : ($assetData->AssetModels[$assetId]->Content);
		$assetDbInterface->Update($assetId, $authorUserId, $title, $description, $type, $content);

		$adminLogData->AddToAdminLog("ASSET_UPDATE", "Asset ".$assetId." updated with values: Author User Id: '$authorUserId', Title: '$title', Description: '$description', Type: '$type', AssetURL: '$assetURL'", $authorUserId, $loggedInUser->Id, "");
		
		return "SUCCESS_UPDATED";
	}else{
		$assetDbInterface->Insert($ip, $userAgent, $authorUserId, $title, $description, $type, $assetURL);
		
		$adminLogData->AddToAdminLog("ASSET_INSERT", "Asset inserted with values: Id: '$assetId' Author User Id: '$authorUserId', Title: '$title', Description: '$description', Type: '$type', AssetURL: '$assetURL'", $userData->UsernameToId[$author], $loggedInUser->Id, "");
		
		return "SUCCESS_INSERTED";
	}
}

function PerformAction(&$loggedInUser){
	global $_POST;
	
	if(IsAdmin($loggedInUser) !== false){
		$assetId = $_POST[FORM_SAVEASSET_ASSET_ID];
		$author = $_POST[FORM_SAVEASSET_AUTHOR];
		$title = $_POST[FORM_SAVEASSET_TITLE];
		$description = $_POST[FORM_SAVEASSET_DESCRIPTION];
		$type = $_POST[FORM_SAVEASSET_TYPE];

		return AddAsset($assetId, $author, $title, $description, $type);
	}
}

?>