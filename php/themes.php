<?php


//Fills the list of suggested themes
function LoadThemes(&$loggedInUser, &$config){
	global $dbConn;
	AddActionLog("LoadThemes");
	StartTimer("LoadThemes");
	
	$themes = Array();
	$clean_username = mysqli_real_escape_string($dbConn, $loggedInUser["username"]);

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

		if(intval($theme["theme_daysago"]) >= intval($config["THEME_DAYS_MARK_AS_OLD"]["VALUE"])){
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
		WHERE themevote_username = '$clean_username';
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

		if($votesTotal >= intval($config["THEME_MIN_VOTES_TO_SCORE"]["VALUE"])){
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
			if($count < intval($config["THEME_NUMBER_TO_MARK_TOP"]["VALUE"])){
				$themes[$i]["top_theme"] = 1;
			}
			if($count < intval($config["THEME_NUMBER_TO_MARK_KEEP"]["VALUE"]) || !$theme["has_enough_votes"]){
				$themes[$i]["keep_theme"] = 1;
			}
			$count++;
		}
	}
	StopTimer("LoadThemes");

	return $themes;
}

// Returns an array of the JAMS_CONSIDERED_RECENT most recent themes
function GetRecentThemes() {
	global $dbConn, $config;
	$escapedJamsConsideredRecent = mysqli_real_escape_string($dbConn,$config["JAMS_CONSIDERED_RECENT"]["VALUE"]);
	$sql = "
		SELECT jam_theme AS theme
		FROM jam
		WHERE jam_deleted != 1
		ORDER BY jam_jam_number DESC
		LIMIT $escapedJamsConsideredRecent
	";
	$data = mysqli_query($dbConn, $sql);
	$sql = "";
	$themes = Array();
	while ($jam = mysqli_fetch_array($data)) {
		array_push($themes, $jam["theme"]);
	}
	return $themes;
}

