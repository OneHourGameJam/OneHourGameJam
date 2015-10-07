<?php

//Setup
$adminList = Array("admin");


//Init
session_start();
$loggedInUser = "";
$loginChecked = false;
$config = Array();
Init();

function startsWith($haystack, $needle) {
    // search backwards starting from haystack length characters from the end
    return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== FALSE;
}
function endsWith($haystack, $needle) {
    // search forward starting from end minus needle length characters
    return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== FALSE);
}

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

function IsLoggedIn(){
	global $loginChecked, $loggedInUser;
	
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
	$sessionIDHash = HashPassword($sessionID, $pepper);
	
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


function LogInOrRegister($username, $password){
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
		//User is registered already, check password
		$user = $users[$username];
		$correctPasswordHash = $user["password_hash"];
		$userSalt = $user["salt"];
		$passwordHash = HashPassword($password, $userSalt);
		if($correctPasswordHash == $passwordHash){
			//User password correct!
			$sessionID = "".GenerateSalt();
			$pepper = isset($config["PEPPER"]) ? $config["PEPPER"] : "BetterThanNothing";
			$sessionIDHash = HashPassword($sessionID, $pepper);
			
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
	}else{
		//User not yet registered, register now.
		RegisterUser($username, $password);
		
	}
}

function RegisterUser($username, $password){
	$users = json_decode(file_get_contents("data/users.json"), true);
	
	
	$userSalt = GenerateSalt();
	$passwordHash = HashPassword($password, $userSalt);
	
	if(isset($users[$username])){
		die("Username already registered");
	}else{
		$users[$username]["salt"] = $userSalt;
		$users[$username]["password_hash"] = $passwordHash;
	}
	
	file_put_contents("data/users.json", json_encode($users));
	LogInOrRegister($username, $password);
}

function LogOut(){
	setcookie("sessionID", "", time());
	$_COOKIE["sessionID"] = "";
}

function GenerateSalt(){
	return uniqid(mt_rand(), true);
}

function HashPassword($password, $salt){
	global $config;
	$pepper = isset($config["PEPPER"]) ? $config["PEPPER"] : "";
	$pswrd = $pepper.$password.$salt;
	return hash("sha256", $pswrd);
}

function CreateJam($theme, $date){
	$jamNumber = intval(GetNextJamNumber());
	$theme = trim($theme);
	$date = trim($date);
	
	//Authorize user
	if(IsLoggedIn() === false){
		die("Not logged in.");
	}
	
	//Authorize user
	if(IsAdmin() === false){
		die("Only admins can create jams.");
	}
	
	//Validate jam number
	if($jamNumber <= 0){
		die("Invalid jam number");
	}
	
	//Validate theme
	if(strlen($theme) <= 0){
		die("Invalid theme");
	}
	
	//Validate date
	if(strlen($date) <= 0){
		die("Invalid date");
	}
	
	$newJam = Array();
	$newJam["jam_number"] = $jamNumber;
	$newJam["theme"] = $theme;
	$newJam["date"] = $date;
	$newJam["entries"] = Array();
	file_put_contents("data/jams/jam_$jamNumber.json", json_encode($newJam));
}


function SubmitEntry($gameName, $gameURL, $screenshotURL){
	$gameName = trim($gameName);
	$gameURL = trim($gameURL);
	$screenshotURL = trim($screenshotURL);
	
	//Authorize user
	if(IsLoggedIn() === false){
		die("Not logged in.");
	}
	
	//Validate game name
	if(strlen($gameName) < 1){
		die("Game name not provided");
	}
	
	//Validate Game URL
	if(filter_var($gameURL, FILTER_VALIDATE_URL) === false){
		die("Invalid game URL");
	}
	
	//Validate Screenshot URL
	if($screenshotURL == ""){
		$screenshotURL = "logo.svg";
	}else if(filter_var($screenshotURL, FILTER_VALIDATE_URL) === false){
		die("Invalid screenshot URL. Leave blank for default.");
	}
	
	$filesToParse = GetSortedJamFileList();
	if(count($filesToParse) < 1){
		die("No jam to submit your entry to");
	}
	
	//First on the list is the current jam.
	$currentJamFile = $filesToParse[count($filesToParse) - 1];
	
	$currentJam = json_decode(file_get_contents($currentJamFile), true);
	if(isset($currentJam["entries"])){
		$entryUpdated = false;
		foreach($currentJam["entries"] as $i => $entry){
			if($entry["author"] == IsLoggedIn()){
				//Updating existing entry
				$currentJam["entries"][$i] = Array("title" => "$gameName", "author" => "".IsLoggedIn(), "url" => "$gameURL", "screenshot_url" => "$screenshotURL");
				file_put_contents($currentJamFile, json_encode($currentJam));
				$entryUpdated = true;
			}
		}
		if(!$entryUpdated){
			//Submitting new entry
			$currentJam["entries"][] = Array("title" => "$gameName", "author" => "".IsLoggedIn(), "url" => "$gameURL", "screenshot_url" => "$screenshotURL");
			file_put_contents($currentJamFile, json_encode($currentJam));
		}
	}
	
}

function GetNextJamNumber(){
	$NextJamNumber = 0;
	
	for($i = 0; $i < 1000; $i++){
		if(file_exists("data/jams/jam_$i.json")){
			$NextJamNumber = max($NextJamNumber, $i + 1);
		}
	}
	
	return $NextJamNumber;
}

function GetSortedJamFileList(){
	$filesToParse = Array();
	for($i = 0; $i < 1000; $i++){
		if(file_exists("data/jams/jam_$i.json")){
			$filesToParse[] = "data/jams/jam_$i.json";
		}
	}
	krsort($filesToParse);
	return $filesToParse;
}

//Initializes the site.
function Init(){
	global $config;
	
	$configTxt = file_get_contents("config/config.txt");
	$lines = explode("\n", $configTxt);
	$linesUpdated = Array();
	foreach($lines as $i => $line){
		$line = trim($line);
		if(startsWith($line, "#")){
			//Comment
			continue;
		}
		$linePair = explode("|", $line);
		if(count($linePair) == 2){
			//key-value pair
			$key = trim($linePair[0]);
			$value = trim($linePair[1]);
			$config[$key] = $value;
			
			//Validate line
			switch($key){
				case "PEPPER":
					if(strlen($value) < 1){
						//Generate pepper if none exists (first time site launch).
						$config[$key] = GenerateSalt();
						$lines[$i] = "$key | ".$config[$key];
						file_put_contents("config/config.txt", implode("\n", $lines));
					}
				break;
				default:
					$linesUpdated[] = $line;
				break;
			}
		}
		
	}
}


?>