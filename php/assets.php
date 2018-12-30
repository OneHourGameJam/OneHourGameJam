<?php

function LoadAssets(){
	global $dictionary, $assets, $dbConn;
	
	//Clear public lists which get updated by this function
	$dictionary["assets"] = Array();
	$assets = Array();
	
	//Fill list of themes - will return same row multiple times (once for each valid themevote_type)
	$sql = "
		SELECT a.asset_id, a.asset_author, a.asset_title, a.asset_description, a.asset_type, a.asset_content, u.user_display_name
		FROM asset a, user u
		WHERE asset_deleted != 1
          AND a.asset_author = u.user_username
	";
	$data = mysqli_query($dbConn, $sql);
	$sql = "";
	
	//Fill dictionary with non-banned themes
	while($asset = mysqli_fetch_array($data)){
		$id = $asset["asset_id"];
		$author = $asset["asset_author"];
		$author_display_name = $asset["user_display_name"];
		$title = $asset["asset_title"];
		$description = $asset["asset_description"];
		$type = $asset["asset_type"];
		$content = $asset["asset_content"];
		
		$a = Array("id" => $id, "author" => $author, "author_display_name" => $author_display_name, "title" => $title, "description" => $description, "type" => $type, "content" => $content );
		
		switch($type){
			case "AUDIO":
				$a["is_audio"] = 1;
			break;
			case "IMAGE":
				$a["is_image"] = 1;
			break;
			case "TEXT":
				$a["is_text"] = 1;
			break;
			case "LINK":
				$a["is_link"] = 1;
			break;
			case "FILE":
				$a["is_file"] = 1;
			break;
			default:
				$a["is_other"] = 1;
			break;
		}
		
		$assets[$id] = $a;
	}
	
	$dictionary["assets"] = Array();
	foreach($assets as $id => $asset){
		$dictionary["assets"][] = $asset;
	}
}


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


function DeleteAsset($assetID){
	global $loggedInUser, $dbConn, $assets;
	$assetID = trim($assetID);
	
	//Authorize user
	if(!IsAdmin()){
		AddAdminAuthorizationWarning(false);
		return;
	}
	
	$assetExists = false;
	if(isset($assetID) && $assetID !== null && isset($assets[$assetID])){
		$assetExists = true;
	}
	
	if(!$assetExists){
		AddDataWarning("The specified asset does not exist.", false);
		return;
	}
	
	$escapedID = mysqli_real_escape_string($dbConn, $assetID);
    
	$sql = "
        SELECT asset_id, asset_author, asset_title
        FROM asset a
        WHERE asset_deleted != 1
          AND asset_id = $escapedID;
    ";
    $data = mysqli_query($dbConn, $sql);
    $sql = "";
    
    $assetAuthor = "";
    $assetTitle = "";
	if($info = mysqli_fetch_array($data)){
        $assetAuthor = $info["asset_author"];
        $assetTitle = $info["asset_title"];
    }

	$sql = "
		UPDATE asset
		SET
			asset_deleted = 1
		WHERE asset_id = $escapedID;
	";
	$data = mysqli_query($dbConn, $sql);
	$sql = "";
	
	LoadAssets();
        
    AddToAdminLog("ASSET_SOFT_DELETE", "Asset ".$assetID." (Title: $assetTitle; Author: $assetAuthor) soft deleted", $assetAuthor);
}

function GetAssetsOfUserFormatted($author){
	global $dbConn;
	
	$escapedAuthor = mysqli_real_escape_string($dbConn, $author);
	$sql = "
		SELECT *
		FROM asset
		WHERE asset_author = '$escapedAuthor';
	";
	$data = mysqli_query($dbConn, $sql);
	$sql = "";
	
	return ArrayToHTML(MySQLDataToArray($data));
}

?>