<?php

function DeleteAsset($assetId){
	global $loggedInUser, $assetData, $adminLogData, $assetDbInterface;
	$assetId = trim($assetId);

	//Authorize user
	if(IsAdmin($loggedInUser) === false){
		return "NOT_AUTHORIZED";
	}

	$assetExists = false;
	if(isset($assetId) && $assetId !== null && isset($assetData->AssetModels[$assetId])){
		$assetExists = true;
	}

	if(!$assetExists){
		return "ASSET_DOES_NOT_EXIST";
	}

    $data = $assetDbInterface->SelectSingleAsset($assetId);

    $assetAuthorUserId = "";
    $assetTitle = "";
	if($info = mysqli_fetch_array($data)){
        $assetAuthorUserId = $info["asset_author_user_id"];
        $assetTitle = $info["asset_title"];
    }

	$assetDbInterface->SoftDelete($assetId);

	$adminLogData->AddToAdminLog("ASSET_SOFT_DELETE", "Asset ".$assetId." (Title: $assetTitle; Author ID: $assetAuthorUserId) soft deleted", $assetAuthorUserId, $loggedInUser->Id, "");
	
	return "SUCCESS";
}


function PerformAction(&$loggedInUser){
	global $_POST;

	if(IsAdmin($loggedInUser) !== false){
		$assetId = $_POST[FORM_DELETEASSET_ASSET_ID];
		return DeleteAsset($assetId);
	}
}

?>