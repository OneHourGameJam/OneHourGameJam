<?php

//Unmarks a suggested theme as banned (unbans it)
function UnbanTheme(MessageService &$messageService, $unbannedThemeId){
	global $ip, $userAgent, $loggedInUser, $themeData, $themeDbInterface, $userData;

	//Authorize user (logged in)
	if($loggedInUser === false){
		return "NOT_LOGGED_IN";
	}

	//Authorize user (is admin)
	if(IsAdmin($loggedInUser) === false){
		return "NOT_AUTHORIZED";
	}

	$themeAuthorUserId = -1;
	$themeFound = false;
	$unbannedTheme = "";
	foreach($themeData->ThemeModels as $id => $themeModel) {
		if ($themeModel->Deleted != 0){
			continue;
		}
		if ($themeModel->Id == $unbannedThemeId) {
			$themeAuthorUserId = $themeModel->AuthorUserId;
			$unbannedTheme = $themeModel->Theme;
			$themeFound = true;
		}
	}

	if(!$themeFound){
		return "THEME_DOES_NOT_EXIST";
	}

	//Check that theme actually exists
	$data = $themeDbInterface->SelectIfExists($unbannedThemeId);

	if(mysqli_num_rows($data) == 0){
		return "THEME_DOES_NOT_EXIST";
	}

	$themeDbInterface->Unban($unbannedThemeId);

	$messageService->SendMessage(LogMessage::UserLogMessage(
		"THEME_UNBANNED", 
		"Theme '$unbannedTheme' unbanned", 
		$loggedInUser->Id,
		$themeAuthorUserId)
	);
	$userData->LogAdminAction($loggedInUser->Id);

	return "SUCCESS";
}

function PerformAction(MessageService &$messageService, &$loggedInUser){
	global $_POST;
	
	if(IsAdmin($loggedInUser) !== false){
		$unbannedThemeId = $_POST[FORM_UNBANTHEME_THEME_ID];
		return UnbanTheme($messageService, $unbannedThemeId);
	}
}

?>