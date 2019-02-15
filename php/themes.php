<?php

//Fills the list of suggested themes
function LoadThemes(){
	global $dbConn;
	AddActionLog("LoadThemes");
	StartTimer("LoadThemes");
	
	$themes = Array();

	//Fill list of themes - will return same theme row multiple times (once for each valid themevote_type)
	$sql = "
		SELECT theme_id, theme_text, theme_author, theme_banned, themevote_type, count(themevote_id) AS themevote_count, DATEDIFF(Now(), theme_datetime) as theme_daysago
		FROM (theme LEFT JOIN themevote ON (themevote.themevote_theme_id = theme.theme_id))
		WHERE theme_deleted != 1
		GROUP BY theme_id, themevote_type
		ORDER BY theme_banned ASC, theme_id ASC
	";
	$data = mysqli_query($dbConn, $sql);
	$sql = "";

	while($theme = mysqli_fetch_array($data)){
		$themeID = $theme["theme_id"];
		if(isset($themes[$themeID])){
			//Theme already processed, simply log numbers for vote type
			$themeVoteKey = ThemeVoteTypeToKey($theme["themevote_type"]);
			$themes[$themeID][$themeVoteKey] = intval($theme["themevote_count"]);
			continue;
		}

		$themeData = Array();
		$themeData["theme_id"] = $themeID;
		$themeData["theme"] = htmlspecialchars($theme["theme_text"], ENT_QUOTES);
		$themeData["author"] = $theme["theme_author"];
		$themeData["banned"] = $theme["theme_banned"];
		$themeData["votes_against"] = 0;
		$themeData["votes_neutral"] = 0;
		$themeData["votes_for"] = 0;
		$themeData["votes_report"] = 0;
		$themeData["days_ago"] = intval($theme["theme_daysago"]);

		$themeVoteKey = ThemeVoteTypeToKey($theme["themevote_type"]);
		$themeData[$themeVoteKey] = intval($theme["themevote_count"]);

		$themes[$themeID] = $themeData;
	}
	StopTimer("LoadThemes");

	return $themes;
}

function LoadUserThemeVotes(&$loggedInUser){
	global $dbConn;
	AddActionLog("LoadUserThemeVotes");
	StartTimer("LoadUserThemeVotes");
	$userThemeVotes = Array();

	$clean_username = mysqli_real_escape_string($dbConn, $loggedInUser["username"]);

	//Update themes with what the user voted for
	$sql = "
		SELECT themevote_theme_id, themevote_type
		FROM themevote
		WHERE themevote_username = '$clean_username';
	";
	$data = mysqli_query($dbConn, $sql);
	$sql = "";

	while($themeVote = mysqli_fetch_array($data)){
		$themeVoteData = Array();

		$themeID = $themeVote["themevote_theme_id"];
		$userThemeVoteType = $themeVote["themevote_type"];
		$userThemeVotes[$themeID] = $userThemeVoteType;
	}

	StopTimer("LoadUserThemeVotes");
	return $userThemeVotes;
}

