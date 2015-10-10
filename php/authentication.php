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
//TODO: Replace die() with in-page warning
function HashPassword($password, $salt, $iterations){
	global $config;
	$pepper = isset($config["PEPPER"]) ? $config["PEPPER"] : "";
	$pswrd = $pepper.$password.$salt;
	
	//Check that we have sufficient iterations for password generation.
	if($iterations < 100){
		die("Insufficient iterations for password generation.");
	}else if($iterations > 100000){
		die("Too many iterations for password generation.");
	}
	
	for($i = 0; $i < $iterations; $i++){
		$pswrd = hash("sha256", $pswrd);
	}
	return $pswrd;
}

//Function called when the login form is sent. Either logs in or registers the
//user, depending on whether the username exists. Dies if username exists and the
//password is incorrect.
//TODO: Replace die() with in-page warning
function LogInOrRegister($username, $password){
	global $config;
	
	$users = json_decode(file_get_contents("data/users.json"), true);
	$username = strtolower(trim($username));
	$password = trim($password);
	
	//Check username length
	if(strlen($username) < 2 || strlen($username) > 20){
		die("username must be between 2 and 20 characters");
	}
	
	//Check password length
	if(strlen($password) < 8 || strlen($password) > 20){
		die("password must be between 8 and 20 characters");
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
//Dies if user already exists.
//Calls LogInUser(...) after registering the user to also log them in.
//TODO: Replace die() with in-page warning
function RegisterUser($username, $password){
	$users = json_decode(file_get_contents("data/users.json"), true);
	$username = strtolower(trim($username));
	$password = trim($password);
	
	//Check username length
	if(strlen($username) < 2 || strlen($username) > 20){
		die("username must be between 2 and 20 characters");
	}
	
	//Check password length
	if(strlen($password) < 8 || strlen($password) > 20){
		die("password must be between 8 and 20 characters");
	}
	
	$userSalt = GenerateSalt();
	$userPasswordIterations = intval(rand(10000, 20000));
	$passwordHash = HashPassword($password, $userSalt, $userPasswordIterations);
	
	if(isset($users[$username])){
		die("Username already registered");
	}else{
		$users[$username]["salt"] = $userSalt;
		$users[$username]["password_hash"] = $passwordHash;
		$users[$username]["password_iterations"] = $userPasswordIterations;
	}
	
	file_put_contents("data/users.json", json_encode($users));
	LogInUser($username, $password);
}

//Logs in the user with the provided credentials.
//Sets the user's session cookie.
//Should not be called directly, call through LogInOrRegister(...)
//Dies if user does not exist or the password is incorrect
//TODO: Replace die() with in-page warning
function LogInUser($username, $password){
	global $config;
	
	$users = json_decode(file_get_contents("data/users.json"), true);
	$username = strtolower(trim($username));
	$password = trim($password);
	
	//Check username length
	if(strlen($username) < 2 || strlen($username) > 20){
		die("username must be between 2 and 20 characters");
	}
	
	//Check password length
	if(strlen($password) < 8 || strlen($password) > 20){
		die("password must be between 8 and 20 characters");
	}
	
	if(!isset($users[$username])){
		die("User does not exist");
	}
	
	$user = $users[$username];
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
		
		$sessions = Array();
		if(file_exists("data/sessions.json")){
			$sessions = json_decode(file_get_contents("data/sessions.json"), true);
		}
		
		$sessions[$sessionIDHash]["username"] = $username;
		$sessions[$sessionIDHash]["datetime"] = time();
		
		file_put_contents("data/sessions.json", json_encode($sessions));
		
	}else{
		//User password incorrect!
		die("Incorrect username / password combination.");
	}
}

//Logs out the current user by setting their sessionID cookie to blank and expiring it.
//TODO: Clear session from on-server session data
function LogOut(){
	setcookie("sessionID", "", time());
	$_COOKIE["sessionID"] = "";
}

//Checks whether the current user, identified by the sessionID in their cookies, is
//logged in. This function only actually performs the check the first time it is called.
//after then it caches the result in the global $loggedInUser variable and simply
//returns that. This is to prevent re-hashing the provided sessionID multiple times.
//To force it to re-check, set the global variable $loginChecked to false.
//Returns either the logged in user's username or FALSE if not logged in.
function IsLoggedIn(){
	global $loginChecked, $loggedInUser, $config;
	
	if($loginChecked){
		return $loggedInUser;
	}
	
	if(!isset($_COOKIE["sessionID"])){
		//No session cookie, therefore not logged in
		$loggedInUser = false;
		$loginChecked = true;
		return false;
	}
	
	if(!file_exists("data/sessions.json")){
		//No session was ever created on the site
		$loggedInUser = false;
		$loginChecked = true;
		return false;
	}
	
	$sessions = json_decode(file_get_contents("data/sessions.json"), true);
	$sessionID = "".$_COOKIE["sessionID"];
	$pepper = isset($config["PEPPER"]) ? $config["PEPPER"] : "BetterThanNothing";
	$sessionIDHash = HashPassword($sessionID, $pepper, $config["SESSION_PASSWORD_ITERATIONS"]);
	
	if(!isset($sessions[$sessionIDHash])){
		//Session ID does not exist
		$loggedInUser = false;
		$loginChecked = true;
		return false;
	}else{
		//Session ID does in fact exist
		$loggedInUser = $sessions[$sessionIDHash]["username"];
		$loginChecked = true;
		return $sessions[$sessionIDHash]["username"];
	}
}

//Returns TRUE or FALSE depending on whether the logged in user is an admin.
//returns FALSE if there is no logged in user.
//Admins are set in the global variable $adminList, defined in global.php
//TODO: Move adminlist to config.
function IsAdmin(){
	global $adminList;
	$username = IsLoggedIn();
	if($username === false){
		return false;
	}
	
	if(array_search($username, $adminList) !== false){
		return true;
	}else{
		return false;
	}
}
?>