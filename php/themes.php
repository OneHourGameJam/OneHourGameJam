<?php


//Fills the list of suggested themes
function LoadThemes(){
	global $themes, $dictionary, $loggedInUser, $dbConn, $config;
	IsLoggedIn();

	$clean_userName = mysqli_real_escape_string($dbConn, $loggedInUser["username"]);

	//Clear relevant lists
	$themes = Array();
	$dictionary["suggested_themes"] = Array();

	//Fill list of themes - will return same row multiple times (once for each valid themevote_type)
	$sql = "
		SELECT theme_id, theme_text, theme_author, theme_banned, themevote_type, count(themevote_id) AS themevote_count, DATEDIFF(Now(), theme_datetime) as theme_daysago
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

			continue;
		}

		$themeBtnID = preg_replace("/[^A-Za-z0-9]/", '', $theme["theme_text"]);
		$themeData = Array("theme" => htmlspecialchars($theme["theme_text"], ENT_QUOTES), "author" => $theme["theme_author"], "theme_url" => urlencode($theme["theme_text"]), "theme_button_id" => $themeBtnID, "theme_id" => $themeID);

		$themeData["votes_against"] = 0;
		$themeData["votes_neutral"] = 0;
		$themeData["votes_for"] = 0;
		$themeData["votes_report"] = 0;
		$themeData["days_ago"] = intval($theme["theme_daysago"]);

		if(intval($theme["theme_daysago"]) >= intval($config["THEME_DAYS_MARK_AS_OLD"])){
			$themeData["is_old"] = 1;
		}

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
		if(!isset($themes[$themeID])){
			continue; //voted-on theme no longer relevant (was deleted, banned, marked as old,...)
		}
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

	//Calculate popularity and apathy
	$counter = 0;
	foreach($themes as $themeID => $theme){
		$votesTotal = $themes[$themeID]["votes_for"] + $themes[$themeID]["votes_neutral"] + $themes[$themeID]["votes_against"] + $themes[$themeID]["votes_report"];
		$oppinionatedVotesTotal = $themes[$themeID]["votes_for"] + $themes[$themeID]["votes_against"];
		$unopinionatedVotesTotal = $themes[$themeID]["votes_neutral"];
		$themes[$themeID]["votes_popularity"] = "?";
		$themes[$themeID]["votes_apathy"] = "?";
		if($oppinionatedVotesTotal > 0){
			$themes[$themeID]["popularity_num"] = ($themes[$themeID]["votes_for"]) / $oppinionatedVotesTotal;
			$themes[$themeID]["apathy_num"] = 1;
			if($votesTotal > 0){
				$themes[$themeID]["apathy_num"] = $unopinionatedVotesTotal / $votesTotal;
			}
			$themes[$themeID]["votes_popularity"] = round($themes[$themeID]["popularity_num"] * 100) . "%";
			$themes[$themeID]["votes_apathy"] = round($themes[$themeID]["apathy_num"] * 100) . "%";
		}
		$themes[$themeID]["votes_total"] = $votesTotal;


		if($votesTotal >= intval($config["THEME_MIN_VOTES_TO_SCORE"])){
			$themes[$themeID]["has_enough_votes"] = true;
			if($themes[$themeID]["popularity_num"] >= 0.5){
				$themes[$themeID]["is_popular"] = 1;
				unset($themes[$themeID]["is_unpopular"]);
				$themes[$themeID]["popularity_color"] = "#".(str_pad(dechex(0xFF - (0xFF * 2 * (( $themes[$themeID]["popularity_num"]) - 0.5 ))), 2, "0", STR_PAD_LEFT))."FF00";
			}else{
				unset($themes[$themeID]["is_popular"]);
				$themes[$themeID]["is_unpopular"] = 1;
				$themes[$themeID]["popularity_color"] = "#ff".str_pad(dechex((0xFF * 2 * $themes[$themeID]["popularity_num"])), 2, "0", STR_PAD_LEFT)."00";
			}
			$themes[$themeID]["apathy_color"] = "#".str_pad(dechex(0xBB + round(0x44 * ($themes[$themeID]["apathy_num"]))), 2, "0", STR_PAD_LEFT)."DD".str_pad(dechex(0xBB + round(0x44 * (1 - $themes[$themeID]["apathy_num"]))), 2, "0", STR_PAD_LEFT);
		}else{
			$themes[$themeID]["has_enough_votes"] = false;
			$themes[$themeID]["popularity_num"] = 0;
			$themes[$themeID]["apathy_num"] = 0;
			$themes[$themeID]["votes_popularity"] = "?";
			$themes[$themeID]["votes_apathy"] = "?";
		}
		$counter++;
	}

	$user = IsAdmin();
	if($user !== false){
		usort($themes, "CmpArrayByPropertyPopularityNum");
		$count = 0;
		foreach($themes as $i => $theme){
			if($count < intval($config["THEME_NUMBER_TO_MARK_TOP"])){
				$themes[$i]["top_theme"] = 1;
			}
			if($count < intval($config["THEME_NUMBER_TO_MARK_KEEP"]) || !$theme["has_enough_votes"]){
				$themes[$i]["keep_theme"] = 1;
			}
			$count++;
		}
	}

	CalculateThemeSelectionProbabilityByVoteDifference();
	CalculateThemeSelectionProbabilityByPopularity();

	$jsFormattedThemesPopularityThemeList = Array();
	$jsFormattedThemesPopularityPopularityList = Array();
	$jsFormattedThemesPopularityFillColorList = Array();
	$jsFormattedThemesPopularityBorderColorList = Array();

	foreach($themes as $i => $theme){
		$dictionary["suggested_themes"][] = $theme;
		if(isset($theme["top_theme"]) && $theme["top_theme"]){
			$dictionary["top_themes"][] = $theme;
		}

		if($theme["ThemeSelectionProbabilityByVoteDifference"] > 0){
			$popularity = floor($theme["ThemeSelectionProbabilityByVoteDifference"] * 10000) / 100;
			$popularityUnmodified = $popularity;

			if(isset($jsFormattedThemesPopularityThemeList["".$popularity])){
				$safety = 100;
				while($safety > 0){
					$safety--;
					$popularity += 0.001;
					$isTaken = isset($jsFormattedThemesPopularityThemeList["".$popularity]);
					if(!isset($jsFormattedThemesPopularityThemeList["".$popularity])){
						break;
					}
				}
			}

			$jsFormattedThemesPopularityThemeList["".$popularity] = "\"".str_replace("\"", "\\\"", htmlspecialchars_decode($theme["theme"], ENT_COMPAT | ENT_HTML401 | ENT_QUOTES))."\"";
			$jsFormattedThemesPopularityPopularityList["".$popularity] = $popularityUnmodified;

			$randomR = 0x10 + (rand(0,14) * 0x10);
			$randomG = 0x10 + (rand(0,14) * 0x10);
			$randomB = 0x10 + (rand(0,14) * 0x10);
			$jsFormattedThemesPopularityFillColorList["".$popularity] = "'rgba(".$randomR.", ".$randomG.", ".$randomB.", 0.2)'";
			$jsFormattedThemesPopularityBorderColorList["".$popularity] = "'rgba(".$randomR.", ".$randomG.", ".$randomB.", 1)'";
		}
	}

	krsort($jsFormattedThemesPopularityThemeList);
	krsort($jsFormattedThemesPopularityPopularityList);
	krsort($jsFormattedThemesPopularityFillColorList);
	krsort($jsFormattedThemesPopularityBorderColorList);

	$dictionary["js_formatted_themes_popularity_themes_list"] = implode(",", $jsFormattedThemesPopularityThemeList);
	$dictionary["js_formatted_themes_popularity_popularity_list"] = implode(",", $jsFormattedThemesPopularityPopularityList);
	$dictionary["js_formatted_themes_popularity_fill_color_list"] = implode(",", $jsFormattedThemesPopularityFillColorList);
	$dictionary["js_formatted_themes_popularity_border_color_list"] = implode(",", $jsFormattedThemesPopularityBorderColorList);
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
			AddAuthorizationWarning("Not logged in.", false);
			return;
		}
	}

	$newTheme = trim($newTheme);
	if($newTheme == ""){
		AddDataWarning("Theme is blank", false);
		return;
	}

	foreach($themes as $i => $theme){
		if(strtolower($theme["theme"]) == strtolower($newTheme)){
			//Theme is already suggested
			AddDataWarning("This theme has already been suggested.", false);
			return;
		}
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

	LoadThemes();

	AddDataSuccess("Theme added", false);
}

