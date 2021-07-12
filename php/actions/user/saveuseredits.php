<?php

//Edits an existing user, identified by the username.
//Valid values for isAdmin are 0 (not admin) and 1 (admin)
//Only changes whether the user is an admin, does NOT change the user's username.
function EditUser(MessageService &$messageService, $userId, $permissionLevel, $allowlistPermissionValue, $denylistPermissionValue){
	global $userData, $loggedInUser, $userDbInterface;
	
	$allowlistPermissionValue = intval($allowlistPermissionValue);
	$denylistPermissionValue = intval($denylistPermissionValue);

	//Authorize user (is admin)
	if(IsAdmin($loggedInUser) === false){
		return "NOT_AUTHORIZED";
	}

	//Validate values
    $permissionLevel = intval($permissionLevel);
	if($permissionLevel < 0){
		return "INVALID_PERMISSION_LEVEL";
	}

	//Check that the user exists
	if(!isset($userData->UserModels[$userId])){
		return "USER_DOES_NOT_EXIST";
	}

    if(!UserHasPermissionLevel($loggedInUser, $userData->UserModels[$userId]->Admin)){
        return "INSUFFICIENT_PERMISSIONS_OUTRANK";
    }

	if(!UserHasPermissionLevel($loggedInUser, $permissionLevel)){
        return "INSUFFICIENT_PERMISSIONS_BEYOND_OWN";
    }

	$userDbInterface->UpdateUserPermissionLevel($userId, $permissionLevel);
	$userDbInterface->UpdateUserPermissions($userId, $allowlistPermissionValue, $denylistPermissionValue);
	
	$username = $userData->UserModels[$userId]->Username;

	$messageService->SendMessage(LogMessage::UserLogMessage(
		"USER_EDITED", 
		"User $username updated with values: PermissionLevel: $permissionLevel; AllowlistPermissions: $allowlistPermissionValue; DenylistPermissions: $denylistPermissionValue",
		$loggedInUser->Id,
		$userId)
	);
	$userData->LogAdminAction($loggedInUser->Id);

	return "SUCCESS";
}

function PerformAction(MessageService &$messageService, &$loggedInUser){
	global $_POST, $userPermissionsSettings;
	
	if(IsAdmin($loggedInUser) !== false){
		$userId = $_POST[FORM_EDITUSER_USER_ID];
		$permissionLevel = (isset($_POST[FORM_EDITUSER_PERMISSION_LEVEL])) ? intval($_POST[FORM_EDITUSER_PERMISSION_LEVEL]) : 0;
		if($permissionLevel < 0){
			die("invalid isadmin value");
		}

		$allowlistPermissionValue = 0;
		$denylistPermissionValue = 0;
		foreach($userPermissionsSettings as $i => $permissionSetting){
			$permissionFlag = pow(2, $permissionSetting["BIT_FLAG_EXPONENT"]);
			$permissionKey = $permissionSetting["PERMISSION_KEY"];

			if(isset($_POST["allowlist_".$permissionKey])){
				if($_POST["allowlist_".$permissionKey] == "1"){
					$allowlistPermissionValue = $allowlistPermissionValue | $permissionFlag;
				}
			}

			if(isset($_POST["denylist_".$permissionKey])){
				if($_POST["denylist_".$permissionKey] == "1"){
					$denylistPermissionValue = $denylistPermissionValue | $permissionFlag;
				}
			}
		}

		return EditUser($messageService, $userId, $permissionLevel, $allowlistPermissionValue, $denylistPermissionValue);
	}
}

?>