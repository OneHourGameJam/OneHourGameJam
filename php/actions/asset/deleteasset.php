<?php

function DeleteAsset($assetID){
	global $loggedInUser, $dbConn, $assetData, $adminLogData;
	$assetID = trim($assetID);

	//Authorize user
	if(IsAdmin($loggedInUser) === false){
		return "NOT_AUTHORIZED";
	}

	$assetExists = false;
	if(isset($assetID) && $assetID !== null && isset($assetData->AssetModels[$assetID])){
		$assetExists = true;
	}

	if(!$assetExists){
		return "ASSET_DOES_NOT_EXIST";
	}

	$escapedID = mysqli_real_escape_string($dbConn, $assetID);

	$sql = "
        SELECT asset_id, asset_author_user_id, asset_title
        FROM asset a
        WHERE asset_deleted != 1
          AND asset_id = $escapedID;
    ";
    $data = mysqli_query($dbConn, $sql);
    $sql = "";

    $assetAuthorUserId = "";
    $assetTitle = "";
	if($info = mysqli_fetch_array($data)){
        $assetAuthorUserId = $info["asset_author_user_id"];
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

	$adminLogData->AddToAdminLog("ASSET_SOFT_DELETE", "Asset ".$assetID." (Title: $assetTitle; Author ID: $assetAuthorUserId) soft deleted", $assetAuthorUserId, $loggedInUser->Id, "");
	
	return "SUCCESS";
}


function PerformAction(&$loggedInUser){
	global $_POST;

	if(IsAdmin($loggedInUser) !== false){
		$assetID = $_POST["asset_id"];
		return DeleteAsset($assetID);
	}
}

?>