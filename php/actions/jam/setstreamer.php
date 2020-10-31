<?php

//Edits an existing jam, identified by the jam id.
//Only changes the theme, date and time and colors does NOT change the jam number.
function SetStreamer(MessageService &$messageService, $jamNumber){
	global $loggedInUser, $jamDbInterface, $jamData;

	//Authorize user
	if($loggedInUser === false){
		return "NOT_LOGGED_IN";
	}
	
	if(!($loggedInUser->Permissions[HOST_STREAM])){
		return "PERMISSION_DENIED";
	}

	$jamModel = $jamData->GetJamByNumber($jamNumber);
	if($jamModel == null || $jamModel->JamNumber == 0){
		return "NO_JAM_TO_SUBMIT_TO";
	}

	$jamId = $jamModel->Id;
	if(!isset($jamData->JamModels[$jamId])){
		return "INVALID_JAM_ID";
	}

	if($jamData->JamModels[$jamId]->StreamerUserId){
		return "STREAMER_ALREADY_SET";
	}

	$streamerUserId = $loggedInUser->Id;
	$streamerTwitchUsername = $loggedInUser->Twitch;

	$jamDbInterface->UpdateStreamer($jamId, $streamerUserId, $streamerTwitchUsername);

	$messageService->SendMessage(LogMessage::UserLogMessage(
		"JAM_STREAMER", 
		"User $streamerUserId set themself streamer for jam $jamId with Twitch username $streamerTwitchUsername", 
		$loggedInUser->Id)
	);
	
	return "SUCCESS";
}

function PerformAction(MessageService &$messageService, &$loggedInUser){
	global $_POST;
	
	if(IsAdmin($loggedInUser) !== false){
		$jamNumber = intval($_POST[FORM_SETSTREAMER_JAM_NUMBER]);

		return SetStreamer($messageService, $jamNumber);
	}
}

?>