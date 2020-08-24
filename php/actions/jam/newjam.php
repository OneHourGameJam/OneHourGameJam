<?php

//Creates a new jam with the provided theme, which starts at the given date
//and time. All three are non-blank strings. $date and $time should be
//parsable by PHP's date(...) function. Function also authorizes the user
//(checks whether or not they are an admin).
function CreateJam(MessageService &$messageService, $theme, $date, $time, $colorsList){
	global $ip, $userAgent, $loggedInUser, $jamData, $userData;

	$maxNonDeletedJamNumber = 0;
	foreach($jamData->JamModels as $i => $jamModel){
		if($jamModel->Deleted){
			continue;
		}
		$maxNonDeletedJamNumber = max($maxNonDeletedJamNumber, $jamModel->JamNumber);
	}

	$jamNumber = $maxNonDeletedJamNumber + 1;
	$theme = trim($theme);
	$date = trim($date);
	$time = trim($time);
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

	$jamData->AddJamToDatabase($ip, $userAgent, $loggedInUser->Id, $jamNumber, -2, $theme, "".gmdate("Y-m-d H:i", $datetime), $colors);

	$messageService->SendMessage(LogMessage::UserLogMessage(
		"JAM_ADDED", 
		"Jam scheduled with values: JamNumber: $jamNumber, Theme: '$theme', StartTime: '$startTime', Colors: $colors", 
		$loggedInUser->Id)
	);
	$userData->LogAdminAction($loggedInUser->Id);

	return "SUCCESS";
}

function PerformAction(MessageService &$messageService, &$loggedInUser){
	global $_POST, $configData;
	
	if(IsAdmin($loggedInUser) !== false){
		$theme = (isset($_POST[FORM_NEWJAM_THEME])) ? $_POST[FORM_NEWJAM_THEME] : "";
		$date = (isset($_POST[FORM_NEWJAM_DATE])) ? $_POST[FORM_NEWJAM_DATE] : "";
		$time = (isset($_POST[FORM_NEWJAM_TIME])) ? $_POST[FORM_NEWJAM_TIME] : "";
		$jamColors = Array();
		for($colorIndex = 0; $colorIndex < $configData->ConfigModels[CONFIG_MAX_COLORS_FOR_JAM]->Value; $colorIndex++){
			if(isset($_POST[FORM_NEWJAM_JAM_COLOR.$colorIndex])){
				$jamColors[] = $_POST[FORM_NEWJAM_JAM_COLOR.$colorIndex];
			}
		}
		if(count($jamColors) == 0){
			$jamColors = Array("FFFFFF");
		}

		return CreateJam($messageService, $theme, $date, $time, $jamColors);
	}
}

?>