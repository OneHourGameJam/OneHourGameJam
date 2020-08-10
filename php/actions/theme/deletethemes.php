<?php

//Removes an array of suggested themes
function RemoveThemes($deletedThemeIds){
	global $ip, $userAgent, $loggedInUser, $adminLogData, $themeData, $themeDbInterface;
	
	//Authorize user (logged in)
	if($loggedInUser === false){
		return "NOT_LOGGED_IN";
	}

	//Authorize user (is admin)
	if(IsAdmin($loggedInUser) === false){
		return "NOT_AUTHORIZED";
	}

	$error = false;

	foreach($deletedThemeIds as $deletedThemeId){
		$themeAuthorUserId = -1;
		$themeFound = false;
		$removedTheme = "";

		foreach($themeData->ThemeModels as $id => $themeModel) {
			if ($themeModel->Deleted != 0){
				continue;
			}
			if ($themeModel->Id == $deletedThemeId) {
				$themeAuthorUserId = $themeModel->AuthorUserId;
				$removedTheme = $themeModel->Theme;
				$themeFound = true;
			}
		}

		if(!$themeFound){
			$error = true;
			die("1");
			continue;
		}

		//Check that theme actually exists
		$data = $themeDbInterface->SelectIfExists($deletedThemeId);

		if(mysqli_num_rows($data) == 0){
			die("2");
			$error = true;
		}

		$themeDbInterface->SoftDelete($deletedThemeId);

		$adminLogData->AddToAdminLog("THEME_SOFT_DELETED", "Theme '$removedTheme' soft deleted", $themeAuthorUserId, $loggedInUser->Id, "");
	}

	if($error){
		return "FAILURE";
	}

	return "SUCCESS";
}

function PerformAction(&$loggedInUser){
	global $_POST;

	if(IsAdmin($loggedInUser) !== false){
		if (!isset($_POST[FORM_DELETETHEMES_THEME_ID])) {
			return "NO_THEMES_SELECTED";
		}

		$deletedThemeIds = $_POST[FORM_DELETETHEMES_THEME_ID];
		
		if(empty($deletedThemeIds)){
			return "NO_THEMES_SELECTED";
		}
		
		return RemoveThemes($deletedThemeIds);
	}
	else{
		return "FAILURE";
	}
}

?>
