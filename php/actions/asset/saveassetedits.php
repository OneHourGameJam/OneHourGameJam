<?php

function AddAsset($assetID, $author, $title, $description, $type){
	global $loggedInUser, $_FILES, $dbConn, $ip, $userAgent, $assets, $users, $config;

	$assetID = trim($assetID);
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
	if(!isset($users[$author])){
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
	if(isset($assetID) && $assetID !== null && isset($assets[$assetID])){
		$assetExists = true;
	}

	$ext = pathinfo($_FILES["assetfile"]["name"], PATHINFO_EXTENSION);
	$fileNumber = -1;
	mkdir("assets/$author");
	for($i = 1; $i <= 100; $i++){
		if(!file_exists("assets/$author/$i.$ext")){
			$fileNumber = $i;
			break;
		}
	}
	if($fileNumber == -1){
		return "COULD_NOT_FIND_VALID_FILE_NAME";
	}

	//Upload asset
	$assetURL = "";
	$asset_folder = "assets/$author";
	$asset_name = "$fileNumber.$ext";
	if(isset($_FILES["assetfile"]) && $_FILES["assetfile"] != null && $_FILES["assetfile"]["size"] != 0){
		$target_file = $asset_folder ."/". $asset_name;

		if ($_FILES["assetfile"]["size"] > $config["MAX_ASSET_FILE_SIZE_IN_BYTES"]->Value) {
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
		//Update entry

		$assets[$assetID]->Id = $assetID;
		$assets[$assetID]->Author = $author;
		$assets[$assetID]->Title = $title;
		$assets[$assetID]->Description = $description;
		$assets[$assetID]->Type = $type;
		if($assetURL != ""){
			//Uploaded new file
			$assets[$assetID]->Content = $assetURL;
        }

		$escapedID = mysqli_real_escape_string($dbConn, $assets[$assetID]->Id);
		$escapedAuthor = mysqli_real_escape_string($dbConn, $assets[$assetID]->Author);
		$escapedTitle = mysqli_real_escape_string($dbConn, $assets[$assetID]->Title);
		$escapedDescription = mysqli_real_escape_string($dbConn, $assets[$assetID]->Description);
		$escapedType = mysqli_real_escape_string($dbConn, $assets[$assetID]->Type);
		$escapedContent = mysqli_real_escape_string($dbConn, $assets[$assetID]->Content);

		$sql = "
			UPDATE asset
			SET
				asset_author = '$escapedAuthor',
				asset_title = '$escapedTitle',
				asset_description = '$escapedDescription',
				asset_type = '$escapedType',
				asset_content = '$escapedContent'
			WHERE asset_id = $escapedID;
		";
		$data = mysqli_query($dbConn, $sql);
        $sql = "";

		AddToAdminLog("ASSET_UPDATE", "Asset ".$assetID." updated with values: Author: '$author', Title: '$title', Description: '$description', Type: '$type', AssetURL: '$assetURL'", $author, $loggedInUser->Username);
		
		return "SUCCESS_UPDATED";
	}else{
		$escapedAuthor = mysqli_real_escape_string($dbConn, $author);
		$escapedTitle = mysqli_real_escape_string($dbConn, $title);
		$escapedDescription = mysqli_real_escape_string($dbConn, $description);
		$escapedType = mysqli_real_escape_string($dbConn, $type);
		$escapedContent = mysqli_real_escape_string($dbConn, $assetURL);

		$sql = "
			INSERT INTO asset
			(asset_id,
			asset_datetime,
			asset_ip,
			asset_user_agent,
			asset_author,
			asset_title,
			asset_description,
			asset_type,
			asset_content,
			asset_deleted)
			VALUES
			(null,
			Now(),
			'$ip',
			'$userAgent',
			'$escapedAuthor',
			'$escapedTitle',
			'$escapedDescription',
			'$escapedType',
			'$escapedContent',
			0);

		";
		$data = mysqli_query($dbConn, $sql);
		$sql = "";
		
		AddToAdminLog("ASSET_INSERT", "Asset inserted with values: Id: '$assetID' Author: '$author', Title: '$title', Description: '$description', Type: '$type', AssetURL: '$assetURL'", $author, $loggedInUser->Username);
		
		return "SUCCESS_INSERTED";
	}
}

function PerformAction(&$loggedInUser){
	global $_POST;
	
	if(IsAdmin($loggedInUser) !== false){
		$assetID = $_POST["asset_id"];
		$author = $_POST["author"];
		$title = $_POST["title"];
		$description = $_POST["description"];
		$type = $_POST["type"];

		return AddAsset($assetID, $author, $title, $description, $type);
	}
}

?>