<?php

define("OVERRIDE_MIGRATION", "MIGRATION");
define("OVERRIDE_LEGACY_NUM", "-2");
define("OVERRIDE_LEGACY", "LEGACY");
define("OVERRIDE_AUTOMATIC_NUM", "-1");
define("OVERRIDE_AUTOMATIC", "AUTOMATIC");
define("OVERRIDE_AUTOMATIC_PRUNING", "AUTOMATIC PRUNING");
define("OVERRIDE_UNUSED", "-3");

define("AUTH_SHA256", 1);
define("AUTH_BCRYPT", 2);

//Generates a password salt
function GenerateSalt(){
	AddActionLog("GenerateSalt");
	return uniqid(mt_rand(), true);
}

//Hashes the given password and salt the number of iterations. Also uses the
//whole-site salt (called pepper), as defined in config.
function HashPassword($password, $salt, $iterations, &$configData){
	AddActionLog("HashPassword");
	StartTimer("HashPassword");

	$pepper = isset($configData->ConfigModels[CONFIG_PEPPER]->Value) ? $configData->ConfigModels[CONFIG_PEPPER]->Value : "";
	$pswrd = $pepper.$password.$salt;

	//Check that we have sufficient iterations for password generation.
	if(!ValidateHashingIterationNumber($iterations, $configData)){
		StopTimer("HashPassword");
		return;
	}

	for($i = 0; $i < $iterations; $i++){
		$pswrd = hash("sha256", $pswrd);
	}

	StopTimer("HashPassword");
	return $pswrd;
}

//Checks whether the current user, identified by the sessionID in their cookies, is
//logged in. This function only actually performs the check the first time it is called.
//after then it caches the result in the global $loggedInUser variable and simply
//returns that. This is to prevent re-hashing the provided sessionID multiple times.
//To force it to re-check, set the global variable $loginChecked to false.
//Returns either the logged in user's username or FALSE if not logged in.
function IsLoggedIn(&$configData, &$userData){
	global $loginChecked, $loggedInUser, $ip, $userAgent, $sessionDbInterface, $userDbInterface, $_COOKIE;
	AddActionLog("IsLoggedIn");
	StartTimer("IsLoggedIn");

	if($loginChecked){
		StopTimer("IsLoggedIn");
		return $loggedInUser;
	}

	$loggedInUser = Array();

	if(!isset($_COOKIE[COOKIE_SESSION_ID])){
		//No session cookie, therefore not logged in
		$loggedInUser = false;
		$loginChecked = true;
		StopTimer("IsLoggedIn");
		return false;
	}

	$sessionID = "".$_COOKIE[COOKIE_SESSION_ID];
	$pepper = isset($configData->ConfigModels[CONFIG_PEPPER]) ? $configData->ConfigModels[CONFIG_PEPPER]->Value : "BetterThanNothing";
	$sessionIdHash = HashPassword($sessionID, $pepper, $configData->ConfigModels[CONFIG_SESSION_PASSWORD_ITERATIONS]->Value, $configData);

	$data = $sessionDbInterface->SelectSingleSession($sessionIdHash);

	if($session = mysqli_fetch_array($data)){
		//Session ID does in fact exist
		$userId = $session["session_user_id"];

		$loggedInUser = $userData->UserModels[$userId];
        $loginChecked = true;

		$data = $sessionDbInterface->UpdateLastUsedTime($sessionIdHash);

		$data = $userDbInterface->UpdateLastUsedIpAndUserAgent($userId, $ip, $userAgent);

		StopTimer("IsLoggedIn");
		return $loggedInUser;
	} else {
		//Session ID does not exist
		$loggedInUser = false;
		$loginChecked = true;
		StopTimer("IsLoggedIn");
		return false;
	}

	StopTimer("IsLoggedIn");
}

//Returns TRUE or FALSE depending on whether the specified user is an admin.
function IsAdmin(&$user){
	AddActionLog("IsAdmin");
	StartTimer("IsAdmin");

	if($user === false){
		StopTimer("IsAdmin");
		return false;
	}

	if($user->Admin != 0){
		StopTimer("IsAdmin");
		return true;
	}else{
		StopTimer("IsAdmin");
		return false;
	}

	StopTimer("IsAdmin");
}

//Returns TRUE or FALSE depending on whether the specified user has the specified permission level
function UserHasPermissionLevel(&$user, $permissionLevel){
	AddActionLog("UserHasPermissionLevel");
	StartTimer("UserHasPermissionLevel");

	if($user === false){
		StopTimer("UserHasPermissionLevel");
		return false;
	}

	if($user->Admin >= $permissionLevel){
		StopTimer("UserHasPermissionLevel");
		return true;
	}else{
		StopTimer("UserHasPermissionLevel");
		return false;
	}

	StopTimer("UserHasPermissionLevel");
}

