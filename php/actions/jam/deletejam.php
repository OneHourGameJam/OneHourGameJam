<?php

//Returns true / false based on whether or not the specified jam exists (and has not been deleted)
function JamExists($jamId){
	global $jamDbInterface;

	//Validate values
	$jamId = intval($jamId);
	if($jamId <= 0){
		return FALSE;
	}
	$data = $jamDbInterface->SelectIfJamExists($jamId);

	if(mysqli_fetch_array($data)){
		return true;
	}else{
		return false;
	}
}

//Deletes an existing jam, identified by the jam number.
function DeleteJam(MessageService &$messageService, $jamId){
	global $jamData, $loggedInUser, $jamDbInterface, $userData;

	//Authorize user (is admin)
	if(IsAdmin($loggedInUser) === false){
		return "NOT_AUTHORIZED";
	}

	if(!CanDeleteJam($jamId)){
		return "CANNOT_DELETE_JAM";
	}

	//Validate values
	$jamId = intval($jamId);
	if($jamId <= 0){
		return "INVALID_JAM_ID";
	}

	if(count($jamData->JamModels) == 0){
		return "NO_JAMS_EXIST";
	}

	$jamDbInterface->SoftDelete($jamId);

	$messageService->SendMessage(LogMessage::UserLogMessage(
		"JAM_SOFT_DELETED", 
		"Jam $jamId soft deleted", 
		$loggedInUser->Id)
	);
	$userData->LogAdminAction($loggedInUser->Id);
	
	return "SUCCESS";
}

//Returns true / false based on whether or not the specified jam can be deleted
function CanDeleteJam($jamId){
	global $loggedInUser, $gameDbInterface;

	//Authorize user (is admin)
	if(IsAdmin($loggedInUser) === false){
		return FALSE;
	}

	//Validate values
	$jamId = intval($jamId);
	if($jamId <= 0){
		return FALSE;
	}

	if(!JamExists($jamId)){
		return FALSE;
	}

	$data = $gameDbInterface->SelectEntriesInJam($jamId, $loggedInUser->Id);

	if(mysqli_fetch_array($data)){
		return false;
	}else{
		return true;
	}
}

function PerformAction(MessageService &$messageService, &$loggedInUser){
	global $_POST;

	if(IsAdmin($loggedInUser) !== false){
		$jamId = (isset($_POST[FORM_DELETEJAM_JAM_ID])) ? $_POST[FORM_DELETEJAM_JAM_ID] : "";
		if($jamId != ""){
			return DeleteJam($messageService, intval($jamId));
		}
	}
}

?>