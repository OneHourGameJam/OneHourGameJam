<?php

//Unmarks a suggested theme as banned (unbans it)
function UnbanTheme($unbannedThemeId){
	global $ip, $userAgent, $loggedInUser, $adminLogData, $themeData, $themeDbInterface;

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

    $adminLogData->AddToAdminLog("THEME_UNBANNED", "Theme '$unbannedTheme' unbanned", $themeAuthorUserId, $loggedInUser->Id, "");

	return "SUCCESS";
}

function PerformAction(&$loggedInUser){
	global $_POST;
	
	if(IsAdmin($loggedInUser) !== false){
		$unbannedThemeId = $_POST[FORM_UNBANTHEME_THEME_ID];
		return UnbanTheme($unbannedThemeId);
	}
}

?>