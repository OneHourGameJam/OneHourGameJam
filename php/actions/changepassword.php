<?php

//Changes the logged in user's password if the old one matches.
function ChangePassword($oldPassword, $newPassword1, $newPassword2){
	global $users, $loggedInUser, $dbConn, $actionResult, $config;

	//Authorize user (is admin)
	if($loggedInUser === false){
		$actionResult = "NOT_LOGGED_IN";
		return;
	}

	$newPassword1 = trim($newPassword1);
	$newPassword2 = trim($newPassword2);
	if($newPassword1 != $newPassword2){
		$actionResult = "PASSWORDS_DONT_MATCH";
		return;
	}
	$password = $newPassword1;

	if(!ValidatePassword($password, $config)){
		$actionResult = "INVALID_PASSWORD_LENGTH";
		return;
	}

	//Check that the user exists
	if(!isset($users[$loggedInUser["username"]])){
		$actionResult = "USER_DOES_NOT_EXIST";
		return;
	}

	$user = $users[$loggedInUser["username"]];
	$correctPasswordHash = $user["password_hash"];
	$userSalt = $user["salt"];
	$userPasswordIterations = intval($user["password_iterations"]);
	$passwordHash = HashPassword($oldPassword, $userSalt, $userPasswordIterations, $config);
	if($correctPasswordHash != $passwordHash){
		$actionResult = "INCORRECT_PASSWORD";
		return;
	}

	//Generate new salt, number of iterations and hashed password.
	$newUserSalt = GenerateSalt();
	$newUserPasswordIterations = GenerateUserHashIterations($config);
	$newPasswordHash = HashPassword($password, $newUserSalt, $newUserPasswordIterations, $config);

	$users[$loggedInUser["username"]]["salt"] = $newUserSalt;
	$users[$loggedInUser["username"]]["password_hash"] = $newPasswordHash;
	$users[$loggedInUser["username"]]["password_iterations"] = $newUserPasswordIterations;

	$newUserSaltClean = mysqli_real_escape_string($dbConn, $newUserSalt);
	$newPasswordHashClean = mysqli_real_escape_string($dbConn, $newPasswordHash);
	$newUserPasswordIterationsClean = mysqli_real_escape_string($dbConn, $newUserPasswordIterations);
	$usernameClean = mysqli_real_escape_string($dbConn, $loggedInUser["username"]);

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
	
	$actionResult = "SUCCESS";
}

function PerformAction(&$loggedInUser){
	global $_POST;

	if($loggedInUser !== false){
		$passwordold = $_POST["passwordold"];
		$password1 = $_POST["password1"];
		$password2 = $_POST["password2"];

		ChangePassword($passwordold, $password1, $password2);
	}
}

?>