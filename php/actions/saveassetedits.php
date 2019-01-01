<?php

function AddAsset($assetID, $author, $title, $description, $type){
	global $loggedInUser, $_FILES, $dbConn, $ip, $userAgent, $assets, $users;
	
	$assetID = trim($assetID);
	$author = trim($author);
	$title = trim($title);
	$description = trim($description);
	$type = trim($type);
	
	//Authorize user
	if(!IsAdmin()){
		AddAdminAuthorizationWarning(false);
		return;
	}
	
	//Validate author
	if(strlen($author) < 1){
		AddDataWarning("Asset author is empty", false);
		return;
	}
	if(!isset($users[$author])){
		AddDataWarning("Author is not a valid user (must use their username)", false);
		return;
	}
	
	//Validate title
	if(strlen($title) < 1){
		AddDataWarning("Asset title is empty", false);
		return;
	}
	
	//Validate description
	if(strlen($description) < 1){
		AddDataWarning("Asset description is empty", false);
		return;
	}
	
	//Validate type
	if(strlen($type) < 1){
		AddDataWarning("Asset type is blank", false);
		return;
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
			AddDataWarning("Invalid asset type", false);
			return;
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
		AddInternalDataError("Could not find valid file name for asset.", false);
		return;
	}
	
	//Upload asset
	$assetURL = "";
	$asset_folder = "assets/$author";
	$asset_name = "$fileNumber.$ext";
	if(isset($_FILES["assetfile"]) && $_FILES["assetfile"] != null && $_FILES["assetfile"]["size"] != 0){
		$uploadPass = 1;
		$target_file = $asset_folder ."/". $asset_name;
		
		if ($_FILES["assetfile"]["size"] > 15000000) {
			AddDataWarning("Uploaded screenshot is too big (max 15MB)", false);
			return;
			$uploadPass = 0;
		}
		
		if($uploadPass == 1){
			if(!file_exists($asset_folder)){
				mkdir($asset_folder);
				file_put_contents($asset_folder."/.htaccess", "Order allow,deny\nAllow from all");
			}
			move_uploaded_file($_FILES["assetfile"]["tmp_name"], $target_file);
			$assetURL = $target_file;
		}
	}
	
	if($assetURL == "" && !$assetExists){
		AddInternalDataError("Upload failure - Could not determine URL", false);
		return;
	}
	
	//Create or update entry
	if($assetExists){
		//Update entry
		
		$assets[$assetID]["id"] = $assetID;
		$assets[$assetID]["author"] = $author;
		$assets[$assetID]["title"] = $title;
		$assets[$assetID]["description"] = $description;
		$assets[$assetID]["type"] = $type;
		if($assetURL != ""){
			//Uploaded new file
			$assets[$assetID]["content"] = $assetURL;
        }

		$escapedID = mysqli_real_escape_string($dbConn, $assets[$assetID]["id"]);
		$escapedAuthor = mysqli_real_escape_string($dbConn, $assets[$assetID]["author"]);
		$escapedTitle = mysqli_real_escape_string($dbConn, $assets[$assetID]["title"]);
		$escapedDescription = mysqli_real_escape_string($dbConn, $assets[$assetID]["description"]);
		$escapedType = mysqli_real_escape_string($dbConn, $assets[$assetID]["type"]);
		$escapedContent = mysqli_real_escape_string($dbConn, $assets[$assetID]["content"]);
		
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
        
        AddToAdminLog("ASSET_UPDATE", "Asset ".$assetID." updated with values: Author: '$author', Title: '$title', Description: '$description', Type: '$type', AssetURL: '$assetURL'", $author);
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
        
        AddToAdminLog("ASSET_INSERT", "Asset inserted with values: Id: '$assetID' Author: '$author', Title: '$title', Description: '$description', Type: '$type', AssetURL: '$assetURL'", $author);
	}
	
	LoadAssets();
}

if(IsAdmin()){
    $assetID = $_POST["asset_id"];
    $author = $_POST["author"];
    $title = $_POST["title"];
    $description = $_POST["description"];
    $type = $_POST["type"];
    
    AddAsset($assetID, $author, $title, $description, $type);
}
$page = "assets";

?>