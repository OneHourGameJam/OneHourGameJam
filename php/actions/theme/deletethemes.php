<?php

//Removes an array of suggested themes
function RemoveThemes($deletedThemeIds){
	global $dbConn, $ip, $userAgent, $loggedInUser, $adminLogData, $themeData;
	
	//Authorize user (logged in)
	if($loggedInUser === false){
		return "NOT_LOGGED_IN";
	}

	//Authorize user (is admin)
	if(IsAdmin($loggedInUser) === false){
		return "NOT_AUTHORIZED";
	}

	$clean_ip = mysqli_real_escape_string($dbConn, $ip);
	$clean_userAgent = mysqli_real_escape_string($dbConn, $userAgent);

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
			continue;
		}

		$cleanThemeId = mysqli_real_escape_string($dbConn, $deletedThemeId);

		//Check that theme actually exists
		$sql = "SELECT theme_id FROM theme WHERE theme_deleted != 1 AND theme_id = '$cleanThemeId'";
		$data = mysqli_query($dbConn, $sql);
		$sql = "";

		if(mysqli_num_rows($data) == 0){
			return "THEME_DOES_NOT_EXIST";
		}

		$sql = "UPDATE theme SET theme_deleted = 1 WHERE theme_deleted != 1 AND theme_id = '$cleanThemeId'";
		$data = mysqli_query($dbConn, $sql);
		$sql = "";

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
		if (!isset($_POST['selected-themes'])) {
			return "NO_THEMES_SELECTED";
		}

		$deletedThemeIds = $_POST['selected-themes'];
		
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