function RenderThemes(&$themes){
	AddActionLog("RenderThemes");
	StartTimer("RenderThemes");
	
	$render = Array();

	$render["suggested_themes"] = Array();
	
	$voteDifference = CalculateThemeSelectionProbabilityByVoteDifference($themes, $config);
	$popularity = CalculateThemeSelectionProbabilityByPopularity($themes, $config);

	$jsFormattedThemesPopularityThemeList = Array();
	$jsFormattedThemesPopularityPopularityList = Array();
	$jsFormattedThemesPopularityFillColorList = Array();
	$jsFormattedThemesPopularityBorderColorList = Array();

	foreach($themes as $i => $theme){
		$render["suggested_themes"][] = $theme;
		if(isset($theme["top_theme"]) && $theme["top_theme"]){
			$render["top_themes"][] = $theme;
		}

		if($voteDifference[$i]["ThemeSelectionProbabilityByVoteDifference"] > 0){
			$popularity = floor($voteDifference[$i]["ThemeSelectionProbabilityByVoteDifference"] * 10000) / 100;
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

	$render["js_formatted_themes_popularity_themes_list"] = implode(",", $jsFormattedThemesPopularityThemeList);
	$render["js_formatted_themes_popularity_popularity_list"] = implode(",", $jsFormattedThemesPopularityPopularityList);
	$render["js_formatted_themes_popularity_fill_color_list"] = implode(",", $jsFormattedThemesPopularityFillColorList);
	$render["js_formatted_themes_popularity_border_color_list"] = implode(",", $jsFormattedThemesPopularityBorderColorList);

	StopTimer("RenderThemes");
	return $render;
}


function GetThemesOfUserFormatted($username){
	global $dbConn;
	AddActionLog("GetThemesOfUserFormatted");
	StartTimer("GetThemesOfUserFormatted");

	$escapedUsername = mysqli_real_escape_string($dbConn, $username);
	$sql = "
		SELECT *
		FROM theme
		WHERE theme_author = '$escapedUsername';
	";
	$data = mysqli_query($dbConn, $sql);
	$sql = "";

	StopTimer("GetThemesOfUserFormatted");
	return ArrayToHTML(MySQLDataToArray($data));
}

function GetThemeVotesOfUserFormatted($username){
	global $dbConn;
	AddActionLog("GetThemeVotesOfUserFormatted");
	StartTimer("GetThemeVotesOfUserFormatted");

	$escapedUsername = mysqli_real_escape_string($dbConn, $username);
	$sql = "
		SELECT theme.theme_text, themevote.*, IF(themevote.themevote_type = 1, '-1', IF(themevote.themevote_type = 2, '0', '+1'))
		FROM themevote, theme
		WHERE theme.theme_id = themevote.themevote_theme_id
		  AND themevote_username = '$escapedUsername';
	";
	$data = mysqli_query($dbConn, $sql);
	$sql = "";

	StopTimer("GetThemeVotesOfUserFormatted");
	return ArrayToHTML(MySQLDataToArray($data));
}

function CalculateThemeSelectionProbabilityByVoteDifference(&$themes, &$config){
	AddActionLog("CalculateThemeSelectionProbabilityByVoteDifference");
	StartTimer("CalculateThemeSelectionProbabilityByVoteDifference");
	$minimumVotes = $config["THEME_MIN_VOTES_TO_SCORE"]["VALUE"];

	$result = Array();

	$totalVotesDifference = 0;
	foreach($themes as $id => $theme){
		$themeOption = Array();
		$result[$id]["ThemeSelectionProbabilityByVoteDifference"] = 0;
		$result[$id]["ThemeSelectionProbabilityByVoteDifferenceText"] = "0%";

		if(isset($theme["banned"])){
			continue;
		}

		$votesFor = $theme["votes_for"];
		$votesNeutral = $theme["votes_neutral"];
		$votesAgainst = $theme["votes_against"];
		$votesDifference = $votesFor - $votesAgainst;

		$votesTotal = $votesFor + $votesNeutral + $votesAgainst;
		$votesOpinionatedTotal = $votesFor + $votesAgainst;

		if($votesOpinionatedTotal <= 0){
			continue;
		}

		$votesPopularity = $votesFor / ($votesOpinionatedTotal);

		if($votesTotal <= 0 || $votesTotal < $minimumVotes){
			continue;
		}

		$themeOption["theme"] = $theme["theme"];
		$themeOption["votes_for"] = $votesFor;
		$themeOption["votes_difference"] = $votesDifference;
		$themeOption["popularity"] = $votesPopularity;
		$totalVotesDifference += max(0, $votesDifference);

		$availableThemes[$id] = $themeOption;
	}

	if($totalVotesDifference > 0 && count($availableThemes) > 0){
		foreach($availableThemes as $id => $availableTheme){
			$voteDifference = $availableTheme["votes_difference"];
			$selectionProbability = max(0, $voteDifference / $totalVotesDifference);
			$result[$id]["ThemeSelectionProbabilityByVoteDifference"] = $selectionProbability;
			$result[$id]["ThemeSelectionProbabilityByVoteDifferenceText"] = round($selectionProbability * 100)."%";
		}
	}
	StopTimer("CalculateThemeSelectionProbabilityByVoteDifference");
	return $result;
}

function CalculateThemeSelectionProbabilityByPopularity(&$themes, &$config){
	AddActionLog("CalculateThemeSelectionProbabilityByPopularity");
	StartTimer("CalculateThemeSelectionProbabilityByPopularity");
	$minimumVotes = $config["THEME_MIN_VOTES_TO_SCORE"]["VALUE"];
	$totalPopularity = 0;

	$result = Array();

	$totalVotesDifference = 0;
	foreach($themes as $id => $theme){
		$themeOption = Array();
		$result[$id]["ThemeSelectionProbabilityByPopularity"] = 0;
		$result[$id]["ThemeSelectionProbabilityByPopularityText"] = "0%";

		if(isset($theme["banned"])){
			continue;
		}

		$votesFor = $theme["votes_for"];
		$votesNeutral = $theme["votes_neutral"];
		$votesAgainst = $theme["votes_against"];
		$votesDifference = $votesFor - $votesAgainst;

		$votesTotal = $votesFor + $votesNeutral + $votesAgainst;
		$votesOpinionatedTotal = $votesFor + $votesAgainst;

		if($votesOpinionatedTotal <= 0){
			continue;
		}

		$votesPopularity = $votesFor / ($votesOpinionatedTotal);

		if($votesTotal <= 0 || $votesTotal < $minimumVotes){
			continue;
		}

		$themeOption["theme"] = $theme["theme"];
		$themeOption["votes_for"] = $votesFor;
		$themeOption["votes_difference"] = $votesDifference;
		$themeOption["popularity"] = $votesPopularity;
		$totalPopularity += max(0, $votesPopularity);

		$availableThemes[] = $themeOption;
	}

	if($totalPopularity > 0 && count($availableThemes) > 0){
		foreach($availableThemes as $id => $availableTheme){
			$popularity = $availableTheme["popularity"];
			$selectionProbability = max(0, $popularity / $totalPopularity);
			$result[$id]["ThemeSelectionProbabilityByPopularity"] = $selectionProbability;
			$result[$id]["ThemeSelectionProbabilityByPopularityText"] = round($selectionProbability * 100)."%";
		}
	}
	StopTimer("CalculateThemeSelectionProbabilityByPopularity");
	return $result;
}


?>
