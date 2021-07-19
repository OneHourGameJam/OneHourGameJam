<?php

//Add a suggested theme
function AddTheme($newTheme){
	global $themeData, $configData, $jamData, $ip, $userAgent, $loggedInUser, $themeDbInterface;
	
	//Authorize user (logged in)
	if($loggedInUser === false){
		return "NOT_LOGGED_IN";
	}

	$newTheme = trim($newTheme);
	if($newTheme == ""){
		return "INVALID_THEME";
	}
	
	foreach($themeData->ActiveThemeModels as $i => $theme){
		if(strtolower($theme->Theme) == strtolower($newTheme)){
			if($theme->Banned){
				return "THEME_BANNED";
			}
			return "THEME_ALREADY_SUGGESTED";
		}
	}

	if(ThemePresenter::IsRecentTheme($jamData, $configData, $newTheme)) {
		return "THEME_RECENTLY_USED";
	}

	$themesByThisUser = 0;
	foreach($themeData->ActiveThemeModels as $i => $themeModel) {
		if ($themeModel->AuthorUserId == $loggedInUser->Id && !$themeModel->Banned) {
			$themesByThisUser++;
		}
	}
	if ($themesByThisUser >= $configData->ConfigModels[CONFIG_THEMES_PER_USER]->Value) {
		return "TOO_MANY_THEMES";
	}

	$themeDbInterface->Insert($ip, $userAgent, $newTheme, $loggedInUser->Id);

	return "SUCCESS";
}

function PerformAction(MessageService &$messageService, &$loggedInUser){
	global $_POST;

	if($loggedInUser !== false){
		$newTheme = $_POST[FORM_NEWTHEME_THEME];
		return AddTheme($newTheme);
	}
}

?>