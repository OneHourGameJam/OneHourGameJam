<?php


//Function called when the login form is sent. Either logs in or registers the
//user, depending on whether the username exists.
function LogInOrRegister($username, $password){
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
		//User is registered already, log them in
		return LogInUser($username, $password);
	}else{
		//User not yet registered, register now.
		return RegisterUser($username, $password);
	}
}

//Registers the given user. Funciton should be called through LogInOrRegister(...).
//Calls LogInUser(...) after registering the user to also log them in.
function RegisterUser($username, $password){
	global $userData, $dbConn, $ip, $userAgent, $configData;

	$username = str_replace(" ", "_", strtolower(trim($username)));
	$password = trim($password);

	if(!ValidateUsername($username, $configData)){
		return "INVALID_USERNAME_LENGTH";
	}

	if(!ValidatePassword($password, $configData)){
		return "INVALID_PASSWORD_LENGTH";
	}

	$userSalt = GenerateSalt();
	$userPasswordIterations = GenerateUserHashIterations($configData);
	$passwordHash = HashPassword($password, $userSalt, $userPasswordIterations, $configData);
	$admin = (count($userData->UserModels) == 0) ? 1 : 0;

	$userId = -1;
	if(isset($userData->UsernameToId[$username])){
		$userId = $userData->UsernameToId[$username];
	}

	if($userId != -1){
		return "USERNAME_ALREADY_REGISTERED";
	}else{
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
	
	$userData = new UserData();
	return LogInUser($username, $password);
}

//Logs in the user with the provided credentials.
//Sets the user's session cookie.
//Should not be called directly, call through LogInOrRegister(...)
function LogInUser($username, $password){
	global $configData, $userData, $dbConn;

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
	$correctPasswordHash = $user->PasswordHash;
	$userSalt = $user->Salt;
	$userPasswordIterations = intval($user->PasswordIterations);
	$passwordHash = HashPassword($password, $userSalt, $userPasswordIterations, $configData);
	if($correctPasswordHash == $passwordHash){
		//User password correct!
		$sessionID = "".GenerateSalt();
		$pepper = isset($configData->ConfigModels["PEPPER"]->Value) ? $configData->ConfigModels["PEPPER"]->Value : "BetterThanNothing";
		$sessionIDHash = HashPassword($sessionID, $pepper, $configData->ConfigModels["SESSION_PASSWORD_ITERATIONS"]->Value, $configData);

		$daysToKeepLoggedIn = $configData->ConfigModels["DAYS_TO_KEEP_LOGGED_IN"]->Value;
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
			'$userId',
			Now(),
			Now());
		";

		mysqli_query($dbConn, $sql) ;
		$sql = "";
	}else{
		return "INCORRECT_PASSWORD";
	}

	return "SUCCESS";
}

function PerformAction(&$loggedInUser){
	global $_POST;

	$username = (isset($_POST["un"])) ? $_POST["un"] : "";
	$password = (isset($_POST["pw"])) ? $_POST["pw"] : "";
	$loginChecked = false;

	$username = strtolower(trim($username));
	$password = trim($password);
	return LogInOrRegister($username, $password);
}

?>