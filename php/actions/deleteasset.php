<?php

function DeleteAsset($assetID){
	global $loggedInUser, $dbConn, $assets, $actionResult;
	$assetID = trim($assetID);

	//Authorize user
	if(IsAdmin($loggedInUser) === false){
		$actionResult = "NOT_AUTHORIZED";
		AddAdminAuthorizationWarning(false);
		return;
	}

	$assetExists = false;
	if(isset($assetID) && $assetID !== null && isset($assets[$assetID])){
		$assetExists = true;
	}

	if(!$assetExists){
		$actionResult = "ASSET_DOES_NOT_EXIST";
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

	$actionResult = "SUCCESS";
    AddToAdminLog("ASSET_SOFT_DELETE", "Asset ".$assetID." (Title: $assetTitle; Author: $assetAuthor) soft deleted", $assetAuthor, $loggedInUser["username"]);
}

if(IsAdmin($loggedInUser) !== false){
    $assetID = $_POST["asset_id"];
    DeleteAsset($assetID);
}
$page = "assets";

?>