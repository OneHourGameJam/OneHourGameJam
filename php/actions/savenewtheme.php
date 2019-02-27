<?php

//Add a suggested theme
function AddTheme($newTheme, $isBot){
	global $themes, $dbConn, $ip, $userAgent, $actionResult;

	if($isBot){
		$user = "bot";
	}else{
		//Authorize user (logged in)
		$user = IsLoggedIn();
		if($user === false){
			$actionResult = "NOT_LOGGED_IN";
			AddAuthorizationWarning("Not logged in.", false);
			return;
		}
	}

	$newTheme = trim($newTheme);
	if($newTheme == ""){
		$actionResult = "INVALID_THEME";
		AddDataWarning("Theme is blank", false);
		return;
	}

	foreach($themes as $i => $theme){
		if(strtolower($theme["theme"]) == strtolower($newTheme)){
			//Theme is already suggested
			$actionResult = "THEME_ALREADY_SUGGESTED";
			AddDataWarning("This theme has already been suggested.", false);
			return;
		}
	}

	$recentThemes = GetRecentThemes();
	if (in_array($newTheme, $recentThemes)) {
		$actionResult = "THEME_RECENTLY_USED";
		AddDataWarning("This theme has been used in a recent jam.", false);
		return;
	}

	$clean_ip = mysqli_real_escape_string($dbConn, $ip);
	$clean_userAgent = mysqli_real_escape_string($dbConn, $userAgent);
	$clean_newTheme = mysqli_real_escape_string($dbConn, $newTheme);
	$clean_userName = mysqli_real_escape_string($dbConn, $user["username"]);

	//Insert new theme
	$sql = "
		INSERT INTO theme
		(theme_datetime, theme_ip, theme_user_agent, theme_text, theme_author)
		VALUES (Now(), '$clean_ip', '$clean_userAgent', '$clean_newTheme', '$clean_userName');";
	$data = mysqli_query($dbConn, $sql);
	$sql = "";

	$actionResult = "SUCCESS";
	LoadThemes();

	AddDataSuccess("Theme added", false);
}

if(IsLoggedIn()){
    $newTheme = $_POST["theme"];
    AddTheme($newTheme, false);
}

?>