<?php

//Returns true / false based on whether or not the specified jam exists (and has not been deleted)
function JamExists($jamID){
	global $dbConn;

	//Validate values
	$jamID = intval($jamID);
	if($jamID <= 0){
		return FALSE;
	}

	$escapedJamID = mysqli_real_escape_string($dbConn, "$jamID");

	$sql = "
		SELECT 1
		FROM jam
		WHERE jam_id = $escapedJamID
		AND jam_deleted = 0;
		";
	$data = mysqli_query($dbConn, $sql);
	$sql = "";

	if(mysqli_fetch_array($data)){
		return true;
	}else{
		return false;
	}
}

//Deletes an existing jam, identified by the jam number.
function DeleteJam($jamID){
	global $jams, $dbConn, $loggedInUser;

	//Authorize user (is admin)
	if(IsAdmin($loggedInUser) === false){
		return "NOT_AUTHORIZED";
	}

	if(!CanDeleteJam($jamID)){
		return "CANNOT_DELETE_JAM";
	}

	//Validate values
	$jamID = intval($jamID);
	if($jamID <= 0){
		return "INVALID_JAM_ID";
	}

	if(count($jams) == 0){
		return "NO_JAMS_EXIST";
	}

	$escapedJamID = mysqli_real_escape_string($dbConn, "$jamID");

	$sql = "UPDATE jam SET jam_deleted = 1 WHERE jam_id = $escapedJamID";
	$data = mysqli_query($dbConn, $sql);
	$sql = "";

	AddToAdminLog("JAM_SOFT_DELETED", "Jam $jamID soft deleted", "", $loggedInUser->Username);
	
	return "SUCCESS";
}

//Returns true / false based on whether or not the specified jam can be deleted
function CanDeleteJam($jamID){
	global $dbConn, $loggedInUser;

	//Authorize user (is admin)
	if(IsAdmin($loggedInUser) === false){
		return FALSE;
	}

	//Validate values
	$jamID = intval($jamID);
	if($jamID <= 0){
		return FALSE;
	}

	if(!JamExists($jamID)){
		return FALSE;
	}

	$escapedJamID = mysqli_real_escape_string($dbConn, "$jamID");

	$sql = "
		SELECT 1
		FROM entry
		WHERE entry_jam_id = $escapedJamID
		AND entry_deleted = 0;
		";
	$data = mysqli_query($dbConn, $sql);
	$sql = "";

	if(mysqli_fetch_array($data)){
		return false;
	}else{
		return true;
	}
}

function PerformAction(&$loggedInUser){
	global $_POST;

	if(IsAdmin($loggedInUser) !== false){
		$jamID = (isset($_POST["jamID"])) ? $_POST["jamID"] : "";
		if($jamID != ""){
			return DeleteJam(intval($jamID));
		}
	}
}

?>