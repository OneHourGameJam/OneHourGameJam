<?php

//Creates a new jam with the provided theme, which starts at the given date
//and time. All three are non-blank strings. $date and $time should be
//parsable by PHP's date(...) function. Function also authorizes the user
//(checks whether or not they are an admin).
function CreateJam($theme, $date, $time, $colorsList){
	global $ip, $userAgent, $loggedInUser, $actionResult;

	$currentJamData = GetCurrentJamNumberAndID();
	$jamNumber = intval($currentJamData["NUMBER"] + 1);
	$theme = trim($theme);
	$date = trim($date);
	$time = trim($time);
	$username = trim($loggedInUser["username"]);
	foreach($colorsList as $i => $color){
		$clr = trim($color);
		if(!preg_match('/^[0-9A-Fa-f]{6}/', $clr)){
			$actionResult = "INVALID_COLOR";
			AddDataWarning("Invalid color: ".$clr." Must be a string of 6 hex values, which represent a color. Example:<br />FFFFFF-067BC2-D56062-F37748-ECC30B-84BCDA", false);
			return;
		}
		$colorsList[$i] = $clr;
	}

	//Authorize user (logged in)
	if(IsLoggedIn() === false){
		$actionResult = "NOT_LOGGED_IN";
		AddAuthorizationWarning("Not logged in.", false);
		return;
	}

	//Authorize user (is admin)
	if(IsAdmin() === false){
		$actionResult = "NOT_AUTHORIZED";
		AddAuthorizationWarning("Only admins can create jams.", false);
		return;
	}

	//Validate jam number
	if($jamNumber <= 0){
		$actionResult = "INVALID_JAM_NUMBER";
		AddDataWarning("Invalid jam number", false);
		return;
	}

	//Validate theme
	if(strlen($theme) <= 0){
		$actionResult = "INVALID_THEME";
		AddDataWarning("Invalid theme", false);
		return;
	}

	//Validate date and time and create datetime object
	if(strlen($date) <= 0){
		$actionResult = "INVALID_DATE";
		AddDataWarning("Invalid date", false);
		return;
	}else if(strlen($time) <= 0){
		$actionResult = "INVALID_TIME";
		AddDataWarning("Invalid time", false);
		return;
	}else{
		$datetime = strtotime($date." ".$time." UTC");
	}

	$colors = implode("|", $colorsList);

	$newJam = Array();
	$newJam["jam_number"] = $jamNumber;
	$newJam["theme"] = $theme;
	$newJam["date"] = gmdate("d M Y", $datetime);
	$newJam["time"] = gmdate("H:i", $datetime);
	$newJam["start_time"] = gmdate("c", $datetime);
	$newJam["entries"] = Array();

	AddJamToDatabase($ip, $userAgent, $username, $newJam["jam_number"], $newJam["theme"], "".gmdate("Y-m-d H:i", $datetime), $colors);

	AddDataSuccess("Jam Scheduled");
	$actionResult = "SUCCESS";
}

if(IsAdmin()){
    $theme = (isset($_POST["theme"])) ? $_POST["theme"] : "";
    $date = (isset($_POST["date"])) ? $_POST["date"] : "";
    $time = (isset($_POST["time"])) ? $_POST["time"] : "";
    $jamColors = Array();
    for($colorIndex = 0; $colorIndex < $config["MAX_COLORS_FOR_JAM"]["VALUE"]; $colorIndex++){
        if(isset($_POST["jamcolor".$colorIndex])){
            $jamColors[] = $_POST["jamcolor".$colorIndex];
        }
    }
    if(count($jamColors) == 0){
        $jamColors = Array("FFFFFF");
    }

    CreateJam($theme, $date, $time, $jamColors);
}

?>