function RenderThemes(&$themes, &$userThemeVotes, &$themesByVoteDifference, &$themesByPopularity, &$config){
	AddActionLog("RenderThemes");
	StartTimer("RenderThemes");
	
	$render = Array();

	$render["suggested_themes"] = Array();

	$jsFormattedThemesPopularityThemeList = Array();
	$jsFormattedThemesPopularityPopularityList = Array();
	$jsFormattedThemesPopularityFillColorList = Array();
	$jsFormattedThemesPopularityBorderColorList = Array();

	$themesUserHasNotVotedFor = 0;

	foreach($themes as $i => $themeData){
		//print $i."<br>";

		$themeID = intval($themeData["theme_id"]);
		$themeText = $themeData["theme"];
		$banned = $themeData["banned"];
		$votesFor = $themeData["votes_for"];
		$votesNeutral = $themeData["votes_neutral"];
		$votesAgainst = $themeData["votes_against"];
		$votesTotal = $votesFor + $votesNeutral + $votesAgainst;
		$userCastAVoteForThisTheme = false;

		$theme = Array();
		$theme["theme"] = $themeText;
		$theme["votes_for"] = $votesFor;
		$theme["votes_neutral"] = $votesNeutral;
		$theme["votes_against"] = $votesAgainst;
		$theme["votes_report"] = $themeData["votes_report"];
		$theme["votes_total"] = $votesTotal;
		$theme["votes_popularity"] = "?";
		$theme["votes_apathy"] = "?";
		$theme["popularity_num"] = 0;
		$theme["apathy_num"] = 0;
		$theme["has_enough_votes"] = false;
		$theme["top_theme"] = 0;
		$theme["keep_theme"] = false;
		$theme["apathy_color"] = "#ffffff";
		$theme["popularity_color"] = "#ffffff";
		$theme["banned"] = $banned;
		$theme["author"] = $themeData["author"];
		$theme["theme_id"] = $themeID;
		$theme["ThemeSelectionProbabilityByVoteDifferenceText"] = $themesByVoteDifference[$themeID]["ThemeSelectionProbabilityByVoteDifferenceText"];
		$theme["ThemeSelectionProbabilityByPopularityText"] = $themesByPopularity[$themeID]["ThemeSelectionProbabilityByPopularityText"];
		$theme["days_ago"] = $themeData["days_ago"];
		
		//Generate theme vote button ID
		$themeBtnID = preg_replace("/[^A-Za-z0-9]/", '', $themeText);
		$theme["theme_button_id"] = $themeBtnID;

		//Visibility of banned themes
		if($banned == 1){
			$theme["banned"] = 1;
			if(IsAdmin()){
				$theme["theme_visible"] = 1;
			}
		}else{
			$theme["theme_visible"] = 1;
		}

		//User theme vote
		if(isset($userThemeVotes[$themeID])){
			$userThemeVoteKey = UserThemeVoteTypeToKey($userThemeVotes[$themeID]);
			$theme[$userThemeVoteKey] = 1;
			$userCastAVoteForThisTheme = true;
		}
		
		//Mark theme as old
		if(intval($theme["days_ago"]) >= intval($config["THEME_DAYS_MARK_AS_OLD"]["VALUE"])){
			$theme["is_old"] = 1;
		}

		//Compute JavaScript formatted lists for themes pie chart
		if($themesByVoteDifference[$themeID]["ThemeSelectionProbabilityByVoteDifference"] > 0){
			$themeSelectionProbabilityByVoteDifference = floor($themesByVoteDifference[$themeID]["ThemeSelectionProbabilityByVoteDifference"] * 10000) / 100;
			$themeSelectionProbabilityByVoteDifferenceUnmodified = $themeSelectionProbabilityByVoteDifference;

			if(isset($jsFormattedThemesPopularityThemeList["".$themeSelectionProbabilityByVoteDifference])){
				$safety = 100;
				while($safety > 0){
					$safety--;
					$themeSelectionProbabilityByVoteDifference += 0.001;
					if(!isset($jsFormattedThemesPopularityThemeList["".$themeSelectionProbabilityByVoteDifference])){
						break;
					}
				}
			}

			$jsFormattedThemesPopularityThemeList["".$themeSelectionProbabilityByVoteDifference] = "\"".str_replace("\"", "\\\"", htmlspecialchars_decode($theme["theme"], ENT_COMPAT | ENT_HTML401 | ENT_QUOTES))."\"";
			$jsFormattedThemesPopularityPopularityList["".$themeSelectionProbabilityByVoteDifference] = $themeSelectionProbabilityByVoteDifferenceUnmodified;

			$randomR = 0x10 + (rand(0,14) * 0x10);
			$randomG = 0x10 + (rand(0,14) * 0x10);
			$randomB = 0x10 + (rand(0,14) * 0x10);
			$jsFormattedThemesPopularityFillColorList["".$themeSelectionProbabilityByVoteDifference] = "'rgba(".$randomR.", ".$randomG.", ".$randomB.", 0.2)'";
			$jsFormattedThemesPopularityBorderColorList["".$themeSelectionProbabilityByVoteDifference] = "'rgba(".$randomR.", ".$randomG.", ".$randomB.", 1)'";
		}

		//Check if user voted for this theme
		if(!$banned && !$userCastAVoteForThisTheme){
			$themesUserHasNotVotedFor++;
		}

		//Calculate popularity and apathy
		if($votesTotal >= intval($config["THEME_MIN_VOTES_TO_SCORE"]["VALUE"])){
			$theme["has_enough_votes"] = true;

			$oppinionatedVotesTotal = $votesFor + $votesAgainst;
			$unopinionatedVotesTotal = $votesNeutral;

			//Popularity
			if($oppinionatedVotesTotal > 0){
				$votesPopularity = $votesFor / $oppinionatedVotesTotal;
				$theme["popularity_num"] = $votesPopularity;
				$theme["votes_popularity"] = round($votesPopularity * 100) . "%";
			}
			if($votesPopularity >= 0.5){
				//Popularity color >50%: yellow to green
				$theme["popularity_color"] = "#".(str_pad(dechex(0xFF - (0xFF * 2 * ($votesPopularity - 0.5))), 2, "0", STR_PAD_LEFT))."FF00";
			}else{
				//Popularity color <50%: red to yellow
				$theme["popularity_color"] = "#ff".str_pad(dechex((0xFF * 2 * $votesPopularity)), 2, "0", STR_PAD_LEFT)."00";
			}

			//Apathy
			if($votesTotal > 0){
				$votesApathy = $unopinionatedVotesTotal / $votesTotal;
				$theme["apathy_num"] = $votesApathy;
				$theme["votes_apathy"] = round($votesApathy * 100) . "%";
			}
			//Apathy color: blue to red
			$theme["apathy_color"] = "#".str_pad(dechex(0xBB + round(0x44 * $votesApathy)), 2, "0", STR_PAD_LEFT)."DD".str_pad(dechex(0xBB + round(0x44 * (1 - $votesApathy))), 2, "0", STR_PAD_LEFT);
		}
		
		$render["suggested_themes"][] = $theme;
	}

	//Determine top themes and themes to mark to keep
	if(IsAdmin()){
		usort($render["suggested_themes"], "CmpArrayByPropertyPopularityNum");
		$count = 0;
		foreach($render["suggested_themes"] as $i => $theme){
			if($count < intval($config["THEME_NUMBER_TO_MARK_TOP"]["VALUE"])){
				$render["suggested_themes"][$i]["top_theme"] = 1;
				$render["top_themes"][] = $theme;
			}
			if($count < intval($config["THEME_NUMBER_TO_MARK_KEEP"]["VALUE"]) || !$theme["has_enough_votes"]){
				$render["suggested_themes"][$i]["keep_theme"] = 1;
			}
			$count++;
		}
	}

	//Add "Themes need a vote" notification
	if($themesUserHasNotVotedFor != 0){
		$render["user_has_not_voted_for_all_themes"] = 1;
		$render["themes_user_has_not_voted_for"] = $themesUserHasNotVotedFor;
		if($themesUserHasNotVotedFor != 0 && $themesUserHasNotVotedFor != 1){
			$render["themes_user_has_not_voted_for_plural"] = 1;
		}
	}

	//Finalize JavaScript formatted data for the pie chart
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

function ThemeVoteTypeToKey($themeVoteType){
	switch($themeVoteType){
		case "1":
			return "votes_against";
		case "2":
			return "votes_neutral";
		case "3":
			return "votes_for";
	}
}

function UserThemeVoteTypeToKey($themeVoteType){
	switch($themeVoteType){
		case "1":
			return "user_vote_against";
		case "2":
			return "user_vote_neutral";
		case "3":
			return "user_vote_for";
	}
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
	$availableThemes = Array();

	$totalVotesDifference = 0;
	foreach($themes as $id => $theme){
		$themeOption = Array();
		$themeID = $theme["theme_id"];
		$result[$themeID]["ThemeSelectionProbabilityByVoteDifference"] = 0;
		$result[$themeID]["ThemeSelectionProbabilityByVoteDifferenceText"] = "0%";

		if($theme["banned"] == 1){
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
	$availableThemes = Array();

	$totalVotesDifference = 0;
	foreach($themes as $id => $theme){
		$themeOption = Array();
		$themeID = $theme["theme_id"];
		$result[$themeID]["ThemeSelectionProbabilityByPopularity"] = 0;
		$result[$themeID]["ThemeSelectionProbabilityByPopularityText"] = "0%";

		if($theme["banned"] == 1){
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
