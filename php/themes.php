<?php

//Fills the list of suggested themes
function LoadThemes(){
	global $themes, $dictionary, $loggedInUser, $dbConn;
	IsLoggedIn();
	
	$clean_userName = mysqli_real_escape_string($dbConn, $loggedInUser["username"]);
	
	//Clear relevant lists
	$themes = Array();
	$dictionary["suggested_themes"] = Array();
	
	//Fill list of themes - will return same row multiple times (once for each valid themevote_type)
	$sql = "
		SELECT theme_id, theme_text, theme_author, theme_banned, themevote_type, count(themevote_id) AS themevote_count
		FROM (theme LEFT JOIN themevote ON (themevote.themevote_theme_id = theme.theme_id))
		WHERE theme_deleted != 1
		GROUP BY theme_id, themevote_type
		ORDER BY theme_banned ASC, theme_id ASC
	";
	$data = mysqli_query($dbConn, $sql);
	$sql = "";
	
	//Fill dictionary with non-banned themes
	while($theme = mysqli_fetch_array($data)){
		$themeID = $theme["theme_id"];
		if(isset($themes[$themeID])){
			//Theme already processed, simply log numbers for vote type
			switch($theme["themevote_type"]){
				case "1":
					$themes[$themeID]["votes_against"] = intval($theme["themevote_count"]);
				break;
				case "2":
					$themes[$themeID]["votes_neutral"] = intval($theme["themevote_count"]);
				break;
				case "3":
					$themes[$themeID]["votes_for"] = intval($theme["themevote_count"]);
				break;
			}
		
			$votesTotal = $themes[$themeID]["votes_for"] + $themes[$themeID]["votes_neutral"] + $themes[$themeID]["votes_against"] + $themes[$themeID]["votes_report"];
			$oppinionatedVotesTotal = $themes[$themeID]["votes_for"] + $themes[$themeID]["votes_against"];
			$themes[$themeID]["votes_popularity"] = "?";
			if($oppinionatedVotesTotal > 0){
				$themes[$themeID]["votes_popularity"] = round(($themes[$themeID]["votes_for"] * 100) / $oppinionatedVotesTotal) . "%";
			}
			$themes[$themeID]["votes_total"] = $votesTotal;
			
			continue;
		}
		
		$themeBtnID = preg_replace("/[^A-Za-z0-9]/", '', $theme["theme_text"]);
		$themeData = Array("theme" => htmlspecialchars($theme["theme_text"], ENT_QUOTES), "author" => $theme["theme_author"], "theme_url" => urlencode($theme["theme_text"]), "theme_button_id" => $themeBtnID, "theme_id" => $themeID);
		
		$themeData["votes_against"] = 0;
		$themeData["votes_neutral"] = 0;
		$themeData["votes_for"] = 0;
					
		switch($theme["themevote_type"]){
			case "1":
				$themeData["votes_against"] = intval($theme["themevote_count"]);
			break;
			case "2":
				$themeData["votes_neutral"] = intval($theme["themevote_count"]);
			break;
			case "3":
				$themeData["votes_for"] = intval($theme["themevote_count"]);
			break;
		}
		
		if($theme["theme_banned"] == 1){
			$themeData["banned"] = 1;
			if(IsAdmin()){
				$themeData["theme_visible"] = 1;
			}
		}else{
			$themeData["theme_visible"] = 1;
		}
		
		$votesTotal = $themeData["votes_for"] + $themeData["votes_neutral"] + $themeData["votes_against"] + $themeData["votes_report"];
		$oppinionatedVotesTotal = $themeData["votes_for"] + $themeData["votes_against"];
		$themeData["votes_popularity"] = "?";
		if($oppinionatedVotesTotal > 0){
			$themeData["votes_popularity"] = round(($themeData["votes_for"] * 100) / $oppinionatedVotesTotal) . "%";
		}
		$themeData["votes_total"] = $votesTotal;
		$themes[$themeID] = $themeData;
	}
	
	//Update themes with what the user voted for
	$sql = "
		SELECT themevote_theme_id, themevote_type
		FROM themevote
		WHERE themevote_username = '$clean_userName';
	";
	$data = mysqli_query($dbConn, $sql);
	$sql = "";
	
	while($theme = mysqli_fetch_array($data)){
		$themeID = $theme["themevote_theme_id"];
		switch($theme["themevote_type"]){
			case "1":
				$themes[$themeID]["user_vote_against"] = 1;
			break;
			case "2":
				$themes[$themeID]["user_vote_neutral"] = 1;
			break;
			case "3":
				$themes[$themeID]["user_vote_for"] = 1;
			break;
		}
	}
	
	
	foreach($themes as $i => $theme){
		$dictionary["suggested_themes"][] = $theme;
	}
}

