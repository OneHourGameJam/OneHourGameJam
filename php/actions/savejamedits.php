<?php

//Edits an existing jam, identified by the jam number.
//Only changes the theme, date and time, does NOT change the jam number.
function EditJam($jamNumber, $theme, $date, $time, $colorsString){
	global $jams, $dbConn;
	
	//Authorize user (is admin)
	if(IsAdmin() === false){
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
			AddDataWarning("Invalid color: ".$clr." Must be a string of 6 hex values, which represent a color. Example:<br />FFFFFF-067BC2-D56062-F37748-ECC30B-84BCDA", false);
			return;
		}
		$colorSHexCodes[] = $clr;
	}
	$colors = implode("|", $colorSHexCodes);
	
	//Validate values
	$jamNumber = intval($jamNumber);
	if($jamNumber <= 0){
		AddDataWarning("invalid jam number", false);
		return;
	}
	
	if(strlen($theme) <= 0){
		AddDataWarning("invalid theme", false);
		return;
	}
	
	//Validate date and time and create datetime object
	if(strlen($date) <= 0){
		AddDataWarning("Invalid date", false);
		return;
	}else if(strlen($time) <= 0){
		AddDataWarning("Invalid time", false);
		return;
	}else{
		$datetime = strtotime($date." ".$time." UTC");
	}
	
	if(count($jams) == 0){
		return; //No jams exist
	}
	
	$escapedTheme = mysqli_real_escape_string($dbConn, $theme);
	$escapedStartTime = mysqli_real_escape_string($dbConn, "".gmdate("Y-m-d H:i", $datetime));
	$escapedJamNumber = mysqli_real_escape_string($dbConn, "$jamNumber");
	$escapedColors = mysqli_real_escape_string($dbConn, "$colors");
	
	$sql = "
		UPDATE jam
		SET jam_theme = '$escapedTheme', 
		    jam_start_datetime = '$escapedStartTime', 
		    jam_colors = '$escapedColors'
		WHERE jam_jam_number = $escapedJamNumber
		  AND jam_deleted = 0";
	$data = mysqli_query($dbConn, $sql);
	$sql = "";
	
	AddDataSuccess("Jam Updated");
    
    AddToAdminLog("JAM_UPDATED", "Jam updated with values: JamNumber: $jamNumber, Theme: '$theme', Date: '$date', Time: '$time', Colors: $colorsString", "");
}

if(IsAdmin()){
    $jamNumber = intval($_POST["jam_number"]);
    $theme = $_POST["theme"];
    $date = $_POST["date"];
    $time = $_POST["time"];
    $jamcolors = $_POST["jamcolors"];
    
    EditJam($jamNumber, $theme, $date, $time, $jamcolors);
}
$page = "main"; 

?>