//Removes a suggested theme
function RemoveTheme($removedTheme){
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

	$removedTheme = trim($removedTheme);
	if($removedTheme == ""){
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
		AddDataWarning("Theme does not exist", false);
		return;
	}

	$sql = "UPDATE theme SET theme_deleted = 1 WHERE theme_deleted != 1 AND theme_text = '$clean_removedTheme'";
	$data = mysqli_query($dbConn, $sql);
	$sql = "";
    
    AddToAdminLog("THEME_SOFT_DELETED", "Theme '$removedTheme' soft deleted", "");

	LoadThemes();

	AddDataSuccess("Theme Removed", false);
}

//Marks a suggested theme as banned
function BanTheme($bannedTheme){
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

	$bannedTheme = trim($bannedTheme);
	if($bannedTheme == ""){
		AddDataWarning("Theme is blank", false);
		return;
	}

	$clean_bannedTheme = mysqli_real_escape_string($dbConn, $bannedTheme);
	$clean_ip = mysqli_real_escape_string($dbConn, $ip);
	$clean_userAgent = mysqli_real_escape_string($dbConn, $userAgent);

	//Check that theme actually exists
	$sql = "SELECT theme_id FROM theme WHERE theme_banned != 1 AND theme_text = '$clean_bannedTheme'";
	$data = mysqli_query($dbConn, $sql);
	$sql = "";

	if(mysqli_num_rows($data) == 0){
		AddDataWarning("Theme does not exist", false);
		return;
	}

	$sql = "UPDATE theme SET theme_banned = 1 WHERE theme_banned != 1 AND theme_text = '$clean_bannedTheme'";
	$data = mysqli_query($dbConn, $sql);
	$sql = "";
    
    AddToAdminLog("THEME_BANNED", "Theme '$bannedTheme' banned", "");

	LoadThemes();

	AddDataSuccess("Theme Banned", false);
}

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