//Add a suggested theme
function AddTheme($newTheme, $isBot){
	global $themes, $dbConn, $ip, $userAgent;
	
	if($isBot){
		$user = "bot";
	}else{
		//Authorize user (logged in)
		$user = IsLoggedIn();
		if($user === false){
			die("Not logged in.");
		}
	}
	
	$newTheme = trim($newTheme);
	if($newTheme == ""){
		die("Theme is blank");
	}
	
	foreach($themes as $i => $theme){
		if(strtolower($theme["theme"]) == strtolower($newTheme)){
			//Theme is already suggested
			die("This theme has already been suggested.");
			return;
		}
	}
	
	$clean_ip = mysqli_real_escape_string($dbConn, $ip);
	$clean_userAgent = mysqli_real_escape_string($dbConn, $userAgent);
	$clean_newTheme = mysqli_real_escape_string($dbConn, $newTheme);
	$clean_userName = mysqli_real_escape_string($dbConn, $user["username"]);
	
	
	//Check if the theme exists already and is deleted
	$sql = "SELECT theme_id FROM theme WHERE theme_deleted = 1 AND theme_text = '$clean_newTheme'";
	$data = mysqli_query($dbConn, $sql);
	$sql = "";
	
	if(mysqli_num_rows($data) == 0){
		//Insert new theme
		$sql = "
			INSERT INTO theme
			(theme_datetime, theme_ip, theme_user_agent, theme_text, theme_author)
			VALUES (Now(), '$clean_ip', '$clean_userAgent', '$clean_newTheme', '$clean_userName');";
		$data = mysqli_query($dbConn, $sql);
		$sql = "";
	}else{
		//Undelete theme
		$sql = "UPDATE theme SET theme_deleted = 0 WHERE theme_deleted = 1 AND theme_text = '$clean_newTheme'";
		$data = mysqli_query($dbConn, $sql);
		$sql = "";
	}
	
	LoadThemes();
}

//Removes a suggested theme
function RemoveTheme($removedTheme){
	global $themes, $dbConn, $ip, $userAgent;
	
	//Authorize user (logged in)
	$user = IsAdmin();
	if($user === false){
		die("Not logged in.");
	}
	
	//Authorize user (is admin)
	if(IsAdmin() === false){
		die("Only admins can delete themes.");
	}
	
	$removedTheme = trim($removedTheme);
	if($removedTheme == ""){
		die("Theme is blank");
	}
	
	$clean_removedTheme = mysqli_real_escape_string($dbConn, $removedTheme);
	$clean_ip = mysqli_real_escape_string($dbConn, $ip);
	$clean_userAgent = mysqli_real_escape_string($dbConn, $userAgent);
	
	//Check that theme actually exists
	$sql = "SELECT theme_id FROM theme WHERE theme_deleted != 1 AND theme_text = '$clean_removedTheme'";
	$data = mysqli_query($dbConn, $sql);
	$sql = "";
	
	if(mysqli_num_rows($data) == 0){
		die("Theme does not exist");
	}
	
	$sql = "UPDATE theme SET theme_deleted = 1 WHERE theme_deleted != 1 AND theme_text = '$clean_removedTheme'";
	$data = mysqli_query($dbConn, $sql);
	$sql = "";
	
	LoadThemes();
}

//Marks a suggested theme as banned
function BanTheme($bannedTheme){
	global $themes, $dbConn, $ip, $userAgent;
	
	//Authorize user (logged in)
	$user = IsAdmin();
	if($user === false){
		die("Not logged in.");
	}
	
	//Authorize user (is admin)
	if(IsAdmin() === false){
		die("Only admins can delete themes.");
	}
	
	$bannedTheme = trim($bannedTheme);
	if($bannedTheme == ""){
		die("Theme is blank");
	}
	
	$clean_bannedTheme = mysqli_real_escape_string($dbConn, $bannedTheme);
	$clean_ip = mysqli_real_escape_string($dbConn, $ip);
	$clean_userAgent = mysqli_real_escape_string($dbConn, $userAgent);
	
	//Check that theme actually exists
	$sql = "SELECT theme_id FROM theme WHERE theme_banned != 1 AND theme_text = '$clean_bannedTheme'";
	$data = mysqli_query($dbConn, $sql);
	$sql = "";
	
	if(mysqli_num_rows($data) == 0){
		die("Theme does not exist");
	}
	
	$sql = "UPDATE theme SET theme_banned = 1 WHERE theme_banned != 1 AND theme_text = '$clean_bannedTheme'";
	$data = mysqli_query($dbConn, $sql);
	$sql = "";
	
	LoadThemes();
}

//Unmarks a suggested theme as banned (unbans it)
function UnbanTheme($unbannedTheme){
	global $themes, $dbConn, $ip, $userAgent;
	
	//Authorize user (logged in)
	$user = IsAdmin();
	if($user === false){
		die("Not logged in.");
	}
	
	//Authorize user (is admin)
	if(IsAdmin() === false){
		die("Only admins can delete themes.");
	}
	
	$unbannedTheme = trim($unbannedTheme);
	if($unbannedTheme == ""){
		die("Theme is blank");
	}
	
	$clean_unbannedTheme = mysqli_real_escape_string($dbConn, $unbannedTheme);
	$clean_ip = mysqli_real_escape_string($dbConn, $ip);
	$clean_userAgent = mysqli_real_escape_string($dbConn, $userAgent);
	
	//Check that theme actually exists
	$sql = "SELECT theme_id FROM theme WHERE theme_banned = 1 AND theme_text = '$clean_unbannedTheme'";
	$data = mysqli_query($dbConn, $sql);
	$sql = "";
	
	if(mysqli_num_rows($data) == 0){
		die("Theme is not banned");
	}
	
	$sql = "UPDATE theme SET theme_banned = 0 WHERE theme_banned = 1 AND theme_text = '$clean_unbannedTheme'";
	$data = mysqli_query($dbConn, $sql);
	$sql = "";
	
	LoadThemes();
}



?>