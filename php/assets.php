<?php

function RenderAssets(&$assetData){
	AddActionLog("RenderAssets");
	StartTimer("RenderAssets");
	$render = Array();
	foreach($assetData->AssetModels as $id => $assetModel){
		$asset = RenderAsset($assetModel);
		$render[] = $asset;
	}

	StopTimer("RenderAssets");
	return $render;
}

function RenderAsset(&$asset){
	AddActionLog("RenderAsset");
	StartTimer("RenderAsset");
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

	StopTimer("RenderAsset");
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