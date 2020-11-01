<?php

function DeleteNotification(MessageService &$messageService, $notificationId){
	global $loggedInUser, $notificationDbInterface, $notificationData, $userData;

	$notificationId = intval(trim($notificationId));

	//Authorize user
	if($loggedInUser === false){
		return "NOT_LOGGED_IN";
	}

	if(IsAdmin($loggedInUser) === false){
		return "NOT_AUTHORIZED";
	}

	if(!isset($notificationData->NotificationModels[$notificationId])){
		return "UNKNOWN_NOTIFICATION";
	}

	$notificationDbInterface->SoftDelete($notificationId);

	$messageService->SendMessage(LogMessage::UserLogMessage(
		"NOTIFICATION_SOFT_DELETED", 
		"Notification $notificationId soft deleted", 
		$loggedInUser->Id)
	);
	$userData->LogAdminAction($loggedInUser->Id);

	return "SUCESS";
}

function PerformAction(MessageService &$messageService, &$loggedInUser){
	global $_POST;
	
	if($loggedInUser !== false){
		$notificationId = (isset($_POST[FORM_EDITNOTIFICATION_ID])) ? $_POST[FORM_EDITNOTIFICATION_ID] : "";

		return DeleteNotification($messageService, $notificationId);
	}
}

?>
