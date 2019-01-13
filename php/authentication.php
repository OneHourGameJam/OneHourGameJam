<?php

//Generates a password salt
function GenerateSalt(){
	AddActionLog("GenerateSalt");
	return uniqid(mt_rand(), true);
}

//Hashes the given password and salt the number of iterations. Also uses the
//whole-site salt (called pepper), as defined in config.
//There is a minimum and maximum number of iterations for security and performance
//reasons, set to 100 < iterations < 100k. We suggest that passwords and session IDs
//are hashed at least 10k times
//TODO: Move min and max iterations to config
function HashPassword($password, $salt, $iterations){
	global $config;
	AddActionLog("HashPassword");
	StartTimer("HashPassword");
	$pepper = isset($config["PEPPER"]["VALUE"]) ? $config["PEPPER"]["VALUE"] : "";
	$pswrd = $pepper.$password.$salt;

	//Check that we have sufficient iterations for password generation.
	if($iterations < 100){
		AddInternalDataError("Insufficient iterations for password generation.", false);
		StopTimer("HashPassword");
		return;
	}else if($iterations > 100000){
		AddInternalDataError("Too many iterations for password generation.", false);
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
function GetUsernameForUserId($userID){
	global $users;
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
//Set $force to TRUE to force reloading (for example if a user setting was changed for the logged in user)
function IsLoggedIn($force = FALSE){
	global $loginChecked, $loggedInUser, $config, $users, $dictionary, $dbConn, $ip, $userAgent;
	AddActionLog("IsLoggedIn");
	StartTimer("IsLoggedIn");

	if($loginChecked && !$force){
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
	$sessionIDHash = HashPassword($sessionID, $pepper, $config["SESSION_PASSWORD_ITERATIONS"]["VALUE"]);

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
		$username = GetUsernameForUserId($userID);
		$loggedInUser = $users[$username];
		$loggedInUser["username"] = $username;
		$dictionary["user"] = $loggedInUser;
		$dictionary["user"]["username"] = $username;
		if($loggedInUser["admin"] != 0){
			$dictionary["user"]["isadmin"] = 1;
		}
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
function IsAdmin(){
	global $adminList;
	AddActionLog("IsAdmin");
	StartTimer("IsAdmin");
	$loggedInUser = IsLoggedIn();
	if($loggedInUser === false){
		StopTimer("IsAdmin");
		return false;
	}

	if($loggedInUser["admin"] != 0){
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


?>
