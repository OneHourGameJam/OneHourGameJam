<?php

//Unmarks a suggested theme as banned (unbans it)
function UnbanTheme($unbannedTheme){
	global $themes, $dbConn, $ip, $userAgent;

	//Authorize user (logged in)
	$user = IsAdmin();
	if($user === false){
		AddAuthorizationWarning("Not logged in.", false);
		return;
	}

	//Authorize user (is admin)
	if(IsAdmin() === false){
		AddAuthorizationWarning("Only admins can delete themes.", false);
		return;
	}

	$unbannedTheme = trim($unbannedTheme);
	if($unbannedTheme == ""){
		AddDataWarning("Theme is blank", false);
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
		AddDataWarning("Theme is not banned", false);
		return;
	}

	$sql = "UPDATE theme SET theme_banned = 0 WHERE theme_banned = 1 AND theme_text = '$clean_unbannedTheme'";
	$data = mysqli_query($dbConn, $sql);
	$sql = "";
    
    AddToAdminLog("THEME_UNBANNED", "Theme '$unbannedTheme' unbanned", ""); 

	LoadThemes();

	AddDataSuccess("Theme Unbanned", false);
}

if(IsAdmin()){
    $unbannedTheme = $_POST["theme"];
    UnbanTheme($unbannedTheme);
}

?>