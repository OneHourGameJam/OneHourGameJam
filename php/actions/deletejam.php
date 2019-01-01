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
	global $jams, $dbConn;
	
	//Authorize user (is admin)
	if(IsAdmin() === false){
		AddAuthorizationWarning("Only admins can delete jams.", false);
		return;
	}
	
	if(!CanDeleteJam($jamID)){
		AddInternalDataError("This jam cannot be deleted.", false);
		return;
	}
	
	//Validate values
	$jamID = intval($jamID);
	if($jamID <= 0){
		AddDataWarning("invalid jam ID", false);
		return;
	}
	
	if(count($jams) == 0){
		return; //No jams exist
	}
	
	$escapedJamID = mysqli_real_escape_string($dbConn, "$jamID");
	
	$sql = "UPDATE jam SET jam_deleted = 1 WHERE jam_id = $escapedJamID";
	$data = mysqli_query($dbConn, $sql);
	$sql = "";
	
	AddDataSuccess("Jam Deleted");
    
    AddToAdminLog("JAM_SOFT_DELETED", "Jam $jamID soft deleted", "");
}

//Returns true / false based on whether or not the specified jam can be deleted
function CanDeleteJam($jamID){
	global $jams, $dbConn;
	
	//Authorize user (is admin)
	if(IsAdmin() === false){
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

if(IsAdmin()){
    $jamID = (isset($_POST["jamID"])) ? $_POST["jamID"] : "";
    if($jamID != ""){
        DeleteJam(intval($jamID));
        $page = "editcontent";
    }
}

?>