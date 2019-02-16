<?php

//Removes a suggested theme
function RemoveTheme($removedTheme){
	global $themes, $dbConn, $ip, $userAgent, $actionResult, $loggedInUser;

	//Authorize user (logged in)
	if($loggedInUser === false){
		$actionResult = "NOT_LOGGED_IN";
		AddAuthorizationWarning("Not logged in.", false);
		return;
	}

	//Authorize user (is admin)
	if(IsAdmin($loggedInUser) === false){
		$actionResult = "NOT_AUTHORIZED";
		AddAuthorizationWarning("Only admins can delete themes.", false);
		return;
	}

	$removedTheme = trim($removedTheme);
	if($removedTheme == ""){
		$actionResult = "INVALID_THEME";
		AddDataWarning("Theme is blank", false);
		return;
	}

	$clean_removedTheme = mysqli_real_escape_string($dbConn, $removedTheme);
	$clean_ip = mysqli_real_escape_string($dbConn, $ip);
	$clean_userAgent = mysqli_real_escape_string($dbConn, $userAgent);

	//Check that theme actually exists
	$sql = "SELECT theme_id FROM theme WHERE theme_deleted != 1 AND theme_text = '$clean_removedTheme'";
	$data = mysqli_query($dbConn, $sql);
	$sql = "";

	if(mysqli_num_rows($data) == 0){
		$actionResult = "THEME_DOES_NOT_EXIST";
		AddDataWarning("Theme does not exist", false);
		return;
	}

	$sql = "UPDATE theme SET theme_deleted = 1 WHERE theme_deleted != 1 AND theme_text = '$clean_removedTheme'";
	$data = mysqli_query($dbConn, $sql);
	$sql = "";

    AddToAdminLog("THEME_SOFT_DELETED", "Theme '$removedTheme' soft deleted", "");

	$actionResult = "SUCCESS";
	LoadThemes();

	AddDataSuccess("Theme Removed", false);
}

if(IsAdmin($loggedInUser) !== false){
    $deletedTheme = $_POST["theme"];
    RemoveTheme($deletedTheme);
}

?>