function ValidatePassword($password, &$configData){
	AddActionLog("ValidatePassword");

	//Check password length
	if(strlen($password) < $configData->ConfigModels[CONFIG_MINIMUM_PASSWORD_LENGTH]->Value){
		return false;
	}
	if(strlen($password) > $configData->ConfigModels[CONFIG_MAXIMUM_PASSWORD_LENGTH]->Value){
		return false;
	}

	return true;
}

function GenerateUserHashIterations(&$configData){
	AddActionLog("GenerateUserHashIterations");

	$minimumHashIterations = $configData->ConfigModels[CONFIG_MINIMUM_PASSWORD_HASH_ITERATIONS]->Value;
	$maximumHashIterations = $configData->ConfigModels[CONFIG_MAXIMUM_PASSWORD_HASH_ITERATIONS]->Value;

	return intval(rand($minimumHashIterations, $maximumHashIterations));
}

function ValidateHashingIterationNumber($iterations, &$configData){
	AddActionLog("ValidateHashingIterationNumber");

	if($iterations < $configData->ConfigModels[CONFIG_MINIMUM_PASSWORD_HASH_ITERATIONS]->Value){
		return false;
	}
	if($iterations > $configData->ConfigModels[CONFIG_MAXIMUM_PASSWORD_HASH_ITERATIONS]->Value){
		return false;
	}

	return true;
}

function ValidateUsername($username, &$configData){
	AddActionLog("ValidateUsername");

	if(strlen($username) < $configData->ConfigModels[CONFIG_MINIMUM_USERNAME_LENGTH]->Value){
		return false;
	}
	if(strlen($username) > $configData->ConfigModels[CONFIG_MAXIMUM_USERNAME_LENGTH]->Value){
		return false;
	}

	return true;
}

function RedirectToHttpsIfRequired($configData){
	AddActionLog("RedirectToHttpsIfRequired");
	StartTimer("RedirectToHttpsIfRequired");

    if($configData->ConfigModels[CONFIG_REDIRECT_TO_HTTPS]->Value){
        if(!isset($_SERVER['HTTPS'])){
        	//Redirect to https
            $url = "https://". $_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"];
            header("HTTP/1.1 301 Moved Permanently");
            header("Location: $url");
            die();
        }
    }
	StopTimer("RedirectToHttpsIfRequired");
}
function VerifyPassword($user, $password) {
	global $configData;
	switch ($user->AuthVersion) {
		case AUTH_SHA256:
			$correctPasswordHash = $user->PasswordHash;
			$userSalt = $user->Salt;
			$passwordIterations = intval($user->PasswordIterations);
			$passwordHash = HashPassword($password, $userSalt, $passwordIterations, $configData);
			if($correctPasswordHash != $passwordHash)
				return "INCORRECT_PASSWORD";
			UpdateUserPassword($user->Id, $password);
			break;
		case AUTH_BCRYPT:
			if(!password_verify($password, $user->PasswordHash))
				return "INCORRECT_PASSWORD";
			break;
		default:
			return "INVALID_AUTH_VERSION";
	}
	return "SUCCESS";
}

function UpdateUserPassword($userId, $plainTextPassword) {
	global $loggedInUser, $userData, $userDbInterface;
	
	// bcrypt generates new salt, number of iterations and hashed password and stores them by itself.
	$newPasswordHash = password_hash($plainTextPassword, PASSWORD_BCRYPT);

	if (isset($userData->UserModels[$userId])) {
		// update a loaded user's information
		$userData->UserModels[$userId]->Salt = OVERRIDE_UNUSED; // doesn't matter with bcrypt
		$userData->UserModels[$userId]->PasswordIterations = OVERRIDE_UNUSED; // doesn't matter with bcrypt
	
		$userData->UserModels[$userId]->PasswordHash = $newPasswordHash; // only important field with bcrypt
		$userData->UserModels[$userId]->AuthVersion = AUTH_BCRYPT; // tell site we're using AUTH_BCRYPT now.
	}
	$userDbInterface->UpdatePassword($userId, OVERRIDE_UNUSED, $newPasswordHash, OVERRIDE_UNUSED, AUTH_BCRYPT); 
	// Salt and password iterations columns set to empty values. Auth version: AUTH_BCRYPT.
}
?>
