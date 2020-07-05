<?php

//Add a suggested theme
function AddTheme($newTheme){
	global $themeData, $configData, $jamData, $dbConn, $ip, $userAgent, $loggedInUser;
	
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
			return "THEME_ALREADY_SUGGESTED";
		}
	}

	if(IsRecentTheme($jamData, $configData, $newTheme)) {
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

	$clean_ip = mysqli_real_escape_string($dbConn, $ip);
	$clean_userAgent = mysqli_real_escape_string($dbConn, $userAgent);
	$clean_newTheme = mysqli_real_escape_string($dbConn, $newTheme);
	$clean_user_id = mysqli_real_escape_string($dbConn, $loggedInUser->Id);

	//Insert new theme
	$sql = "
		INSERT INTO theme
		(theme_datetime, theme_ip, theme_user_agent, theme_text, theme_author_user_id)
		VALUES (Now(), '$clean_ip', '$clean_userAgent', '$clean_newTheme', $clean_user_id);";
	$data = mysqli_query($dbConn, $sql);
	$sql = "";

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