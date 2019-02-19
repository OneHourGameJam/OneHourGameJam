<?php

//Edits an existing jam, identified by the jam id.
//Only changes the theme, date and time and colors does NOT change the jam number.
function EditJam($jamID, $theme, $date, $time, $colorsString){
	global $jams, $dbConn, $loggedInUser;

	//Authorize user (is admin)
	if(IsAdmin($loggedInUser) === false){
		return "NOT_AUTHORIZED";
	}

	$theme = trim($theme);
	$date = trim($date);
	$time = trim($time);

	$colorsList = explode("-", $colorsString);
	$colorSHexCodes = Array();
	foreach($colorsList as $i => $color){
		$clr = trim($color);
		if(!preg_match('/^[0-9A-Fa-f]{6}/', $clr)){
			return "INVALID_COLOR";
		}
		$colorSHexCodes[] = $clr;
	}
	$colors = implode("|", $colorSHexCodes);

	//Validate values
	$jamID = intval($jamID);
	if(!isset($jams[$jamID])){
		return "INVALID_JAM_ID";
	}

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

	if(count($jams) == 0){
		return "NO_JAMS_EXIST";
	}

	$escapedTheme = mysqli_real_escape_string($dbConn, $theme);
	$escapedStartTime = mysqli_real_escape_string($dbConn, "".gmdate("Y-m-d H:i", $datetime));
	$escapedJamID = mysqli_real_escape_string($dbConn, $jamID);
	$escapedColors = mysqli_real_escape_string($dbConn, "$colors");

	$sql = "
		UPDATE jam
		SET jam_theme = '$escapedTheme',
		    jam_start_datetime = '$escapedStartTime',
		    jam_colors = '$escapedColors'
		WHERE jam_id = $escapedJamID";
	$data = mysqli_query($dbConn, $sql);
	$sql = "";

	AddToAdminLog("JAM_UPDATED", "Jam updated with values: JamID: $jamID, Theme: '$theme', Date: '$date', Time: '$time', Colors: $colorsString", "", $loggedInUser["username"]);
	
	return "SUCCESS";
}

function PerformAction(&$loggedInUser){
	global $_POST;
	
	if(IsAdmin($loggedInUser) !== false){
		$jamID = intval($_POST["jamID"]);
		$theme = $_POST["theme"];
		$date = $_POST["date"];
		$time = $_POST["time"];
		$jamcolors = $_POST["jamcolors"];

		return EditJam($jamID, $theme, $date, $time, $jamcolors);
	}
}

?>