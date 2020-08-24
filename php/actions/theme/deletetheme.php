<?php

//Removes a suggested theme
function RemoveTheme(MessageService &$messageService, $themeId, $pageId){
	global $themeData, $ip, $userAgent, $loggedInUser, $themeDbInterface, $userData;

	//Authorize user (logged in)
	if($loggedInUser === false){
		return "NOT_LOGGED_IN";
	}

	//Check that the theme exists and get the user of the given theme
	$themeAuthorUserId = -1;
	$themeFound = false;
	$removedTheme = "";
	foreach($themeData->ThemeModels as $id => $themeModel) {
		if ($themeModel->Deleted != 0){
			continue;
		}
		if ($themeModel->Id == $themeId) {
			$themeAuthorUserId = $themeModel->AuthorUserId;
			$removedTheme = $themeModel->Theme;
			$themeFound = true;
		}
	}

	if(!$themeFound){
		return "THEME_DOES_NOT_EXIST";
	}

	//Authorize user (is admin or suggested this theme originally)
	if(!isAdmin($loggedInUser) && $themeAuthorUserId != $loggedInUser->Id){
		return "NOT_AUTHORIZED";
	}

	//Check that theme actually exists
	$data = $themeDbInterface->SelectIfExists($themeId);

	if(mysqli_num_rows($data) == 0){
		return "THEME_DOES_NOT_EXIST";
	}

	$themeDbInterface->SoftDelete($themeId);

	$messageService->SendMessage(LogMessage::UserLogMessage(
		"THEME_SOFT_DELETED", 
		"Theme '$removedTheme' soft deleted", 
		$loggedInUser->Id,
		$themeAuthorUserId)
	);
	$userData->LogAdminAction($loggedInUser->Id);

	// Can be triggered from both themes and managethemes, send user to correct location.
	return $pageId == "themes" ? "SUCCESS_THEMES" : "SUCCESS_MANAGETHEMES";
}

function PerformAction(MessageService &$messageService, &$loggedInUser){
	global $_POST;

	$deleteThemeId = $_POST[FORM_DELETETHEME_THEME_ID];
	$pageId = $_POST[FORM_DELETETHEME_PAGE];
	return RemoveTheme($messageService, $deleteThemeId, $pageId);
	
}

?>