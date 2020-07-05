<?php

//Removes a suggested theme
function RemoveTheme($themeId, $pageId){
	global $themeData, $dbConn, $ip, $userAgent, $loggedInUser, $adminLogData;

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

	$cleanThemeId = mysqli_real_escape_string($dbConn, $themeId);
	$cleanIp = mysqli_real_escape_string($dbConn, $ip);
	$cleanUserAgent = mysqli_real_escape_string($dbConn, $userAgent);

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

	// Can be triggered from both themes and managethemes, send user to correct location.
	return $pageId == "themes" ? "SUCCESS_THEMES" : "SUCCESS_MANAGETHEMES";
}

function PerformAction(&$loggedInUser){
	global $_POST;

	$deleteThemeId = $_POST["theme_id"];
	$pageId = $_POST["pageid"];
	return RemoveTheme($deleteThemeId, $pageId);
	
}

?>