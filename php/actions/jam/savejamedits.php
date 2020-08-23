<?php

//Edits an existing jam, identified by the jam id.
//Only changes the theme, date and time and colors does NOT change the jam number.
function EditJam(MessageService &$messageService, $jamId, $theme, $date, $time, $colorsString){
	global $jamData, $loggedInUser, $jamDbInterface;

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
	$jamId = intval($jamId);
	if(!isset($jamData->JamModels[$jamId])){
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

	if(count($jamData->JamModels) == 0){
		return "NO_JAMS_EXIST";
	}

	$jamDbInterface->Update($jamId, $theme, gmdate("Y-m-d H:i", $datetime), $colors);

	$messageService->SendMessage(LogMessage::UserLogMessage(
		"JAM_UPDATED", 
		"Jam updated with values: JamID: $jamId, Theme: '$theme', Date: '$date', Time: '$time', Colors: $colorsString", 
		$loggedInUser->Id)
	);
	
	return "SUCCESS";
}

function PerformAction(MessageService &$messageService, &$loggedInUser){
	global $_POST;
	
	if(IsAdmin($loggedInUser) !== false){
		$jamId = intval($_POST[FORM_EDITJAM_JAM_ID]);
		$theme = $_POST[FORM_EDITJAM_THEME];
		$date = $_POST[FORM_EDITJAM_DATE];
		$time = $_POST[FORM_EDITJAM_TIME];
		$jamcolors = $_POST[FORM_EDITJAM_JAM_COLORS];

		return EditJam($messageService, $jamId, $theme, $date, $time, $jamcolors);
	}
}

?>