function GetThemesOfUserFormatted($username){
	global $dbConn;

	$escapedUsername = mysqli_real_escape_string($dbConn, $username);
	$sql = "
		SELECT *
		FROM theme
		WHERE theme_author = '$escapedUsername';
	";
	$data = mysqli_query($dbConn, $sql);
	$sql = "";

	return ArrayToHTML(MySQLDataToArray($data));
}

function GetThemeVotesOfUserFormatted($username){
	global $dbConn;

	$escapedUsername = mysqli_real_escape_string($dbConn, $username);
	$sql = "
		SELECT theme.theme_text, themevote.*, IF(themevote.themevote_type = 1, '-1', IF(themevote.themevote_type = 2, '0', '+1'))
		FROM themevote, theme
		WHERE theme.theme_id = themevote.themevote_theme_id
		  AND themevote_username = '$escapedUsername';
	";
	$data = mysqli_query($dbConn, $sql);
	$sql = "";

	return ArrayToHTML(MySQLDataToArray($data));
}

function CalculateThemeSelectionProbabilityByVoteDifference(){
	global $themes;
	$minimumVotes = 10;

	$selectedTheme = "";

	$totalVotesDifference = 0;
	foreach($themes as $id => $theme){
		$themeOption = Array();

		if($theme["banned"]){
			$themes[$id]["ThemeSelectionProbabilityByVoteDifference"] = 0;
			$themes[$id]["ThemeSelectionProbabilityByVoteDifferenceText"] = "0%";
			continue;
		}

		$votesFor = $theme["votes_for"];
		$votesNeutral = $theme["votes_neutral"];
		$votesAgainst = $theme["votes_against"];
		$votesDifference = $votesFor - $votesAgainst;

		$votesTotal = $votesFor + $votesNeutral + $votesAgainst;
		$votesOpinionatedTotal = $votesFor + $votesAgainst;

		if($votesOpinionatedTotal <= 0){
			$themes[$id]["ThemeSelectionProbabilityByVoteDifference"] = 0;
			$themes[$id]["ThemeSelectionProbabilityByVoteDifferenceText"] = "0%";
			continue;
		}

		$votesPopularity = $votesFor / ($votesOpinionatedTotal);

		if($votesTotal <= 0 || $votesTotal < $minimumVotes){
			$themes[$id]["ThemeSelectionProbabilityByVoteDifference"] = 0;
			$themes[$id]["ThemeSelectionProbabilityByVoteDifferenceText"] = "0%";
			continue;
		}

		$themeOption["theme"] = $theme["theme"];
		$themeOption["votes_for"] = $votesFor;
		$themeOption["votes_difference"] = $votesDifference;
		$themeOption["popularity"] = $votesPopularity;
		$totalVotesDifference += max(0, $votesDifference);

		$themes[$id]["ThemeSelectionProbabilityByVoteDifference"] = 0;
		$themes[$id]["ThemeSelectionProbabilityByVoteDifferenceText"] = "0%";
		$availableThemes[$id] = $themeOption;
	}

	if($totalVotesDifference > 0 && count($availableThemes) > 0){
		foreach($availableThemes as $id => $availableTheme){
			$voteDifference = $availableTheme["votes_difference"];
			$selectionProbability = max(0, $voteDifference / $totalVotesDifference);
			$themes[$id]["ThemeSelectionProbabilityByVoteDifference"] = $selectionProbability;
			$themes[$id]["ThemeSelectionProbabilityByVoteDifferenceText"] = round($selectionProbability * 100)."%";
		}
	}
}

