<?php

//Edits an existing user's password, user is identified by the username.
function EditUserPassword($userId, $newPassword1, $newPassword2){
	global $userData, $configData, $loggedInUser, $adminLogData, $userDbInterface;

	//Authorize user (is admin)
	if(IsAdmin($loggedInUser) === false){
		return "NOT_AUTHORIZED";
	}

	$newPassword1 = trim($newPassword1);
	$newPassword2 = trim($newPassword2);
	if($newPassword1 != $newPassword2){
		return "PASSWORDS_DONT_MATCH";
	}
	$password = $newPassword1;

	if(!ValidatePassword($password, $configData)){
		return "INVALID_PASSWORD_LENGTH";
	}

	//Check that the user exists
	if(!isset($userData->UserModels[$userId])){
		return "USER_DOES_NOT_EXIST";
	}

	//Generate new salt, number of iterations and hashed password.
	$newUserSalt = GenerateSalt();
	$newUserPasswordIterations = GenerateUserHashIterations($configData);
	$newPasswordHash = HashPassword($password, $newUserSalt, $newUserPasswordIterations, $configData);

	$userData->UserModels[$loggedInUser->Id]->Salt = $newUserSalt;
	$userData->UserModels[$loggedInUser->Id]->PasswordHash = $newPasswordHash;
	$userData->UserModels[$loggedInUser->Id]->PasswordIterations = $newUserPasswordIterations;

	$userDbInterface->UpdatePassword($userId, $newUserSalt, $newPasswordHash, $newUserPasswordIterations);

	$username = $userData->UserModels[$userId]->Username;
    $adminLogData->AddToAdminLog("USER_PASSWORD_RESET", "Password reset for user $username", $userId, $loggedInUser->Id, "");

	return "SUCCESS";
}

function PerformAction(&$loggedInUser){
	global $_POST;
	
	if(IsAdmin($loggedInUser) !== false){
		$userId = $_POST[FORM_SAVENEWUSERPASSWORD_USER_ID];
		$password1 = $_POST[FORM_SAVENEWUSERPASSWORD_PASSWORD_1];
		$password2 = $_POST[FORM_SAVENEWUSERPASSWORD_PASSWORD_2];

		return EditUserPassword($userId, $password1, $password2);
	}
}

?>