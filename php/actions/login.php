<?php


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

$username = (isset($_POST["un"])) ? $_POST["un"] : "";
$password = (isset($_POST["pw"])) ? $_POST["pw"] : "";
$loginChecked = false;

$username = strtolower(trim($username));
$password = trim($password);
LogInOrRegister($username, $password);

?>