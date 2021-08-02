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
define("AUTH_VERSION", 1);
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

	$salt = GenerateSalt();
	$passwordIterations = GenerateUserHashIterations($configData);
	$passwordHash = HashPassword($password, $salt, $passwordIterations, $configData);
	$isAdmin = (count($userData->UserModels) == 0) ? 1 : 0;

	$userDbInterface->Insert($username, $ip, $userAgent, $salt, $passwordHash, AUTH_VERSION, $passwordIterations, $isAdmin);
	
	$userData = new UserData($userDbInterface, $sessionDbInterface, $configData);
	return LogInUser($username, $password);
}

//Logs in the user with the provided credentials.
//Sets the user's session cookie.
//Should not be called directly, call through TryLogin(...)
function LogInUser($username, $password){
	global $configData, $userData, $sessionDbInterface, $_COOKIE, $userDbInterface;

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

	switch ($user->AuthVersion) {
		case 1:
			$correctPasswordHash = $user->PasswordHash;
			$userSalt = $user->Salt;
			$passwordIterations = intval($user->PasswordIterations);
			$passwordHash = HashPassword($password, $userSalt, $passwordIterations, $configData);
			if($correctPasswordHash == $passwordHash) {
				//User password is correct!
				//Migrate password to v2
				$userDbInterface->UpdateUserAuthToV2($userId, password_hash($password, PASSWORD_BCRYPT));
				SetUserVariables($userId);
			} else {
				return "INCORRECT_PASSWORD";
			}
			break;
		case 2:
			if(password_verify($password, $user->PasswordHash)) {
				//User password is correct!
				SetUserVariables($userId);
			} else {
				return "INCORRECT_PASSWORD";
			}
			break;
		default:
			return "INVALID_AUTH_VERSION";
	}

	return "SUCCESS";
}

function SetUserVariables($userId) {
	global $configData, $userData, $sessionDbInterface, $_COOKIE;
	$sessionID = "".GenerateSalt();
	$pepper = isset($configData->ConfigModels[CONFIG_PEPPER]->Value) ? $configData->ConfigModels[CONFIG_PEPPER]->Value : "BetterThanNothing";
	$sessionIdHash = HashPassword($sessionID, $pepper, $configData->ConfigModels[CONFIG_SESSION_PASSWORD_ITERATIONS]->Value, $configData);

	$daysToKeepLoggedIn = $configData->ConfigModels[CONFIG_DAYS_TO_KEEP_LOGGED_IN]->Value;
	setcookie(COOKIE_SESSION_ID, $sessionID, time()+60*60*24*$daysToKeepLoggedIn);
	$_COOKIE[COOKIE_SESSION_ID] = $sessionID;

	$sessionDbInterface->Insert($userId, $sessionIdHash);
}
