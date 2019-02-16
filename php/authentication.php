<?php

//Generates a password salt
function GenerateSalt(){
	AddActionLog("GenerateSalt");
	return uniqid(mt_rand(), true);
}

//Hashes the given password and salt the number of iterations. Also uses the
//whole-site salt (called pepper), as defined in config.
function HashPassword($password, $salt, $iterations, &$config){
	AddActionLog("HashPassword");
	StartTimer("HashPassword");
	$pepper = isset($config["PEPPER"]["VALUE"]) ? $config["PEPPER"]["VALUE"] : "";
	$pswrd = $pepper.$password.$salt;

	//Check that we have sufficient iterations for password generation.
	
	if(!ValidateHashingIterationNumber($iterations, $config)){
		AddInternalDataError("Insufficient/Too many iterations for password generation.", false);
		StopTimer("HashPassword");
		return;
	}

	for($i = 0; $i < $iterations; $i++){
		$pswrd = hash("sha256", $pswrd);
	}
	StopTimer("HashPassword");
	return $pswrd;
}

//Returns the username of the user associated with the provided user id
function GetUsernameForUserId($userID, &$users){
	AddActionLog("GetUsernameForUserId");
	StartTimer("GetUsernameForUserId");

	foreach($users as $i => $user){
		if($user["id"] == $userID){
			StopTimer("GetUsernameForUserId");
			return $user["username"];
		}
	}
	StopTimer("GetUsernameForUserId");
}

//Checks whether the current user, identified by the sessionID in their cookies, is
//logged in. This function only actually performs the check the first time it is called.
//after then it caches the result in the global $loggedInUser variable and simply
//returns that. This is to prevent re-hashing the provided sessionID multiple times.
//To force it to re-check, set the global variable $loginChecked to false.
//Returns either the logged in user's username or FALSE if not logged in.
function IsLoggedIn(&$config, &$users){
	global $loginChecked, $loggedInUser, $dbConn, $ip, $userAgent;
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
	$pepper = isset($config["PEPPER"]) ? $config["PEPPER"]["VALUE"] : "BetterThanNothing";
	$sessionIDHash = HashPassword($sessionID, $pepper, $config["SESSION_PASSWORD_ITERATIONS"]["VALUE"], $config);

    $cleanSessionIdHash = mysqli_real_escape_string($dbConn, $sessionIDHash);

	$sql = "
		SELECT session_id, session_user_id
		FROM session
		WHERE session_id = '$cleanSessionIdHash';
	";
	$data = mysqli_query($dbConn, $sql);
	$sql = "";

	if($session = mysqli_fetch_array($data)){
		//Session ID does in fact exist
		$userID = $session["session_user_id"];
		$username = GetUsernameForUserId($userID, $users);
		$loggedInUser = $users[$username];
        $loginChecked = true;

		$sql = "
			UPDATE session
			SET session_datetime_last_used = Now()
			WHERE session_id = '$cleanSessionIdHash'
		";
		$data = mysqli_query($dbConn, $sql);
        $sql = "";

        $cleanUserId = mysqli_real_escape_string($dbConn, $userID);
        $cleanIp = mysqli_real_escape_string($dbConn, $ip);
        $cleanUserAgent = mysqli_real_escape_string($dbConn, $userAgent);

		$sql = "
			UPDATE user
            SET user_last_login_datetime = Now(),
                user_last_ip = '$cleanIp',
                user_last_user_agent = '$cleanUserAgent'
			WHERE user_id = $cleanUserId
        ";
		$data = mysqli_query($dbConn, $sql);
		$sql = "";

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

	if($user["admin"] != 0){
		StopTimer("IsAdmin");
		return true;
	}else{
		StopTimer("IsAdmin");
		return false;
	}

	StopTimer("IsAdmin");
}

// Fetchs the user bio in the database, and sets it under the "bio" key.
function LoadBio($user) {
	global $dbConn;
	AddActionLog("LoadBio");
	StartTimer("LoadBio");
	if (isset($user)) {
		$clean_username = mysqli_real_escape_string($dbConn, $user["username"]);
		$sql = "SELECT user_bio FROM user WHERE user_username = '$clean_username'";
		$data = mysqli_query($dbConn, $sql);
		$info = mysqli_fetch_array($data);
		$user["bio"] = $info["user_bio"];
	}

	StopTimer("LoadBio");
	return $user;
}

function GetUsersOfUserFormatted($username){
	global $dbConn;

	AddActionLog("GetUsersOfUserFormatted");
	StartTimer("GetUsersOfUserFormatted");
	$escapedUsername = mysqli_real_escape_string($dbConn, $username);
	$sql = "
		SELECT *
		FROM user
		WHERE user_username = '$escapedUsername';
	";
	$data = mysqli_query($dbConn, $sql);
	$sql = "";

	StopTimer("GetUsersOfUserFormatted");
	return ArrayToHTML(MySQLDataToArray($data));
}

function GetSessionsOfUserFormatted($userId){
	global $dbConn;

	AddActionLog("GetSessionsOfUserFormatted");
	StartTimer("GetSessionsOfUserFormatted");
	$escapedID = mysqli_real_escape_string($dbConn, $userId);
	$sql = "
		SELECT *
		FROM session
		WHERE session_user_id = '$escapedID';
	";
	$data = mysqli_query($dbConn, $sql);
	$sql = "";

	StopTimer("GetSessionsOfUserFormatted");
	return ArrayToHTML(MySQLDataToArray($data));
}

function ValidatePassword($password, &$config){
	//Check password length
	if(strlen($password) < $config["MINIMUM_PASSWORD_LENGTH"]["VALUE"]){
		return false;
	}
	if(strlen($password) > $config["MAXIMUM_PASSWORD_LENGTH"]["VALUE"]){
		return false;
	}

	return true;
}

function GenerateUserHashIterations(&$config){
	$minimumHashIterations = $config["MINIMUM_PASSWORD_HASH_ITERATIONS"]["VALUE"];
	$maximumHashIterations = $config["MAXIMUM_PASSWORD_HASH_ITERATIONS"]["VALUE"];

	return intval(rand($minimumHashIterations, $maximumHashIterations));
}

function ValidateHashingIterationNumber($iterations, &$config){
	if($iterations < $config["MINIMUM_PASSWORD_HASH_ITERATIONS"]["VALUE"]){
		return false;
	}
	if($iterations > $config["MAXIMUM_PASSWORD_HASH_ITERATIONS"]["VALUE"]){
		return false;
	}

	return true;
}

function ValidateUsername($username, &$config){
	if(strlen($username) < $config["MINIMUM_USERNAME_LENGTH"]["VALUE"]){
		return false;
	}
	if(strlen($username) > $config["MAXIMUM_USERNAME_LENGTH"]["VALUE"]){
		return false;
	}

	return true;
}

?>
