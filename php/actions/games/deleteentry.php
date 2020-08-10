<?php

//Returns true / false based on whether or not the specified entry can be deleted
function CanDeleteEntry($entryId){
	global $loggedInUser, $gameData, $adminLogData;

	//Authorize user (is admin)
	if(IsAdmin($loggedInUser) === false){
		return FALSE;
	}

	//Validate values
	$entryId = intval($entryId);
	if($entryId <= 0){
		return FALSE;
	}

	if(!$gameData->EntryExists($entryId)){
		return FALSE;
	}

	return true;
}

//Deletes an existing entry, identified by the entryID.
function DeleteEntry($entryId){
	global $jamData, $loggedInUser, $adminLogData, $gameData, $userData, $gameDbInterface;

	//Authorize user (is admin)
	if(IsAdmin($loggedInUser) === false){
		return "NOT_AUTHORIZED";
	}

	if(!CanDeleteEntry($entryId)){
		return "CANNOT_DELETE_ENTRY";
	}

	if(count($jamData->JamModels) == 0){
		return "NO_JAMS_EXIST";
	}
	
	$deletedEntryAuthorId = $gameData->GameModels[$entryId]->AuthorUserId;

	$gameDbInterface->SoftDelete($entryId);

    $adminLogData->AddToAdminLog("ENTRY_SOFT_DELETED", "Entry $entryId soft deleted", $deletedEntryAuthorId, $loggedInUser->Id, "");

	return "SUCCESS";
}

function PerformAction(&$loggedInUser){
	global $_POST;

	if(IsAdmin($loggedInUser) !== false){
		$entryId = (isset($_POST[FORM_DELETEENTRY_ENTRY_ID])) ? $_POST[FORM_DELETEENTRY_ENTRY_ID] : "";
		if($entryId != ""){
			return DeleteEntry(intval($entryId));
		}
	}
}

?>