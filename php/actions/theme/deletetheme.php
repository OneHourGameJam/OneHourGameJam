<?php

//Removes a suggested theme
function RemoveTheme($removedTheme, $pageId){
	global $themes, $dbConn, $ip, $userAgent, $loggedInUser;

	//Authorize user (logged in)
	if($loggedInUser === false){
		return "NOT_LOGGED_IN";
	}

	//Check that the theme exists and get the user of the given theme
	$themeAuthor = "";
	foreach($themes as $id => $theme) {
		if ($theme["theme_deleted"] != 0){
			continue;
		}
		if ($theme["banned"] != 0){
			continue;
		}
		if ($theme["theme"] == $removedTheme) {
			$themeAuthor = $theme["author"];
		}
	}

	if($themeAuthor == ""){
		return "THEME_DOES_NOT_EXIST";
	}

	//Authorize user (is admin or suggested this theme originally)
	if(!isAdmin($loggedInUser) && $themeAuthor != $loggedInUser->Username){
		return "NOT_AUTHORIZED";
	}

	$removedTheme = trim($removedTheme);
	if($removedTheme == ""){
		return "INVALID_THEME";
	}

	$clean_removedTheme = mysqli_real_escape_string($dbConn, $removedTheme);
	$clean_ip = mysqli_real_escape_string($dbConn, $ip);
	$clean_userAgent = mysqli_real_escape_string($dbConn, $userAgent);

	//Check that theme actually exists
	$sql = "SELECT theme_id FROM theme WHERE theme_deleted != 1 AND theme_text = '$clean_removedTheme'";
	$data = mysqli_query($dbConn, $sql);
	$sql = "";

	if(mysqli_num_rows($data) == 0){
		return "THEME_DOES_NOT_EXIST";
	}

	$sql = "UPDATE theme SET theme_deleted = 1 WHERE theme_deleted != 1 AND theme_text = '$clean_removedTheme'";
	$data = mysqli_query($dbConn, $sql);
	$sql = "";

    AddToAdminLog("THEME_SOFT_DELETED", "Theme '$removedTheme' soft deleted", "", $loggedInUser->Username);

	// Can be triggered from both themes and managethemes, send user to correct location.
	return $pageId == "themes" ? "SUCCESS_THEMES" : "SUCCESS_MANAGETHEMES";
}

function PerformAction(&$loggedInUser){
	global $_POST;

	$deletedTheme = $_POST["theme"];
	$pageId = $_POST["pageid"];
	return RemoveTheme($deletedTheme, $pageId);
	
}

?>