function CalculateThemeSelectionProbabilityByPopularity(){
	global $themes;
	$minimumVotes = 10;
	$totalPopularity = 0;

	$selectedTheme = "";

	$totalVotesDifference = 0;
	foreach($themes as $id => $theme){
		$themeOption = Array();

		if($theme["banned"]){
			$themes[$id]["ThemeSelectionProbabilityByPopularity"] = 0;
			$themes[$id]["ThemeSelectionProbabilityByPopularityText"] = "0%";
			continue;
		}

		$votesFor = $theme["votes_for"];
		$votesNeutral = $theme["votes_neutral"];
		$votesAgainst = $theme["votes_against"];
		$votesDifference = $votesFor - $votesAgainst;

		$votesTotal = $votesFor + $votesNeutral + $votesAgainst;
		$votesOpinionatedTotal = $votesFor + $votesAgainst;

		if($votesOpinionatedTotal <= 0){
			$themes[$id]["ThemeSelectionProbabilityByPopularity"] = 0;
			$themes[$id]["ThemeSelectionProbabilityByPopularityText"] = "0%";
			continue;
		}

		$votesPopularity = $votesFor / ($votesOpinionatedTotal);

		if($votesTotal <= 0 || $votesTotal < $minimumVotes){
			$themes[$id]["ThemeSelectionProbabilityByPopularity"] = 0;
			$themes[$id]["ThemeSelectionProbabilityByPopularityText"] = "0%";
			continue;
		}

		$themeOption["theme"] = $theme["theme"];
		$themeOption["votes_for"] = $votesFor;
		$themeOption["votes_difference"] = $votesDifference;
		$themeOption["popularity"] = $votesPopularity;
		$totalPopularity += max(0, $votesPopularity);

		$themes[$id]["ThemeSelectionProbabilityByPopularity"] = 0;
		$themes[$id]["ThemeSelectionProbabilityByPopularityText"] = "0%";

		$availableThemes[] = $themeOption;
	}

	if($totalPopularity > 0 && count($availableThemes) > 0){
		foreach($availableThemes as $id => $availableTheme){
			$popularity = $availableTheme["popularity"];
			$selectionProbability = max(0, $popularity / $totalPopularity);
			$themes[$id]["ThemeSelectionProbabilityByPopularity"] = $selectionProbability;
			$themes[$id]["ThemeSelectionProbabilityByPopularityText"] = round($selectionProbability * 100)."%";
		}
	}
}


?>
