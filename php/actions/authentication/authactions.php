<?php

//Function called when the login form is sent. Either logs in or registers the
//user, depending on whether the username exists.
function TryLogin($username, $password, $register){
	global $userData, $configData;

	$username = str_replace(" ", "_", strtolower(trim($username)));
	$password = trim($password);

	if(!ValidateUsername($username, $configData)){
		return "INVALID_USERNAME_LENGTH";
	}

	if(!ValidatePassword($password, $configData)){
		return "INVALID_PASSWORD_LENGTH";
	}

	$userId = -1;
	if(isset($userData->UsernameToId[$username])){
		$userId = $userData->UsernameToId[$username];
	}

	if(isset($userData->UserModels[$userId])){
	    if($register){
	        return "USERNAME_ALREADY_REGISTERED";
        }

		//User is registered already, log them in
		return LogInUser($username, $password);
	}else if($register){
		//User not yet registered, register now.
		return RegisterUser($username, $password);
    }else{
        // User not yet registered but tried to login 
        return "USER_DOES_NOT_EXIST";
    }
}
//Registers the given user. Funciton should be called through TryLogin(...).
//Calls LogInUser(...) after registering the user to also log them in.
function RegisterUser($username, $password){
	global $userData, $ip, $userAgent, $configData, $userDbInterface, $sessionDbInterface;

	$username = str_replace(" ", "_", strtolower(trim($username)));
	$password = trim($password);

	if(!ValidateUsername($username, $configData)){
		return "INVALID_USERNAME_LENGTH";
	}

	if(!ValidatePassword($password, $configData)){
		return "INVALID_PASSWORD_LENGTH";
	}

	if(isset($userData->UsernameToId[$username])){
		return "USERNAME_ALREADY_REGISTERED";
	}

	$isAdmin = (count($userData->UserModels) == 0) ? 1 : 0;

	// create user without password
	$userDbInterface->Insert($username, $ip, $userAgent, OVERRIDE_UNUSED, OVERRIDE_UNUSED, AUTH_BCRYPT, OVERRIDE_UNUSED, $isAdmin);	

	$userData = new UserData($userDbInterface, $sessionDbInterface, $configData);
	// change the new user's password to the one provided by the user;
	UpdateUserPassword($userData->UsernameToId[$username], $password, true);
	return LogInUser($username, $password);
}

//Logs in the user with the provided credentials.
//Sets the user's session cookie.
//Should not be called directly, call through TryLogin(...)
function LogInUser($username, $password){
	global $configData, $userData, $sessionDbInterface, $_COOKIE;

	$username = str_replace(" ", "_", strtolower(trim($username)));
	$password = trim($password);

	if(!ValidateUsername($username, $configData)){
		return "INVALID_USERNAME_LENGTH";
	}

	if(!ValidatePassword($password, $configData)){
		return "INVALID_PASSWORD_LENGTH";
	}

	$userId = -1;
	if(isset($userData->UsernameToId[$username])){
		$userId = $userData->UsernameToId[$username];
	}

	if(!isset($userData->UserModels[$userId])){
		return "USER_DOES_NOT_EXIST";
	}

	$user = $userData->UserModels[$userId];
	$auth_status = VerifyPassword($user, $password);
	if($auth_status != "SUCCESS")
		return $auth_status;


	// login successful! set user session data.
	$sessionID = "".GenerateSalt();
	$pepper = isset($configData->ConfigModels[CONFIG_PEPPER]->Value) ? $configData->ConfigModels[CONFIG_PEPPER]->Value : "BetterThanNothing";
	$sessionIdHash = HashPassword($sessionID, $pepper, $configData->ConfigModels[CONFIG_SESSION_PASSWORD_ITERATIONS]->Value, $configData);

	$daysToKeepLoggedIn = $configData->ConfigModels[CONFIG_DAYS_TO_KEEP_LOGGED_IN]->Value;
	setcookie(COOKIE_SESSION_ID, $sessionID, time()+60*60*24*$daysToKeepLoggedIn);
	$_COOKIE[COOKIE_SESSION_ID] = $sessionID;

	$sessionDbInterface->Insert($userId, $sessionIdHash);
	return "SUCCESS";
}
