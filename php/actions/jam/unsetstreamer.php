<?php

//Edits an existing jam, identified by the jam id.
//Only changes the theme, date and time and colors does NOT change the jam number.
function UnsetStreamer(MessageService &$messageService, $jamNumber){
	global $loggedInUser, $jamDbInterface, $jamData;

	//Authorize user
	if($loggedInUser === false){
		return "NOT_LOGGED_IN";
	}

	$jamModel = $jamData->GetJamByNumber($jamNumber);
	if($jamModel == null || $jamModel->JamNumber == 0){
		return "NO_JAM_TO_SUBMIT_TO";
	}

	$jamId = $jamModel->Id;
	if(!isset($jamData->JamModels[$jamId])){
		return "INVALID_JAM_ID";
	}

	if($jamData->JamModels[$jamId]->StreamerUserId != $loggedInUser->Id){
		return "NOT_CURRENTLY_STREAMER";
	}

	$streamerUserId = $loggedInUser->Id;
	$streamerTwitchUsername = $loggedInUser->Twitch;

	$jamDbInterface->UpdateStreamer($jamId, 0, "");

	$messageService->SendMessage(LogMessage::UserLogMessage(
		"JAM_STREAMER", 
		"User $streamerUserId unset themself streamer for jam $jamId with Twitch username $streamerTwitchUsername", 
		$loggedInUser->Id)
	);
	
	return "SUCCESS";
}

function PerformAction(MessageService &$messageService, &$loggedInUser){
	global $_POST;
	
	$jamNumber = intval($_POST[FORM_SETSTREAMER_JAM_NUMBER]);

	return UnsetStreamer($messageService, $jamNumber);
}

?>