<?php
//This file is the site's entry point, called directly from the main index.php
//All other files in the /php dirrectory are included from here.

//Fetch plugins
include_once("plugins/plugins.php");

//Global variable definition
session_start();
include_once("global.php");
include_once("helpers.php");
include_once("authentication.php");
include_once("sanitize.php");

//Initialization. This is where configuration is loaded
include_once("init.php");

//Creates a new jam with the provided theme, which starts at the given date
//and time. All three are non-blank strings. $date and $time should be
//parsable by PHP's date(...) function. Function also authorizes the user
//(checks whether or not they are an admin).
//TODO: Replace die() with in-page warning
function CreateJam($theme, $date, $time){
	$jamNumber = intval(GetNextJamNumber());
	$theme = trim($theme);
	$date = trim($date);
	$time = trim($time);
	
	//Authorize user (logged in)
	if(IsLoggedIn() === false){
		die("Not logged in.");
	}
	
	//Authorize user (is admin)
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
	
	//Validate date and time and create datetime object
	if(strlen($date) <= 0){
		die("Invalid date");
	}else if(strlen($time) <= 0){
		die("Invalid time");
	}else{
		$datetime = strtotime($date." ".$time);
	}
	
	$newJam = Array();
	$newJam["jam_number"] = $jamNumber;
	$newJam["theme"] = $theme;
	$newJam["date"] = date("d M Y", $datetime);
	$newJam["time"] = date("H:i", $datetime);
	$newJam["start_time"] = date("c", $datetime);
	$newJam["entries"] = Array();
	file_put_contents("data/jams/jam_$jamNumber.json", json_encode($newJam));
}

//Submits a new entry to the last jam. All parameters are strings, $gameName
//and $gameURL must be non-blank, $gameURL must be a valid URL, $screenshotURL
//can either be blank or a valid URL. If blank, a default image is used instead.
//Function also authorizes the user (must be logged in)
//TODO: Replace die() with in-page warning
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
	if(SanitizeURL($gameURL) === false){
		die("Invalid game URL");
	}
	
	//Validate Screenshot URL
	if($screenshotURL == ""){
		$screenshotURL = "logo.png";
	}else if(SanitizeURL($screenshotURL) === false){
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



?>