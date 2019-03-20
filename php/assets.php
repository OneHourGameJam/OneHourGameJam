<?php

class Asset{
	public $Id;
	public $Author;
	public $AuthorDisplayName;
	public $Title;
	public $Description;
	public $Type;
	public $Content;
}

function LoadAssets(){
	global $dbConn;
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

		$asset = new Asset();
		$asset->Id = $id;
		$asset->Author = $author;
		$asset->AuthorDisplayName = $author_display_name;
		$asset->Title = $title;
		$asset->Description = $description;
		$asset->Type = $type;
		$asset->Content = $content;

		$assets[$id] = $asset;
	}

	StopTimer("LoadAssets");
	return $assets;
}

function RenderAssets(&$assets){
	AddActionLog("RenderAssets");
	StartTimer("RenderAssets");
	$render = Array();
	foreach($assets as $id => $assetData){
		$asset = RenderAsset($assetData);
		$render[] = $asset;
	}

	StopTimer("RenderAssets");
	return $render;
}

function RenderAsset(&$asset){
	$type = $asset->Type;

	$render = Array();
	$render["id"] = $asset->Id;
	$render["author"] = $asset->Author;
	$render["author_display_name"] = $asset->AuthorDisplayName;
	$render["title"] = $asset->Title;
	$render["description"] = $asset->Description;
	$render["type"] = $type;
	$render["content"] = $asset->Content;

	switch($type){
		case "AUDIO":
			$render["is_audio"] = 1;
		break;
		case "IMAGE":
			$render["is_image"] = 1;
		break;
		case "TEXT":
			$render["is_text"] = 1;
		break;
		case "LINK":
			$render["is_link"] = 1;
		break;
		case "FILE":
			$render["is_file"] = 1;
		break;
		default:
			$render["is_other"] = 1;
		break;
	}

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