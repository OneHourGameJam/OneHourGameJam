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

//Function called when the login form is sent. Either logs in or registers the
//user, depending on whether the username exists.
function LogInOrRegister($username, $password){
	global $config, $users;
	
	$username = str_replace(" ", "_", strtolower(trim($username)));
	$password = trim($password);
	
	//Check username length
	if(strlen($username) < 2 || strlen($username) > 20){
		AddDataWarning("username must be between 2 and 20 characters", false);
		return;
	}
	
	//Check password length
	if(strlen($password) < 8){
		AddDataWarning("password must be at least 8 characters long", false);
		return;
	}
	
	//Check password length
	if(strlen($password) > 128){
		AddDataWarning("Okay, okay... okay... No! That's long enough! 128 character max password length is enough! Please, you're making me cry! ;_;", false);
		return;
	}
	
	if(isset($users[$username])){
		//User is registered already, log them in
		LogInUser($username, $password);
	}else{
		//User not yet registered, register now.
		RegisterUser($username, $password);
	}
}

//Registers the given user. Funciton should be called through LogInOrRegister(...).
//Calls LogInUser(...) after registering the user to also log them in.
function RegisterUser($username, $password){
	global $users, $dbConn, $ip, $userAgent;
	
	$username = str_replace(" ", "_", strtolower(trim($username)));
	$password = trim($password);
	
	//Check username length
	if(strlen($username) < 2 || strlen($username) > 20){
		AddDataWarning("username must be between 2 and 20 characters", false);
		return;
	}
	
	//Check password length
	if(strlen($password) < 8){
		AddDataWarning("password must be at least 8 characters long", false);
		return;
	}
	
	//Check password length
	if(strlen($password) > 128){
		AddDataWarning("Okay, okay... okay... No! That's long enough! 128 character max password length is enough! Please, you're making me cry! ;_;", false);
		return;
	}
	
	$userSalt = GenerateSalt();
	$userPasswordIterations = intval(rand(10000, 20000));
	$passwordHash = HashPassword($password, $userSalt, $userPasswordIterations);
	$admin = (count($users) == 0) ? 1 : 0;
	
	if(isset($users[$username])){
		AddDataWarning("Username already registered", false);
		return;
	}else{
		$newUser = Array();
		$newUser["salt"] = $userSalt;
		$newUser["password_hash"] = $passwordHash;
		$newUser["password_iterations"] = $userPasswordIterations;
		$newUser["admin"] = $admin;
		
		$users[$username] = $newUser;
		
		$usernameClean = mysqli_real_escape_string($dbConn, $username);
		
		$sql = "
			INSERT INTO user
			(user_id,
			user_username,
			user_datetime,
			user_register_ip,
			user_register_user_agent,
			user_display_name,
			user_password_salt,
			user_password_hash,
			user_password_iterations,
			user_last_login_datetime,
			user_last_ip,
			user_last_user_agent,
			user_email,
			user_role)
			VALUES
			(null,
			'$usernameClean',
			Now(),
			'$ip',
			'$userAgent',
			'$usernameClean',
			'$userSalt',
			'$passwordHash',
			$userPasswordIterations,
			Now(),
			'$ip',
			'$userAgent',
			'',
			$admin);
		";
		mysqli_query($dbConn, $sql) ;
		$sql = "";

	}
	
	LoadUsers();
	LogInUser($username, $password);
}

//Logs in the user with the provided credentials.
//Sets the user's session cookie.
//Should not be called directly, call through LogInOrRegister(...)
function LogInUser($username, $password){
	global $config, $users, $dbConn;

	$username = str_replace(" ", "_", strtolower(trim($username)));
	$password = trim($password);

	//Check username length
	if(strlen($username) < 2 || strlen($username) > 20){
		AddDataWarning("username must be between 2 and 20 characters", false);
		return;
	}

	if(!isset($users[$username])){
		AddDataWarning("User does not exist", false);
		return;
	}

	$user = $users[$username];
	$userID = $user["id"];
	$correctPasswordHash = $user["password_hash"];
	$userSalt = $user["salt"];
	$userPasswordIterations = intval($user["password_iterations"]);
	$passwordHash = HashPassword($password, $userSalt, $userPasswordIterations);
	if($correctPasswordHash == $passwordHash){
		//User password correct!
		$sessionID = "".GenerateSalt();
		$pepper = isset($config["PEPPER"]) ? $config["PEPPER"] : "BetterThanNothing";
		$sessionIDHash = HashPassword($sessionID, $pepper, $config["SESSION_PASSWORD_ITERATIONS"]);

		setcookie("sessionID", $sessionID, time()+60*60*24*30);
		$_COOKIE["sessionID"] = $sessionID;

		$sql = "
			INSERT INTO session
			(session_id,
			session_user_id,
			session_datetime_started,
			session_datetime_last_used)
			VALUES
			('$sessionIDHash',
			'$userID',
			Now(),
			Now());
		";

		mysqli_query($dbConn, $sql) ;
		$sql = "";
	}else{
		//User password incorrect!
		AddDataWarning("Incorrect username / password combination.", false);
		return;
	}
}

