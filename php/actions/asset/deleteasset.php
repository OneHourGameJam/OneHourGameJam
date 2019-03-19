<?php

function DeleteAsset($assetID){
	global $loggedInUser, $dbConn, $assets;
	$assetID = trim($assetID);

	//Authorize user
	if(IsAdmin($loggedInUser) === false){
		return "NOT_AUTHORIZED";
	}

	$assetExists = false;
	if(isset($assetID) && $assetID !== null && isset($assets[$assetID])){
		$assetExists = true;
	}

	if(!$assetExists){
		return "ASSET_DOES_NOT_EXIST";
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

	AddToAdminLog("ASSET_SOFT_DELETE", "Asset ".$assetID." (Title: $assetTitle; Author: $assetAuthor) soft deleted", $assetAuthor, $loggedInUser->Username);
	
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