<?php

//Edits an existing jam, identified by the jam id.
//Only changes the theme, date and time and colors does NOT change the jam number.
function EditJam($jamID, $theme, $date, $time, $colorsString){
	global $jams, $dbConn, $actionResult, $loggedInUser;

	//Authorize user (is admin)
	if(IsAdmin($loggedInUser) === false){
		$actionResult = "NOT_AUTHORIZED";
		AddAuthorizationWarning("Only admins can edit jams.", false);
		return;
	}

	$theme = trim($theme);
	$date = trim($date);
	$time = trim($time);

	$colorsList = explode("-", $colorsString);
	$colorSHexCodes = Array();
	foreach($colorsList as $i => $color){
		$clr = trim($color);
		if(!preg_match('/^[0-9A-Fa-f]{6}/', $clr)){
			$actionResult = "INVALID_COLOR";
			AddDataWarning("Invalid color: ".$clr." Must be a string of 6 hex values, which represent a color. Example:<br />FFFFFF-067BC2-D56062-F37748-ECC30B-84BCDA", false);
			return;
		}
		$colorSHexCodes[] = $clr;
	}
	$colors = implode("|", $colorSHexCodes);

	//Validate values
	$jamID = intval($jamID);
	if($jamID <= 0){
		$actionResult = "INVALID_JAM_ID";
		AddDataWarning("invalid jam id", false);
		return;
	}

	if(strlen($theme) <= 0){
		$actionResult = "INVALID_THEME";
		AddDataWarning("invalid theme", false);
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

	if(count($jams) == 0){
		$actionResult = "NO_JAMS_EXIST";
		return; //No jams exist
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

	AddDataSuccess("Jam Updated");

	$actionResult = "SUCCESS";
    AddToAdminLog("JAM_UPDATED", "Jam updated with values: JamID: $jamID, Theme: '$theme', Date: '$date', Time: '$time', Colors: $colorsString", "", $loggedInUser["username"]);
}

function PerformAction(&$loggedInUser){
	global $_POST;
	
	if(IsAdmin($loggedInUser) !== false){
		$jamID = intval($_POST["jamID"]);
		$theme = $_POST["theme"];
		$date = $_POST["date"];
		$time = $_POST["time"];
		$jamcolors = $_POST["jamcolors"];

		EditJam($jamID, $theme, $date, $time, $jamcolors);
	}
}

?>