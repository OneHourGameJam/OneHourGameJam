<?php

function LoadAssets(){
	global $dictionary, $dbConn;

	AddActionLog("LoadAssets");
	StartTimer("LoadAssets");
	$assets = Array();
	
	$sql = "
		SELECT a.asset_id, a.asset_author, a.asset_title, a.asset_description, a.asset_type, a.asset_content, u.user_display_name
		FROM asset a, user u
		WHERE asset_deleted != 1
          AND a.asset_author = u.user_username
	";
	$data = mysqli_query($dbConn, $sql);
	$sql = "";
	
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

	StopTimer("LoadAssets");
	return $assets;
}

function RenderAssets($assets){
	AddActionLog("RenderAssets");
	StartTimer("RenderAssets");
	$render = Array();
	foreach($assets as $id => $asset){
		$render[] = $asset;
	}

	StopTimer("RenderAssets");
	return $render;
}

function GetAssetsOfUserFormatted($author){
	global $dbConn;
	
	AddActionLog("GetAssetsOfUserFormatted");
	StartTimer("GetAssetsOfUserFormatted");
	$escapedAuthor = mysqli_real_escape_string($dbConn, $author);
	$sql = "
		SELECT *
		FROM asset
		WHERE asset_author = '$escapedAuthor';
	";
	$data = mysqli_query($dbConn, $sql);
	$sql = "";
	
	StopTimer("GetAssetsOfUserFormatted");
	return ArrayToHTML(MySQLDataToArray($data));
}

?>