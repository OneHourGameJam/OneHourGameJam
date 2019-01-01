<?php

//Generates a password salt
function GenerateSalt(){
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
	$pepper = isset($config["PEPPER"]) ? $config["PEPPER"] : "";
	$pswrd = $pepper.$password.$salt;
	
	//Check that we have sufficient iterations for password generation.
	if($iterations < 100){
		AddInternalDataError("Insufficient iterations for password generation.", false);
		return;
	}else if($iterations > 100000){
		AddInternalDataError("Too many iterations for password generation.", false);
		return;
	}
	
	for($i = 0; $i < $iterations; $i++){
		$pswrd = hash("sha256", $pswrd);
	}
	return $pswrd;
}

//(Re)Loads the users into the globally accessible $users variable.
function LoadUsers(){
	global $users, $loggedInUser, $dictionary, $dbConn, $userIDLookup;

	$users = Array();

	$sql = "SELECT user_id, user_username, user_display_name, user_twitter, user_email, 
                   user_password_salt, user_password_hash, user_password_iterations, user_role, 
                   DATEDIFF(Now(), user_last_login_datetime) AS days_since_last_login, 
                   DATEDIFF(Now(), log_max_datetime) AS days_since_last_admin_action
            FROM 
                user u LEFT JOIN 
                (
                    SELECT log_admin_username, max(log_datetime) AS log_max_datetime 
                    FROM admin_log 
                    GROUP BY log_admin_username
                ) al ON u.user_username = al.log_admin_username";
	$data = mysqli_query($dbConn, $sql);
    $sql = "";

	while($info = mysqli_fetch_array($data)){
		//Read data about the user
		$currentUser = Array();
		$currentUser["id"] = $info["user_id"];
		$currentUser["username"] = $info["user_username"];
		$currentUser["display_name"] = $info["user_display_name"];
		$currentUser["twitter"] = $info["user_twitter"];
		$currentUser["twitter_text_only"] = str_replace("@", "", $info["user_twitter"]);
		$currentUser["email"] = $info["user_email"];
		$currentUser["salt"] = $info["user_password_salt"];
		$currentUser["password_hash"] = $info["user_password_hash"];
		$currentUser["password_iterations"] = intval($info["user_password_iterations"]);
        $currentUser["admin"] = intval($info["user_role"]);
        
        //This fixes an issue where user_last_login_datetime was not set properly in the database, which results in days_since_last_login being null for users who have not logged in since the fix was applied
        if($info["days_since_last_login"] == null){
            $info["days_since_last_login"] = 1000000;
        }
        
        //For cases where users have never performed an admin action
        if($info["days_since_last_admin_action"] == null){
            $info["days_since_last_admin_action"] = 1000000;
        }

		$currentUser["days_since_last_login"] = intval($info["days_since_last_login"]);
		$currentUser["days_since_last_admin_action"] = intval($info["days_since_last_admin_action"]);

		$users[$currentUser["username"]] = $currentUser;
		$userIDLookup[$currentUser["id"]] = $currentUser["username"];
	}

	ksort($users);
	$dictionary["users"] = $users;
	$dictionary["admins"] = Array();
	$dictionary["registered_users"] = Array();
	foreach($users as $i => $user){
		if($user["admin"] == 1){
			$dictionary["admins"][] = $user;
		}else{
			$dictionary["registered_users"][] = $user;
		}
	}
}

//Checks whether the current user, identified by the sessionID in their cookies, is
//logged in. This function only actually performs the check the first time it is called.
//after then it caches the result in the global $loggedInUser variable and simply
//returns that. This is to prevent re-hashing the provided sessionID multiple times.
//To force it to re-check, set the global variable $loginChecked to false.
//Returns either the logged in user's username or FALSE if not logged in.
//Set $force to TRUE to force reloading (for example if a user setting was changed for the logged in user)
function IsLoggedIn($force = FALSE){
	global $loginChecked, $loggedInUser, $config, $users, $dictionary, $dbConn, $userIDLookup, $ip, $userAgent;

	if($loginChecked && !$force){
		return $loggedInUser;
	}

	$loggedInUser = Array();

	if(!isset($_COOKIE["sessionID"])){
		//No session cookie, therefore not logged in
		$loggedInUser = false;
		$loginChecked = true;
		return false;
	}

	$sessionID = "".$_COOKIE["sessionID"];
	$pepper = isset($config["PEPPER"]) ? $config["PEPPER"] : "BetterThanNothing";
	$sessionIDHash = HashPassword($sessionID, $pepper, $config["SESSION_PASSWORD_ITERATIONS"]);
        
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
		$username = $userIDLookup[$userID];
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

		return $loggedInUser;
	} else {
		//Session ID does not exist
		$loggedInUser = false;
		$loginChecked = true;
		return false;
	}
}

//Returns TRUE or FALSE depending on whether the logged in user is an admin.
//returns FALSE if there is no logged in user.
function IsAdmin(){
	global $adminList;
	$loggedInUser = IsLoggedIn();
	if($loggedInUser === false){
		return false;
	}
	
	if($loggedInUser["admin"] != 0){
		return true;
	}else{
		return false;
	}
}

// Fetchs the user bio in the database, and sets it under the "bio" key.
function LoadBio($user) {
	global $dbConn;
	if (isset($user)) {
		$clean_username = mysqli_real_escape_string($dbConn, $user["username"]);
		$sql = "SELECT user_bio FROM user WHERE user_username = '$clean_username'";
		$data = mysqli_query($dbConn, $sql);
		$info = mysqli_fetch_array($data);
		$user["bio"] = $info["user_bio"];
	}
	return $user;
}

function GetUsersOfUserFormatted($username){
	global $dbConn;
	
	$escapedUsername = mysqli_real_escape_string($dbConn, $username);
	$sql = "
		SELECT *
		FROM user
		WHERE user_username = '$escapedUsername';
	";
	$data = mysqli_query($dbConn, $sql);
	$sql = "";

	return ArrayToHTML(MySQLDataToArray($data));
}

function GetSessionsOfUserFormatted($userId){
	global $dbConn;

	$escapedID = mysqli_real_escape_string($dbConn, $userId);
	$sql = "
		SELECT *
		FROM session
		WHERE session_user_id = '$escapedID';
	";
	$data = mysqli_query($dbConn, $sql);
	$sql = "";

	return ArrayToHTML(MySQLDataToArray($data));
}


?>
