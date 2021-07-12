<?php

//Changes the logged in user's password if the old one matches.
function ChangePassword($oldPassword, $newPassword1, $newPassword2){
	global $userData, $loggedInUser, $configData, $userDbInterface;

	//Authorize user (Logged in)
	if($loggedInUser === false){
		return "NOT_LOGGED_IN";
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
	if(!isset($userData->UserModels[$loggedInUser->Id])){
		return "USER_DOES_NOT_EXIST";
	}

	$loggedInUserId = $loggedInUser->Id;
	$user = $userData->UserModels[$loggedInUserId];
	$correctPasswordHash = $user->PasswordHash;
	$userSalt = $user->Salt;
	$userPasswordIterations = intval($user->PasswordIterations);
	$passwordHash = HashPassword($oldPassword, $userSalt, $userPasswordIterations, $configData);
	if($correctPasswordHash != $passwordHash){
		return "INCORRECT_PASSWORD";
	}

	//Generate new salt, number of iterations and hashed password.
	$newUserSalt = GenerateSalt();
	$newUserPasswordIterations = GenerateUserHashIterations($configData);
	$newPasswordHash = HashPassword($password, $newUserSalt, $newUserPasswordIterations, $configData);

	$userData->UserModels[$loggedInUserId]->Salt = $newUserSalt;
	$userData->UserModels[$loggedInUserId]->PasswordHash = $newPasswordHash;
	$userData->UserModels[$loggedInUserId]->PasswordIterations = $newUserPasswordIterations;

	$userDbInterface->UpdatePassword($loggedInUser->Id, $newUserSalt, $newPasswordHash, $newUserPasswordIterations);
	
	return "SUCCESS";
}

function PerformAction(MessageService &$messageService, &$loggedInUser){
	global $_POST;

	if($loggedInUser !== false){
		$passwordold = $_POST[FORM_CHANGEPASSWORD_OLD_PASSWORD];
		$password1 = $_POST[FORM_CHANGEPASSWORD_PASSWORD_1];
		$password2 = $_POST[FORM_CHANGEPASSWORD_PASSWORD_2];

		return ChangePassword($passwordold, $password1, $password2);
	}
}

?>