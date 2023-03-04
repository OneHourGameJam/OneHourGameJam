<?php

//Edits an existing user, identified by the username.
//Valid values for isAdmin are 0 (not admin) and 1 (admin)
//Only changes whether the user is an admin, does NOT change the user's username.
function EditUser(MessageService &$messageService, $userId, $displayName, $twitterHandle, $twitchUsername, $emailAddress, $bio, $permissionLevel, $allowlistPermissionValue, $denylistPermissionValue){
	global $userData, $configData, $loggedInUser, $userDbInterface;
	
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

    //Validate values
    if(!$displayName || strlen($displayName) < $configData->ConfigModels[CONFIG_MINIMUM_DISPLAY_NAME_LENGTH]->Value || strlen($displayName) > $configData->ConfigModels[CONFIG_MAXIMUM_DISPLAY_NAME_LENGTH]->Value){
        return "INVALID_DISPLAY_NAME";
    }

    //Validate email address
    if($emailAddress != "" && !filter_var($emailAddress, FILTER_VALIDATE_EMAIL)) {
        return "INVALID_EMAIL";
    }

    if($userData->UserModels[$userId]->Admin != $permissionLevel) {
        //Updating user's permission level

        if (!UserHasPermissionLevel($loggedInUser, $userData->UserModels[$userId]->Admin)) {
            return "INSUFFICIENT_PERMISSIONS_OUTRANK";
        }

        if (!UserHasPermissionLevel($loggedInUser, $permissionLevel)) {
            return "INSUFFICIENT_PERMISSIONS_BEYOND_OWN";
        }

        $userDbInterface->UpdateUserPermissionLevel($userId, $permissionLevel);
    }

    //Keep preferences the same
    $preferences = $userData->UserModels[$userId]->UserPreferences;

    $userDbInterface->Update($userId, $displayName, $twitterHandle, $twitchUsername, $emailAddress, CleanHtml($bio), $preferences);
	$userDbInterface->UpdateUserPermissions($userId, $allowlistPermissionValue, $denylistPermissionValue);
	
	$username = $userData->UserModels[$userId]->Username;

	$messageService->SendMessage(LogMessage::UserLogMessage(
		"USER_EDITED", 
		"User $userId ($username) updated with values: PermissionLevel: $permissionLevel; DisplayName: $displayName, TwitterHandle: $twitterHandle, TwitchUsername: $twitchUsername, EmailAddress: $emailAddress, Bio: $bio, AllowlistPermissions: $allowlistPermissionValue; DenylistPermissions: $denylistPermissionValue",
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
        $displayName = (isset($_POST[FORM_EDITUSER_DISPLAY_NAME])) ? $_POST[FORM_EDITUSER_DISPLAY_NAME] : "";
        $twitterHandle = (isset($_POST[FORM_EDITUSER_TWITTER_HANDLE])) ? $_POST[FORM_EDITUSER_TWITTER_HANDLE] : "";
        $twitchUsername = (isset($_POST[FORM_EDITUSER_TWITCH_USERNAME])) ? $_POST[FORM_EDITUSER_TWITCH_USERNAME] : "";
        $emailAddress = (isset($_POST[FORM_EDITUSER_EMAIL_ADDRESS])) ? $_POST[FORM_EDITUSER_EMAIL_ADDRESS] : "";
        $bio = (isset($_POST[FORM_EDITUSER_BIO])) ? $_POST[FORM_EDITUSER_BIO] : "";
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

		return EditUser($messageService, $userId, $displayName, $twitterHandle, $twitchUsername, $emailAddress, $bio, $permissionLevel, $allowlistPermissionValue, $denylistPermissionValue);
	}
}

?>