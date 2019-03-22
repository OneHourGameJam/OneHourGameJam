<?php

//Changes the logged in user's password if the old one matches.
function ChangePassword($oldPassword, $newPassword1, $newPassword2){
	global $userData, $loggedInUser, $dbConn, $configData;

	//Authorize user (is admin)
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
	if(!isset($userData->UserModels[$loggedInUser->Username])){
		return "USER_DOES_NOT_EXIST";
	}

	$loggedInUserUsername = $loggedInUser->Username;
	$user = $userData->UserModels[$loggedInUserUsername];
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

	$userData->UserModels[$loggedInUserUsername]->Salt = $newUserSalt;
	$userData->UserModels[$loggedInUserUsername]->PasswordHash = $newPasswordHash;
	$userData->UserModels[$loggedInUserUsername]->PasswordIterations = $newUserPasswordIterations;

	$newUserSaltClean = mysqli_real_escape_string($dbConn, $newUserSalt);
	$newPasswordHashClean = mysqli_real_escape_string($dbConn, $newPasswordHash);
	$newUserPasswordIterationsClean = mysqli_real_escape_string($dbConn, $newUserPasswordIterations);
	$usernameClean = mysqli_real_escape_string($dbConn, $loggedInUser->Username);

	$sql = "
		UPDATE user
		SET
		user_password_salt = '$newUserSaltClean',
		user_password_iterations = '$newUserPasswordIterationsClean',
		user_password_hash = '$newPasswordHashClean'
		WHERE user_username = '$usernameClean';
	";
	$data = mysqli_query($dbConn, $sql);
	$sql = "";
	
	return "SUCCESS";
}

function PerformAction(&$loggedInUser){
	global $_POST;

	if($loggedInUser !== false){
		$passwordold = $_POST["passwordold"];
		$password1 = $_POST["password1"];
		$password2 = $_POST["password2"];

		return ChangePassword($passwordold, $password1, $password2);
	}
}

?>