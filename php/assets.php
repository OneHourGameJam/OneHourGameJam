<?php

function RenderAssets(&$assetData, &$userData){
	AddActionLog("RenderAssets");
	StartTimer("RenderAssets");
	$render = Array();
	foreach($assetData->AssetModels as $id => $assetModel){
		$asset = RenderAsset($assetModel, $userData);
		$render[] = $asset;
	}

	StopTimer("RenderAssets");
	return $render;
}

function RenderAsset(&$asset, &$userData){
	AddActionLog("RenderAsset");
	StartTimer("RenderAsset");
	$type = $asset->Type;

	$render = Array();
	$render["id"] = $asset->Id;
	$render["author_user_id"] = $asset->AuthorUserId;
	$render["title"] = $asset->Title;
	$render["description"] = $asset->Description;
	$render["type"] = $type;
	$render["content"] = $asset->Content;

	foreach($userData->UserModels as $i => $userModel){
		if($userModel->Id == $asset->AuthorUserId){
			$render["author_username"] = $userModel->Username;
			$render["author_display_name"] = $userModel->DisplayName;
		}
	}

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

?>