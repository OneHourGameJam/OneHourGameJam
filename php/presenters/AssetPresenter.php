<?php

class AssetPresenter{
 
	public static function RenderAssets(&$assetData, &$userData){
		AddActionLog("RenderAssets");
		StartTimer("RenderAssets");
		$assetsViewModel = new AssetsViewModel();
		foreach($assetData->AssetModels as $id => $assetModel){
			$asset = AssetPresenter::RenderAsset($assetModel, $userData);
			$assetsViewModel->LIST[] = $asset;
		}
	
		StopTimer("RenderAssets");
		return $assetsViewModel;
	}
	
	public static function RenderAsset(&$asset, &$userData){
		AddActionLog("RenderAsset");
		StartTimer("RenderAsset");
		$type = $asset->Type;
	
		$assetViewModel = new AssetViewModel();
		$assetViewModel->id = $asset->Id;
		$assetViewModel->author_user_id = $asset->AuthorUserId;
		$assetViewModel->title = $asset->Title;
		$assetViewModel->description = $asset->Description;
		$assetViewModel->type = $type;
		$assetViewModel->content = $asset->Content;
	
		$assetViewModel->author_username = $userData->UserModels[$asset->AuthorUserId]->Username;
		$assetViewModel->author_display_name = $userData->UserModels[$asset->AuthorUserId]->DisplayName;
	
		switch($type){
			case "AUDIO":
				$assetViewModel->is_audio = 1;
			break;
			case "IMAGE":
				$assetViewModel->is_image = 1;
			break;
			case "TEXT":
				$assetViewModel->is_text = 1;
			break;
			case "LINK":
				$assetViewModel->is_link = 1;
			break;
			case "FILE":
				$assetViewModel->is_file = 1;
			break;
			default:
				$assetViewModel->is_other = 1;
			break;
		}
	
		StopTimer("RenderAsset");
		return $assetViewModel;
	}

}

?>