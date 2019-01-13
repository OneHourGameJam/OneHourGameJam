<?php

//Edits an existing user's password, user is identified by the username.
function EditUserPassword($username, $newPassword1, $newPassword2){
	global $users, $dbConn, $actionResult;

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

	//Check password length
	if(strlen($password) < 8){	//MAGIC
		$actionResult = "PASSWORD_TOO_SHORT";
		AddDataWarning("password must be longer than 8 characters", false);
		return;
	}

	//Check password length
	if(strlen($password) > 128){	//MAGIC
		$actionResult = "PASSWORD_TOO_LONG";
		AddDataWarning("password must be shorter than 128 characters", false);
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
	$newUserPasswordIterations = intval(rand(10000, 20000));
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