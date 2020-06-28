<?php

//Creates a new jam with the provided theme, which starts at the given date
//and time. All three are non-blank strings. $date and $time should be
//parsable by PHP's date(...) function. Function also authorizes the user
//(checks whether or not they are an admin).
function CreateJam($theme, $date, $time, $colorsList){
	global $ip, $userAgent, $loggedInUser, $jamData, $adminLogData;

	$currentJamData = GetCurrentJamNumberAndID();
	$jamNumber = intval($currentJamData["NUMBER"] + 1);
	$theme = trim($theme);
	$date = trim($date);
	$time = trim($time);
	$username = trim($loggedInUser->Username);
	foreach($colorsList as $i => $color){
		$clr = trim($color);
		if(!preg_match('/^[0-9A-Fa-f]{6}/', $clr)){
			return "INVALID_COLOR";
		}
		$colorsList[$i] = $clr;
	}

	//Authorize user (logged in)
	if($loggedInUser === false){
		return "NOT_LOGGED_IN";
	}

	//Authorize user (is admin)
	if(IsAdmin($loggedInUser) === false){
		return "NOT_AUTHORIZED";
	}

	//Validate jam number
	if($jamNumber <= 0){
		return "INVALID_JAM_NUMBER";
	}

	//Validate theme
	if(strlen($theme) <= 0){
		return "INVALID_THEME";
	}

	//Validate date and time and create datetime object
	if(strlen($date) <= 0){
		return "INVALID_DATE";
	}else if(strlen($time) <= 0){
		return "INVALID_TIME";
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

	$jamData->AddJamToDatabase($ip, $userAgent, $username, $newJam["jam_number"], -2, $newJam["theme"], "".gmdate("Y-m-d H:i", $datetime), $colors, $adminLogData);

	return "SUCCESS";
}

function PerformAction(&$loggedInUser){
	global $_POST, $configData;
	
	if(IsAdmin($loggedInUser) !== false){
		$theme = (isset($_POST["theme"])) ? $_POST["theme"] : "";
		$date = (isset($_POST["date"])) ? $_POST["date"] : "";
		$time = (isset($_POST["time"])) ? $_POST["time"] : "";
		$jamColors = Array();
		for($colorIndex = 0; $colorIndex < $configData->ConfigModels["MAX_COLORS_FOR_JAM"]->Value; $colorIndex++){
			if(isset($_POST["jamcolor".$colorIndex])){
				$jamColors[] = $_POST["jamcolor".$colorIndex];
			}
		}
		if(count($jamColors) == 0){
			$jamColors = Array("FFFFFF");
		}

		return CreateJam($theme, $date, $time, $jamColors);
	}
}

?>