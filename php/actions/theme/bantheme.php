<?php

//Marks a suggested theme as banned
function BanTheme(MessageService &$messageService, $bannedThemeId){
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
	$bannedTheme = "";
	foreach($themeData->ThemeModels as $id => $themeModel) {
		if ($themeModel->Deleted != 0){
			continue;
		}
		if ($themeModel->Id == $bannedThemeId) {
			$themeAuthorUserId = $themeModel->AuthorUserId;
			$bannedTheme = $themeModel->Theme;
			$themeFound = true;
		}
	}

	if(!$themeFound){
		return "THEME_DOES_NOT_EXIST";
	}

	//Check that theme actually exists 
	$data = $themeDbInterface->SelectIfExists($bannedThemeId);

	if(mysqli_num_rows($data) == 0){
		return "THEME_DOES_NOT_EXIST";
	}

	$themeDbInterface->Ban($bannedThemeId);

	$messageService->SendMessage(LogMessage::UserLogMessage(
		"THEME_BANNED", 
		"Theme '$bannedTheme' banned", 
		$loggedInUser->Id,
		$themeAuthorUserId)
	);
	$userData->LogAdminAction($loggedInUser->Id);

	return "SUCCESS";
}

function PerformAction(MessageService &$messageService, &$loggedInUser){
	global $_POST;

	if(IsAdmin($loggedInUser) !== false){
		$bannedThemeId = $_POST[FORM_BANTHEME_THEME_ID];
		return BanTheme($messageService, $bannedThemeId);
	}
}

?>