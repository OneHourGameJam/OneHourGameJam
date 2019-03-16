<?php

//Removes an array of suggested themes
function RemoveThemes($removedThemes, $pageId){
	global $themes, $dbConn, $ip, $userAgent, $loggedInUser;
	
	//Authorize user (logged in)
	if($loggedInUser === false){
		return "NOT_LOGGED_IN";
	}

	$clean_ip = mysqli_real_escape_string($dbConn, $ip);
	$clean_userAgent = mysqli_real_escape_string($dbConn, $userAgent);

	$error = false;

	foreach($removedThemes as $removedTheme){
		$removedTheme = trim($removedTheme);
		if($removedTheme == ""){
			$error = true;
			continue;
		}

		//Get the user of the given theme
		$themeAuthor = "";
		foreach($themes as $id => $theme) {
			if ($theme["theme"] == $removedTheme) {
				$themeAuthor = $theme["author"];
			}
		}

		//Authorize user (is admin or suggested this theme originally)
		if(!isAdmin($loggedInUser) && $themeAuthor != $loggedInUser["username"]){
			return "NOT_AUTHORIZED";
		}

		$clean_removedTheme = mysqli_real_escape_string($dbConn, $removedTheme);

		//Check that theme actually exists
		$sql = "SELECT theme_id FROM theme WHERE theme_deleted != 1 AND theme_text = '$clean_removedTheme'";
		$data = mysqli_query($dbConn, $sql);
		$sql = "";
	
		if(mysqli_num_rows($data) == 0){
			$error = true;
			continue;
		}
	
		$sql = "UPDATE theme SET theme_deleted = 1 WHERE theme_deleted != 1 AND theme_text = '$clean_removedTheme'";
		$data = mysqli_query($dbConn, $sql);
		$sql = "";
	
		AddToAdminLog("THEME_SOFT_DELETED", "Theme '$removedTheme' soft deleted", "", $loggedInUser["username"]);
	}
	if($error){
		return "FAILURE";
	}

	return $pageId == "themes" ? "SUCCESS_THEMES" : "SUCCESS_MANAGETHEMES";
}

function PerformAction(&$loggedInUser){
	global $_POST;

	if(IsAdmin($loggedInUser) !== false){
		if (!isset($_POST['selected-themes'])) {
			return "NO_THEMES_SELECTED";
		}
		$deletedThemes = $_POST['selected-themes'];
		$pageId = $_POST["pageid"];
		if(!empty($deletedThemes)){
			return RemoveThemes($deletedThemes, $pageId);
		}
	}
	else{
		return "FAILURE";
	}
}

?>
