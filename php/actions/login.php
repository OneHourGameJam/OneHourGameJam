<?php


//Function called when the login form is sent. Either logs in or registers the
//user, depending on whether the username exists.
function LogInOrRegister($username, $password){
	global $users, $actionResult, $config;

	$username = str_replace(" ", "_", strtolower(trim($username)));
	$password = trim($password);

	if(!ValidateUsername($username, $config)){
		$actionResult = "INVALID_USERNAME_LENGTH";
		AddDataWarning("username must be between ".$config["MINIMUM_USERNAME_LENGTH"]["VALUE"]." and ".$config["MAXIMUM_USERNAME_LENGTH"]["VALUE"]." characters long", false);
		return;
	}

	if(!ValidatePassword($password, $config)){
		$actionResult = "INVALID_PASSWORD_LENGTH";
		AddDataWarning("password must be between ".$config["MINIMUM_PASSWORD_LENGTH"]["VALUE"]." and ".$config["MAXIMUM_PASSWORD_LENGTH"]["VALUE"]." characters long", false);
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
	global $users, $dbConn, $ip, $userAgent, $actionResult, $config;

	$username = str_replace(" ", "_", strtolower(trim($username)));
	$password = trim($password);

	if(!ValidateUsername($username, $config)){
		$actionResult = "INVALID_USERNAME_LENGTH";
		AddDataWarning("username must be between ".$config["MINIMUM_USERNAME_LENGTH"]["VALUE"]." and ".$config["MAXIMUM_USERNAME_LENGTH"]["VALUE"]." characters long", false);
		return;
	}

	if(!ValidatePassword($password, $config)){
		$actionResult = "INVALID_PASSWORD_LENGTH";
		AddDataWarning("password must be between ".$config["MINIMUM_PASSWORD_LENGTH"]["VALUE"]." and ".$config["MAXIMUM_PASSWORD_LENGTH"]["VALUE"]." characters long", false);
		return;
	}

	$userSalt = GenerateSalt();
	$userPasswordIterations = GenerateUserHashIterations($config);
	$passwordHash = HashPassword($password, $userSalt, $userPasswordIterations, $config);
	$admin = (count($users) == 0) ? 1 : 0;

	if(isset($users[$username])){
		$actionResult = "USERNAME_ALREADY_REGISTERED";
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

		$actionResult = "REGISTRATION_SUCCESS";
	}

	$users = LoadUsers();
	LogInUser($username, $password);
}

//Logs in the user with the provided credentials.
//Sets the user's session cookie.
//Should not be called directly, call through LogInOrRegister(...)
function LogInUser($username, $password){
	global $config, $users, $dbConn, $actionResult;

	$username = str_replace(" ", "_", strtolower(trim($username)));
	$password = trim($password);

	if(!ValidateUsername($username, $config)){
		$actionResult = "INVALID_USERNAME_LENGTH";
		AddDataWarning("username must be between ".$config["MINIMUM_USERNAME_LENGTH"]["VALUE"]." and ".$config["MAXIMUM_USERNAME_LENGTH"]["VALUE"]." characters long", false);
		return;
	}

	if(!ValidatePassword($password, $config)){
		$actionResult = "INVALID_PASSWORD_LENGTH";
		AddDataWarning("password must be between ".$config["MINIMUM_PASSWORD_LENGTH"]["VALUE"]." and ".$config["MAXIMUM_PASSWORD_LENGTH"]["VALUE"]." characters long", false);
		return;
	}

	if(!isset($users[$username])){
		$actionResult = "USER_DOES_NOT_EXIST";
		AddDataWarning("User does not exist", false);
		return;
	}

	$user = $users[$username];
	$userID = $user["id"];
	$correctPasswordHash = $user["password_hash"];
	$userSalt = $user["salt"];
	$userPasswordIterations = intval($user["password_iterations"]);
	$passwordHash = HashPassword($password, $userSalt, $userPasswordIterations, $config);
	if($correctPasswordHash == $passwordHash){
		//User password correct!
		$sessionID = "".GenerateSalt();
		$pepper = isset($config["PEPPER"]["VALUE"]) ? $config["PEPPER"]["VALUE"] : "BetterThanNothing";
		$sessionIDHash = HashPassword($sessionID, $pepper, $config["SESSION_PASSWORD_ITERATIONS"]["VALUE"], $config);

		$daysToKeepLoggedIn = $config["DAYS_TO_KEEP_LOGGED_IN"]["VALUE"];
		setcookie("sessionID", $sessionID, time()+60*60*24*$daysToKeepLoggedIn);
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
		$actionResult = "INCORRECT_PASSWORD";
		AddDataWarning("Incorrect username / password combination.", false);
		return;
	}
	$actionResult = "SUCCESS";
}

$username = (isset($_POST["un"])) ? $_POST["un"] : "";
$password = (isset($_POST["pw"])) ? $_POST["pw"] : "";
$loginChecked = false;

$username = strtolower(trim($username));
$password = trim($password);
LogInOrRegister($username, $password);

?>