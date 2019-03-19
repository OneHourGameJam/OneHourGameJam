<?php

//Marks a suggested theme as banned
function BanTheme($bannedTheme){
	global $themes, $dbConn, $ip, $userAgent, $loggedInUser;

	//Authorize user (logged in)
	if($loggedInUser === false){
		return "NOT_LOGGED_IN";
	}

	//Authorize user (is admin)
	if(IsAdmin($loggedInUser) === false){
		return "NOT_AUTHORIZED";
	}

	$bannedTheme = trim($bannedTheme);
	if($bannedTheme == ""){
		return "INVALID_THEME";
	}

	$clean_bannedTheme = mysqli_real_escape_string($dbConn, $bannedTheme);
	$clean_ip = mysqli_real_escape_string($dbConn, $ip);
	$clean_userAgent = mysqli_real_escape_string($dbConn, $userAgent);

	//Check that theme actually exists
	$sql = "SELECT theme_id FROM theme WHERE theme_banned != 1 AND theme_text = '$clean_bannedTheme'";
	$data = mysqli_query($dbConn, $sql);
	$sql = "";

	if(mysqli_num_rows($data) == 0){
		return "THEME_DOES_NOT_EXIST";
	}

	$sql = "UPDATE theme SET theme_banned = 1 WHERE theme_banned != 1 AND theme_text = '$clean_bannedTheme'";
	$data = mysqli_query($dbConn, $sql);
	$sql = "";

    AddToAdminLog("THEME_BANNED", "Theme '$bannedTheme' banned", "", $loggedInUser->Username);

	return "SUCCESS";
}

function PerformAction(&$loggedInUser){
	global $_POST;

	if(IsAdmin($loggedInUser) !== false){
		$bannedTheme = $_POST["theme"];
		return BanTheme($bannedTheme);
	}
}

?>