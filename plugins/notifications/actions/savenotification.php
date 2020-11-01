<?php

function SaveNotification(MessageService &$messageService, $loggedInUser, $notificationId, $notificationTitle, $notificationText, $notificationIconImageUrl, $notificationIconLinkUrl, $notificationStartDate, $notificationStartTime, $notificationEndDate, $notificationEndTime){
	global $ip, $userAgent, $notificationData, $notificationDbInterface, $userData;

	$notificationId = intval($notificationId);
	$notificationTitle = trim($notificationTitle);
	$notificationText = trim($notificationText);
	$notificationIconImageUrl = trim($notificationIconImageUrl);
	$notificationIconLinkUrl = trim($notificationIconLinkUrl);
	$notificationStartDate = trim($notificationStartDate);
	$notificationStartTime = trim($notificationStartTime);
	$notificationEndDate = trim($notificationEndDate);
	$notificationEndTime = trim($notificationEndTime);

	//Authorize user
	if($loggedInUser === false){
		return "NOT_LOGGED_IN";
	}

	if(IsAdmin($loggedInUser) === false){
		return "NOT_AUTHORIZED";
	}

	//Validate
	if(strlen($notificationTitle) < 1){
		return "MISSING_TITLE";
	}
	if(strlen($notificationText) < 1){
		return "MISSING_TEXT";
	}

	if(strlen($notificationIconImageUrl) > 0){
		$notificationIconImageUrl = SanitizeURL($notificationIconImageUrl);
	}

	if(strlen($notificationIconLinkUrl) > 0){
		$notificationIconLinkUrl = SanitizeURL($notificationIconLinkUrl);
	}

	if(strlen($notificationIconImageUrl) <= 0 && strlen($notificationIconLinkUrl) > 0){
		return "ICON_LINK_WITH_NO_IMAGE";
	}

	$startTimeString = "$notificationStartDate $notificationStartTime UTC";
	$endTimeString = "$notificationEndDate $notificationEndTime UTC";

	$startTime = strtotime($startTimeString);
	$endTime = strtotime($endTimeString);

	if($endTime <= 0){
		return "END_TIME_INVALID";
	}

	if($startTime <= 0){
		return "START_TIME_INVALID";
	}

	if($endTime <= $startTime){
		return "END_TIME_BEFORE_START_TIME";
	}


	if($endTime <= time()){
		return "END_TIME_IN_THE_PAST";
	}

	$startDateTimeFormatted = gmdate("Y-m-d H:i:00", $startTime);
	$endDateTimeFormatted = gmdate("Y-m-d H:i:00", $endTime);

	if($notificationId > 0){
		if(!isset($notificationData->NotificationModels[$notificationId])){
			return "NOTIFICATION_ID_NOT_FOUND";
		}
		
		$notificationDbInterface->Update($notificationId, $ip, $userAgent, $loggedInUser->Id, $notificationTitle, $notificationText, $notificationIconImageUrl, $notificationIconLinkUrl, $startDateTimeFormatted, $endDateTimeFormatted);

		$messageService->SendMessage(LogMessage::UserLogMessage(
			"NOTIFICATION_UPDATED", 
			"Notification $notificationId updated (title: $notificationTitle, text: $notificationText, icon image url: $notificationIconImageUrl, icon link url: $notificationIconLinkUrl, start datetime: $startDateTimeFormatted, end datetime: $endDateTimeFormatted)", 
			$loggedInUser->Id)
		);
		$userData->LogAdminAction($loggedInUser->Id);
		return "SUCESS_UPDATE";
	}else{
		$notificationDbInterface->Insert($ip, $userAgent, $loggedInUser->Id, $notificationTitle, $notificationText, $notificationIconImageUrl, $notificationIconLinkUrl, $startDateTimeFormatted, $endDateTimeFormatted);

		$messageService->SendMessage(LogMessage::UserLogMessage(
			"NOTIFICATION_ADDED", 
			"Notification added (title: $notificationTitle, text: $notificationText, icon image url: $notificationIconImageUrl, icon link url: $notificationIconLinkUrl, start datetime: $startDateTimeFormatted, end datetime: $endDateTimeFormatted)", 
			$loggedInUser->Id)
		);
		$userData->LogAdminAction($loggedInUser->Id);
		return "SUCESS_INSERT";
	}



}

function PerformAction(MessageService &$messageService, &$loggedInUser){
	global $_POST;
	
	if($loggedInUser !== false){
		$notificationId = (isset($_POST[FORM_EDITNOTIFICATION_ID])) ? $_POST[FORM_EDITNOTIFICATION_ID] : "";
		$notificationTitle = (isset($_POST[FORM_EDITNOTIFICATION_TITLE])) ? $_POST[FORM_EDITNOTIFICATION_TITLE] : "";
		$notificationText = (isset($_POST[FORM_EDITNOTIFICATION_TEXT])) ? $_POST[FORM_EDITNOTIFICATION_TEXT] : "";
		$notificationIconImageUrl = (isset($_POST[FORM_EDITNOTIFICATION_ICON_IMAGE_URL])) ? $_POST[FORM_EDITNOTIFICATION_ICON_IMAGE_URL] : "";
		$notificationIconLinkUrl = (isset($_POST[FORM_EDITNOTIFICATION_ICON_LINK_URL])) ? $_POST[FORM_EDITNOTIFICATION_ICON_LINK_URL] : "";
		$notificationStartDate = (isset($_POST[FORM_EDITNOTIFICATION_START_DATE])) ? $_POST[FORM_EDITNOTIFICATION_START_DATE] : "";
		$notificationStartTime = (isset($_POST[FORM_EDITNOTIFICATION_START_TIME])) ? $_POST[FORM_EDITNOTIFICATION_START_TIME] : "";
		$notificationEndDate = (isset($_POST[FORM_EDITNOTIFICATION_END_DATE])) ? $_POST[FORM_EDITNOTIFICATION_END_DATE] : "";
		$notificationEndTime = (isset($_POST[FORM_EDITNOTIFICATION_END_TIME])) ? $_POST[FORM_EDITNOTIFICATION_END_TIME] : "";

		return SaveNotification($messageService, $loggedInUser, $notificationId, $notificationTitle, $notificationText, $notificationIconImageUrl, $notificationIconLinkUrl, $notificationStartDate, $notificationStartTime, $notificationEndDate, $notificationEndTime);
	}
}

?>
