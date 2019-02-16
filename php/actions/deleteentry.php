<?php

//Returns true / false based on whether or not the specified entry can be deleted
function CanDeleteEntry($entryID){
	global $jams, $dbConn, $loggedInUser;

	//Authorize user (is admin)
	if(IsAdmin($loggedInUser) === false){
		return FALSE;
	}

	//Validate values
	$entryID = intval($entryID);
	if($entryID <= 0){
		return FALSE;
	}

	if(!EntryExists($entryID)){
		return FALSE;
	}

	return true;
}

//Deletes an existing entry, identified by the entryID.
function DeleteEntry($entryID){
	global $jams, $dbConn, $actionResult, $loggedInUser;

	//Authorize user (is admin)
	if(IsAdmin($loggedInUser) === false){
		$actionResult = "NOT_AUTHORIZED";
		return;
	}

	if(!CanDeleteEntry($entryID)){
		$actionResult = "CANNOT_DELETE_ENTRY";
		return;
	}

	//Validate values
	$entryID = intval($entryID);
	if($entryID <= 0){
		$actionResult = "INVALID_JAM_ID";
		return;
	}

	if(count($jams) == 0){
		$actionResult = "NO_JAMS_EXIST";
		return; //No jams exist
	}

	$escapedEntryID = mysqli_real_escape_string($dbConn, "$entryID");

	$sql = "UPDATE entry SET entry_deleted = 1 WHERE entry_id = $escapedEntryID";
	$data = mysqli_query($dbConn, $sql);
    $sql = "";

    AddToAdminLog("ENTRY_SOFT_DELETED", "Entry $entryID soft deleted", "", $loggedInUser["username"]);

	$actionResult = "SUCCESS";
}

function PerformAction(&$loggedInUser){
	global $_POST;

	if(IsAdmin($loggedInUser) !== false){
		$entryID = (isset($_POST["entryID"])) ? $_POST["entryID"] : "";
		if($entryID != ""){
			DeleteEntry(intval($entryID));
		}
	}
}

?>