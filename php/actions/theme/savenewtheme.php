<?php

//Add a suggested theme
function AddTheme($newTheme){
	global $themeData, $configData, $jamData, $dbConn, $ip, $userAgent, $loggedInUser, $themeDbInterface;
	
	//Authorize user (logged in)
	if($loggedInUser === false){
		return "NOT_LOGGED_IN";
	}

	$newTheme = trim($newTheme);
	if($newTheme == ""){
		return "INVALID_THEME";
	}
	
	foreach($themeData->ThemeModels as $i => $theme){
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
	foreach($themeData->ThemeModels as $i => $themeModel) {
		if ($themeModel->AuthorUserId == $loggedInUser->Id && !$themeModel->Banned) {
			$themesByThisUser++;
		}
	}
	if ($themesByThisUser >= $configData->ConfigModels["THEMES_PER_USER"]->Value) {
		return "TOO_MANY_THEMES";
	}

	$themeDbInterface->Insert($ip, $userAgent, $newTheme, $loggedInUser->Id);

	return "SUCCESS";
}

function PerformAction(&$loggedInUser){
	global $_POST;

	if($loggedInUser !== false){
		$newTheme = $_POST["theme"];
		return AddTheme($newTheme);
	}
}

?>