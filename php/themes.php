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
		$themeData = Array(
			"theme" => htmlspecialchars($theme["theme_text"], ENT_QUOTES),
			"author" => $theme["theme_author"], 
			"theme_url" => urlencode($theme["theme_text"]), 
			"theme_button_id" => $themeBtnID, 
			"theme_id" => $themeID);

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

function RenderThemes(&$themes, &$config){
	AddActionLog("RenderThemes");
	StartTimer("RenderThemes");
	
	$render = Array();

	$render["suggested_themes"] = Array();
	
	$voteDifference = CalculateThemeSelectionProbabilityByVoteDifference($themes, $config);
	$popularityArray = CalculateThemeSelectionProbabilityByPopularity($themes, $config);

	$jsFormattedThemesPopularityThemeList = Array();
	$jsFormattedThemesPopularityPopularityList = Array();
	$jsFormattedThemesPopularityFillColorList = Array();
	$jsFormattedThemesPopularityBorderColorList = Array();

	$themesUserHasNotVotedFor = 0;

	foreach($themes as $i => $themeData){
		//print $i."<br>";

		$themeID = intval($themeData["theme_id"]);

		$theme = Array();
		$theme["theme"] = $themeData["theme"];
		$theme["theme_visible"] = $themeData["theme_visible"];
		$theme["votes_for"] = $themeData["votes_for"];
		$theme["votes_neutral"] = $themeData["votes_neutral"];
		$theme["votes_against"] = $themeData["votes_against"];
		$theme["votes_report"] = $themeData["votes_report"];
		$theme["votes_total"] = $themeData["votes_total"];
		$theme["votes_popularity"] = $themeData["votes_popularity"];
		$theme["votes_apathy"] = $themeData["votes_apathy"];
		$theme["popularity_num"] = $themeData["popularity_num"];
		$theme["apathy_num"] = $themeData["apathy_num"];
		$theme["has_enough_votes"] = $themeData["has_enough_votes"];
		$theme["is_popular"] = $themeData["is_popular"];
		$theme["top_theme"] = $themeData["top_theme"];
		$theme["keep_theme"] = $themeData["keep_theme"];
		$theme["is_unpopular"] = $themeData["is_unpopular"];
		$theme["apathy_color"] = $themeData["apathy_color"];
		$theme["popularity_color"] = $themeData["popularity_color"];
		$theme["banned"] = $themeData["banned"];
		$theme["theme_button_id"] = $themeData["theme_button_id"];
		$theme["author"] = $themeData["author"];
		$theme["theme_url"] = $themeData["theme_url"];
		$theme["theme_id"] = $themeID;
		$theme["user_vote_for"] = $themeData["user_vote_for"];
		$theme["user_vote_neutral"] = $themeData["user_vote_neutral"];
		$theme["user_vote_against"] = $themeData["user_vote_against"];
		$theme["ThemeSelectionProbabilityByVoteDifferenceText"] = $voteDifference[$themeID]["ThemeSelectionProbabilityByVoteDifferenceText"];
		$theme["ThemeSelectionProbabilityByPopularityText"] = $popularityArray[$themeID]["ThemeSelectionProbabilityByPopularityText"];
		$theme["days_ago"] = $themeData["days_ago"];
		
		if(isset($themeData["top_theme"]) && $themeData["top_theme"]){
			$themeData["top_themes"][] = $themeData;
		}

		if($voteDifference[$themeID]["ThemeSelectionProbabilityByVoteDifference"] > 0){
			$popularity = floor($voteDifference[$themeID]["ThemeSelectionProbabilityByVoteDifference"] * 10000) / 100;
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

		if(!isset($themeData["banned"]) && !isset($themeData["user_vote_for"]) && !isset($themeData["user_vote_neutral"]) && !isset($themeData["user_vote_against"])){
			$themesUserHasNotVotedFor++;
		}
		
		$render["suggested_themes"][] = $theme;
	}

	if($themesUserHasNotVotedFor != 0){
		$render["user_has_not_voted_for_all_themes"] = 1;
		$render["themes_user_has_not_voted_for"] = $themesUserHasNotVotedFor;
		if($themesUserHasNotVotedFor != 0 && $themesUserHasNotVotedFor != 1){
			$render["themes_user_has_not_voted_for_plural"] = 1;
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
		$themeID = $theme["theme_id"];
		$result[$themeID]["ThemeSelectionProbabilityByVoteDifference"] = 0;
		$result[$themeID]["ThemeSelectionProbabilityByVoteDifferenceText"] = "0%";

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

		$availableThemes[$themeID] = $themeOption;
	}

	if($totalVotesDifference > 0 && count($availableThemes) > 0){
		foreach($availableThemes as $themeID => $availableTheme){
			$voteDifference = $availableTheme["votes_difference"];
			$selectionProbability = max(0, $voteDifference / $totalVotesDifference);
			$result[$themeID]["ThemeSelectionProbabilityByVoteDifference"] = $selectionProbability;
			$result[$themeID]["ThemeSelectionProbabilityByVoteDifferenceText"] = round($selectionProbability * 100)."%";
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
		$themeID = $theme["theme_id"];
		$result[$themeID]["ThemeSelectionProbabilityByPopularity"] = 0;
		$result[$themeID]["ThemeSelectionProbabilityByPopularityText"] = "0%";

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

		$availableThemes[$themeID] = $themeOption;
	}

	if($totalPopularity > 0 && count($availableThemes) > 0){
		foreach($availableThemes as $themeID => $availableTheme){
			$popularity = $availableTheme["popularity"];
			$selectionProbability = max(0, $popularity / $totalPopularity);
			$result[$themeID]["ThemeSelectionProbabilityByPopularity"] = $selectionProbability;
			$result[$themeID]["ThemeSelectionProbabilityByPopularityText"] = round($selectionProbability * 100)."%";
		}
	}
	StopTimer("CalculateThemeSelectionProbabilityByPopularity");
	return $result;
}


?>
