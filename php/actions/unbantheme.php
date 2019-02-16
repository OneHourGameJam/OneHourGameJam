<?php

//Unmarks a suggested theme as banned (unbans it)
function UnbanTheme($unbannedTheme){
	global $themes, $dbConn, $ip, $userAgent, $actionResult, $loggedInUser;

	//Authorize user (logged in)
	if($loggedInUser === false){
		$actionResult = "NOT_LOGGED_IN";
		return;
	}

	//Authorize user (is admin)
	if(IsAdmin($loggedInUser) === false){
		$actionResult = "NOT_AUTHORIZED";
		return;
	}

	$unbannedTheme = trim($unbannedTheme);
	if($unbannedTheme == ""){
		$actionResult = "INVALID_THEME";
		return;
	}

	$clean_unbannedTheme = mysqli_real_escape_string($dbConn, $unbannedTheme);
	$clean_ip = mysqli_real_escape_string($dbConn, $ip);
	$clean_userAgent = mysqli_real_escape_string($dbConn, $userAgent);

	//Check that theme actually exists
	$sql = "SELECT theme_id FROM theme WHERE theme_banned = 1 AND theme_text = '$clean_unbannedTheme'";
	$data = mysqli_query($dbConn, $sql);
	$sql = "";

	if(mysqli_num_rows($data) == 0){
		$actionResult = "THEME_DOES_NOT_EXIST";
		return;
	}

	$sql = "UPDATE theme SET theme_banned = 0 WHERE theme_banned = 1 AND theme_text = '$clean_unbannedTheme'";
	$data = mysqli_query($dbConn, $sql);
	$sql = "";

    AddToAdminLog("THEME_UNBANNED", "Theme '$unbannedTheme' unbanned", "", $loggedInUser["username"]);

	$actionResult = "SUCCESS";
}

function PerformAction(&$loggedInUser){
	global $_POST;
	
	if(IsAdmin($loggedInUser) !== false){
		$unbannedTheme = $_POST["theme"];
		UnbanTheme($unbannedTheme);
	}
}

?>