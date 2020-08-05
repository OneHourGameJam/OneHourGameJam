<?php

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

	$pepper = isset($configData->ConfigModels["PEPPER"]->Value) ? $configData->ConfigModels["PEPPER"]->Value : "";
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
	global $loginChecked, $loggedInUser, $ip, $userAgent, $sessionDbInterface, $userDbInterface;
	AddActionLog("IsLoggedIn");
	StartTimer("IsLoggedIn");

	if($loginChecked){
		StopTimer("IsLoggedIn");
		return $loggedInUser;
	}

	$loggedInUser = Array();

	if(!isset($_COOKIE["sessionID"])){
		//No session cookie, therefore not logged in
		$loggedInUser = false;
		$loginChecked = true;
		StopTimer("IsLoggedIn");
		return false;
	}

	$sessionID = "".$_COOKIE["sessionID"];
	$pepper = isset($configData->ConfigModels["PEPPER"]) ? $configData->ConfigModels["PEPPER"]->Value : "BetterThanNothing";
	$sessionIdHash = HashPassword($sessionID, $pepper, $configData->ConfigModels["SESSION_PASSWORD_ITERATIONS"]->Value, $configData);

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

//Returns TRUE or FALSE depending on whether the logged in user is an admin.
//returns FALSE if there is no logged in user.
function IsAdmin($user){
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

function ValidatePassword($password, &$configData){
	AddActionLog("ValidatePassword");

	//Check password length
	if(strlen($password) < $configData->ConfigModels["MINIMUM_PASSWORD_LENGTH"]->Value){
		return false;
	}
	if(strlen($password) > $configData->ConfigModels["MAXIMUM_PASSWORD_LENGTH"]->Value){
		return false;
	}

	return true;
}

function GenerateUserHashIterations(&$configData){
	AddActionLog("GenerateUserHashIterations");

	$minimumHashIterations = $configData->ConfigModels["MINIMUM_PASSWORD_HASH_ITERATIONS"]->Value;
	$maximumHashIterations = $configData->ConfigModels["MAXIMUM_PASSWORD_HASH_ITERATIONS"]->Value;

	return intval(rand($minimumHashIterations, $maximumHashIterations));
}

function ValidateHashingIterationNumber($iterations, &$configData){
	AddActionLog("ValidateHashingIterationNumber");

	if($iterations < $configData->ConfigModels["MINIMUM_PASSWORD_HASH_ITERATIONS"]->Value){
		return false;
	}
	if($iterations > $configData->ConfigModels["MAXIMUM_PASSWORD_HASH_ITERATIONS"]->Value){
		return false;
	}

	return true;
}

function ValidateUsername($username, &$configData){
	AddActionLog("ValidateUsername");

	if(strlen($username) < $configData->ConfigModels["MINIMUM_USERNAME_LENGTH"]->Value){
		return false;
	}
	if(strlen($username) > $configData->ConfigModels["MAXIMUM_USERNAME_LENGTH"]->Value){
		return false;
	}

	return true;
}

function RedirectToHttpsIfRequired($configData){
	AddActionLog("RedirectToHttpsIfRequired");
	StartTimer("RedirectToHttpsIfRequired");

    if($configData->ConfigModels["REDIRECT_TO_HTTPS"]->Value){
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

?>
