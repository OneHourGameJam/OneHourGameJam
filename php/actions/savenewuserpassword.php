<?php

//Edits an existing user's password, user is identified by the username.
function EditUserPassword($username, $newPassword1, $newPassword2){
	global $users, $dbConn, $actionResult, $config;

	//Authorize user (is admin)
	if(IsAdmin() === false){
		$actionResult = "NOT_AUTHORIZED";
		AddAuthorizationWarning("Only admins can edit entries.", false);
		return;
	}

	$newPassword1 = trim($newPassword1);
	$newPassword2 = trim($newPassword2);
	if($newPassword1 != $newPassword2){
		$actionResult = "PASSWORDS_DONT_MATCH";
		AddDataWarning("passwords don't match", false);
		return;
	}
	$password = $newPassword1;

	if(!ValidatePassword($password, $config)){
		$actionResult = "INVALID_PASSWORD_LENGTH";
		AddDataWarning("password must be between ".$config["MINIMUM_PASSWORD_LENGTH"]["VALUE"]." and ".$config["MAXIMUM_PASSWORD_LENGTH"]["VALUE"]." characters long", false);
		return;
	}

	//Check that the user exists
	if(!isset($users[$username])){
		$actionResult = "USER_DOES_NOT_EXIST";
		AddDataWarning("User does not exist", false);
		return;
	}

	//Generate new salt, number of iterations and hashed password.
	$newUserSalt = GenerateSalt();
	$newUserPasswordIterations = GenerateUserHashIterations($config);
	$newPasswordHash = HashPassword($password, $newUserSalt, $newUserPasswordIterations);

	$users[$loggedInUser["username"]]["salt"] = $newUserSalt;
	$users[$loggedInUser["username"]]["password_hash"] = $newPasswordHash;
	$users[$loggedInUser["username"]]["password_iterations"] = $newUserPasswordIterations;

	$newUserSaltClean = mysqli_real_escape_string($dbConn, $newUserSalt);
	$newPasswordHashClean = mysqli_real_escape_string($dbConn, $newPasswordHash);
	$newUserPasswordIterationsClean = mysqli_real_escape_string($dbConn, $newUserPasswordIterations);
	$usernameClean = mysqli_real_escape_string($dbConn, $username);

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

    AddToAdminLog("USER_PASSWORD_RESET", "Password reset for user $username", $username);

	$actionResult = "SUCCESS";
	LoadUsers();
	$loggedInUser = IsLoggedIn(TRUE);
}

if(IsAdmin()){
    $username = $_POST["username"];
    $password1 = $_POST["password1"];
    $password2 = $_POST["password2"];

    EditUserPassword($username, $password1, $password2);
}
$page = "editusers";

?>