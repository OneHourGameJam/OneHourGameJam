<?php

//Changes the logged in user's password if the old one matches.
function ChangePassword($oldPassword, $newPassword1, $newPassword2){
	global $users, $loggedInUser, $dbConn;

	$loggedInUser = IsLoggedIn();

	//Authorize user (is admin)
	if($loggedInUser === false){
		AddAuthorizationWarning("Not logged in.", false);
		return;
	}

	$newPassword1 = trim($newPassword1);
	$newPassword2 = trim($newPassword2);
	if($newPassword1 != $newPassword2){
		AddDataWarning("passwords don't match", false);
		return;
	}
	$password = $newPassword1;

	//Check password length
	if(strlen($password) < 8){
		AddDataWarning("password must be longer than 8 characters", false);
		return;
	}

	//Check that the user exists
	if(!isset($users[$loggedInUser["username"]])){
		AddDataWarning("User does not exist", false);
		return;
	}

	$user = $users[$loggedInUser["username"]];
	$correctPasswordHash = $user["password_hash"];
	$userSalt = $user["salt"];
	$userPasswordIterations = intval($user["password_iterations"]);
	$passwordHash = HashPassword($oldPassword, $userSalt, $userPasswordIterations);
	if($correctPasswordHash != $passwordHash){
		AddDataWarning("The entered password is incorrect.", false);
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

	LoadUsers();
	$loggedInUser = IsLoggedIn(TRUE);
}

if(IsLoggedIn()){
    $passwordold = $_POST["passwordold"];
    $password1 = $_POST["password1"];
    $password2 = $_POST["password2"];

    ChangePassword($passwordold, $password1, $password2);
}
$page = "usersettings";

?>