<?php

//Edits an existing user, identified by the username.
//Valid values for isAdmin are 0 (not admin) and 1 (admin)
//Only changes whether the user is an admin, does NOT change the user's username.
function EditUser(MessageService &$messageService, $userId, $isAdmin){
	global $userData, $loggedInUser, $userDbInterface;

	//Authorize user (is admin)
	if(IsAdmin($loggedInUser) === false){
		return "NOT_AUTHORIZED";
	}

	//Validate values
	if($isAdmin == 0){
		$isAdmin = 0;
	}else if($isAdmin == 1){
		$isAdmin = 1;
	}else{
		return "INVALID_ISADMIN";
	}

	//Check that the user exists
	if(!isset($userData->UserModels[$userId])){
		return "USER_DOES_NOT_EXIST";
	}

	$userDbInterface->UpdateIsAdmin($userId, $isAdmin);
	
	$username = $userData->UserModels[$userId]->Username;

	$messageService->SendMessage(LogMessage::UserLogMessage(
		"USER_EDITED", 
		"User $username updated with values: IsAdmin: $isAdmin", 
		$loggedInUser->Id,
		$userId)
	);

	return "SUCCESS";
}

function PerformAction(MessageService &$messageService, &$loggedInUser){
	global $_POST;
	
	if(IsAdmin($loggedInUser) !== false){
		$userId = $_POST[FORM_EDITUSER_USER_ID];
		$isAdmin = (isset($_POST[FORM_EDITUSER_IS_ADMIN])) ? intval($_POST[FORM_EDITUSER_IS_ADMIN]) : 0;
		if($isAdmin != 0 && $isAdmin != 1){
			die("invalid isadmin value");
		}

		return EditUser($messageService, $userId, $isAdmin);
	}
}

?>