//Logs out the current user by setting their sessionID cookie to blank and expiring it.
//TODO: Clear session from on-server session data
function LogOut(){
	global $dbConn, $config;

	// Delete the session out of our DB
	$sessionID = "".$_COOKIE["sessionID"];
	$pepper = isset($config["PEPPER"]) ? $config["PEPPER"] : "BetterThanNothing";
	$sessionIDHash = HashPassword($sessionID, $pepper, $config["SESSION_PASSWORD_ITERATIONS"]);

	$sql = "
		DELETE FROM session
		WHERE session_id = '$sessionIDHash';
	";

	mysqli_query($dbConn, $sql) ;
	$sql = "";

	// Clear the cookie
	setcookie("sessionID", "", time());
	$_COOKIE["sessionID"] = "";
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



//Edits an existing user, identified by the username.
//Valid values for isAdmin are 0 (not admin) and 1 (admin)
//Only changes whether the user is an admin, does NOT change the user's username.
function EditUser($username, $isAdmin){
	global $users, $dbConn;
	
	//Authorize user (is admin)
	if(IsAdmin() === false){
		AddAuthorizationWarning("Only admins can edit entries.", false);
		return;
	}
	
	//Validate values
	if($isAdmin == 0){
		$isAdmin = 0;
	}else if($isAdmin == 1){
		$isAdmin = 1;
	}else{
		AddDataWarning("Bad isadmin value", false);
		return;
	}
	
	//Check that the user exists
	if(!isset($users[$username])){
		AddDataWarning("User does not exist", false);
		return;
	}
		
	$usernameClean = mysqli_real_escape_string($dbConn, $username);
	
	$sql = "	
		UPDATE user
		SET
		user_role = $isAdmin
		WHERE user_username = '$usernameClean';
	";
	mysqli_query($dbConn, $sql) ;
    $sql = "";
    
    AddToAdminLog("USER_EDITED", "User $username updated with values: IsAdmin: $isAdmin", $username);
	
	LoadUsers();
	$loggedInUser = IsLoggedIn(TRUE);
}

//Changes data about the logged in user
function ChangeUserData($displayName, $twitterHandle, $emailAddress, $bio){
	global $users, $loggedInUser, $dbConn;
	
	$loggedInUser = IsLoggedIn();
	
	//Authorize user (is admin)
	if($loggedInUser === false){
		AddAuthorizationWarning("Not logged in.", false);
		return;
	}
	
	//Validate values
	if(!$displayName || strlen($displayName) <= 0 || strlen($displayName) > 50){
		AddDataWarning("Display name must be between 0 and 50 characters long", false);
		return;
	}
	
	//Validate email address
	if($emailAddress != "" && !filter_var($emailAddress, FILTER_VALIDATE_EMAIL)) {
		AddDataWarning("Provided email address is not valid", false);
		return;
	}
		
	$displayNameClean = mysqli_real_escape_string($dbConn, $displayName);
	$twitterHandleClean = mysqli_real_escape_string($dbConn, $twitterHandle);
	$emailAddressClean = mysqli_real_escape_string($dbConn, $emailAddress);
	$bioClean = mysqli_real_escape_string($dbConn, CleanHtml($bio));
	$usernameClean = mysqli_real_escape_string($dbConn, $loggedInUser["username"]);
	
	$sql = "	
		UPDATE user
		SET
		user_display_name = '$displayNameClean',
		user_twitter = '$twitterHandleClean',
		user_email = '$emailAddressClean',
		user_bio = '$bioClean'
		WHERE user_username = '$usernameClean';
	";
	$data = mysqli_query($dbConn, $sql);
	$sql = "";
	
	LoadUsers();
	$loggedInUser = IsLoggedIn(TRUE);
}

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

//Edits an existing user's password, user is identified by the username.
function EditUserPassword($username, $newPassword1, $newPassword2){
	global $users, $dbConn;
	
	//Authorize user (is admin)
	if(IsAdmin() === false){
		AddAuthorizationWarning("Only admins can edit entries.", false);
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
	if(!isset($users[$username])){
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
    
	LoadUsers();
	$loggedInUser = IsLoggedIn(TRUE);
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
