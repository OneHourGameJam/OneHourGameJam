<?php

//Edits an existing user, identified by the username.
//Valid values for isAdmin are 0 (not admin) and 1 (admin)
//Only changes whether the user is an admin, does NOT change the user's username.
function EditUser($userId, $isAdmin){
	global $userData, $loggedInUser, $adminLogData, $userDbInterface;

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
    $adminLogData->AddToAdminLog("USER_EDITED", "User $username updated with values: IsAdmin: $isAdmin", $userId, $loggedInUser->Id, "");

	return "SUCCESS";
}

function PerformAction(&$loggedInUser){
	global $_POST;
	
	if(IsAdmin($loggedInUser) !== false){
		$userId = $_POST["user_id"];
		$isAdmin = (isset($_POST["isadmin"])) ? intval($_POST["isadmin"]) : 0;
		if($isAdmin != 0 && $isAdmin != 1){
			die("invalid isadmin value");
		}

		return EditUser($userId, $isAdmin);
	}
}

?>