<?php

function RenderThemes(&$configData, &$jamData, &$themeData, &$themesByVoteDifference, &$themesByPopularity, &$loggedInUser){
	AddActionLog("RenderThemes");
	StartTimer("RenderThemes");
	
	$render = Array();

	$render["suggested_themes"] = Array();

	$render["has_own_themes"] = false;
	$render["has_other_themes"] = false;

	$jsFormattedThemesPopularityThemeList = Array();
	$jsFormattedThemesPopularityPopularityList = Array();
	$jsFormattedThemesPopularityFillColorList = Array();
	$jsFormattedThemesPopularityBorderColorList = Array();

	$themesUserHasNotVotedFor = 0;

	foreach($themeData->ThemeModels as $i => $themeModel){

		$themeID = intval($themeModel->Id);
		$themeText = $themeModel->Theme;
		$banned = $themeModel->Banned;
		$votesFor = $themeModel->VotesFor;
		$votesNeutral = $themeModel->VotesNeutral;
		$votesAgainst = $themeModel->VotesAgainst;
		$votesTotal = $votesFor + $votesNeutral + $votesAgainst;
		$userCastAVoteForThisTheme = false;

		$theme = Array();
		$theme["theme"] = $themeText;
		$theme["votes_for"] = $votesFor;
		$theme["votes_neutral"] = $votesNeutral;
		$theme["votes_against"] = $votesAgainst;
		$theme["votes_report"] = $themeModel->VotesReport;
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
		$theme["author"] = $themeModel->Author;
		$theme["theme_id"] = $themeID;
		$theme["ThemeSelectionProbabilityByVoteDifferenceText"] = $themesByVoteDifference[$themeID]["ThemeSelectionProbabilityByVoteDifferenceText"];
		if($votesTotal < $configData->ConfigModels["THEME_MIN_VOTES_TO_SCORE"]->Value){
			if($configData->ConfigModels["THEME_MIN_VOTES_TO_SCORE"]->Value == 1){
				$theme["UserThemeSelectionProbabilityByVoteDifferenceText"] = "$votesTotal / ".$configData->ConfigModels["THEME_MIN_VOTES_TO_SCORE"]->Value ." Vote";
			}else{
				$theme["UserThemeSelectionProbabilityByVoteDifferenceText"] = "$votesTotal / ".$configData->ConfigModels["THEME_MIN_VOTES_TO_SCORE"]->Value ." Votes";
			}
		}else{
			$theme["UserThemeSelectionProbabilityByVoteDifferenceText"] = $themesByVoteDifference[$themeID]["ThemeSelectionProbabilityByVoteDifferenceText"];
		}
		$theme["ThemeSelectionProbabilityByPopularityText"] = $themesByPopularity[$themeID]["ThemeSelectionProbabilityByPopularityText"];
		$theme["days_ago"] = $themeModel->DaysAgo;
		if($loggedInUser !== false){
			$theme["is_own_theme"] = $themeModel->Author == $loggedInUser->Username;
			if($banned == 0){
				if ($theme["is_own_theme"]) {
					$render["has_own_themes"] = true;
				}else{
					$render["has_other_themes"] = true;
				}
			}
		}
		
		//Generate theme vote button ID
		$themeBtnID = preg_replace("/[^A-Za-z0-9]/", '', $themeText);
		$theme["theme_button_id"] = $themeBtnID;

		//Visibility of banned themes
		if($banned == 1){
			$theme["banned"] = 1;
			if(IsAdmin($loggedInUser) !== false){
				$theme["theme_visible"] = 1;
			}
		}else{
			$theme["theme_visible"] = 1;
		}

		//User theme vote
		if(isset($themeData->LoggedInUserThemeVotes[$themeID])){
			$userThemeVoteKey = UserThemeVoteTypeToKey($themeData->LoggedInUserThemeVotes[$themeID]);
			$theme[$userThemeVoteKey] = 1;
			$userCastAVoteForThisTheme = true;
		}
		
		//Mark theme as old
		$theme["is_old"] = intval($theme["days_ago"]) >= intval($configData->ConfigModels["THEME_DAYS_MARK_AS_OLD"]->Value);
		$theme["is_recent"] = IsRecentTheme($jamData, $configData, $themeText);

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
		if($votesTotal >= intval($configData->ConfigModels["THEME_MIN_VOTES_TO_SCORE"]->Value)){
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
	// Used for theme pruning notification
	$render["themes_must_be_pruned"] = 0;
	if(IsAdmin($loggedInUser) !== false){
		//Determine top themes and themes to mark to keep
		usort($render["suggested_themes"], "CmpArrayByPropertyPopularityNum");
		$count = 0;
		foreach($render["suggested_themes"] as $i => $theme){
			if($count < intval($configData->ConfigModels["THEME_NUMBER_TO_MARK_TOP"]->Value)){
				$render["suggested_themes"][$i]["top_theme"] = 1;
				$render["top_themes"][] = $theme;
			}
			if($count < intval($configData->ConfigModels["THEME_NUMBER_TO_MARK_KEEP"]->Value) || !$theme["has_enough_votes"]){
				$render["suggested_themes"][$i]["keep_theme"] = 1;
			}
			$count++;
		}

		//Determine which themes to mark for automatic deletion
		foreach($render["suggested_themes"] as $i => $theme) {
			if (!$theme["banned"] && (!$theme["keep_theme"] || $theme["is_old"] || $theme["is_recent"])) {
				$render["suggested_themes"][$i]["is_marked_for_deletion"] = 1;
				if ($theme["is_recent"]) {
					$render["themes_must_be_pruned"] = 1;
				}
			} else {
				$render["suggested_theme"][$i]["is_marked_for_deletion"] = 0;
			}
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

function IsRecentTheme(&$jamData, &$configData, $theme) {
	AddActionLog("IsRecentTheme");
	StartTimer("IsRecentTheme");
	$jamNumber = 1; // Number of non deleted jams traversed
	foreach ($jamData->JamModels as $i => $jamModel) {
		$currentTime = new DateTime();
		$startTime = new DateTime($jamModel->StartTime . " UTC");
		if ($jamModel->Deleted == 0 && $startTime < $currentTime) {
			if (strtolower($jamModel->Theme) == strtolower($theme)){
				return "THEME_RECENTLY_USED";
			}
			if (++$jamNumber > $configData->ConfigModels["JAM_THEMES_CONSIDERED_RECENT"]->Value){
				break;
			}
		}
	}
	StopTimer("IsRecentTheme");
}

function UserThemeVoteTypeToKey($themeVoteType){
	AddActionLog("UserThemeVoteTypeToKey");
	switch($themeVoteType){
		case "1":
			return "user_vote_against";
		case "2":
			return "user_vote_neutral";
		case "3":
			return "user_vote_for";
	}
}

function CalculateThemeSelectionProbabilityByVoteDifference(&$themeData, &$configData){
	AddActionLog("CalculateThemeSelectionProbabilityByVoteDifference");
	StartTimer("CalculateThemeSelectionProbabilityByVoteDifference");

	$minimumVotes = $configData->ConfigModels["THEME_MIN_VOTES_TO_SCORE"]->Value;

	$result = Array();
	$availableThemes = Array();

	$totalVotesDifference = 0;
	foreach($themeData->ThemeModels as $id => $themeModels){
		$themeOption = Array();
		$themeID = $themeModels->Id;
		$result[$themeID]["ThemeSelectionProbabilityByVoteDifference"] = 0;
		$result[$themeID]["ThemeSelectionProbabilityByVoteDifferenceText"] = "0%";

		if($themeModels->Banned == 1){
			continue;
		}

		$votesFor = $themeModels->VotesFor;
		$votesNeutral = $themeModels->VotesNeutral;
		$votesAgainst = $themeModels->VotesAgainst;
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

		$themeOption["theme"] = $themeModels->Theme;
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

function CalculateThemeSelectionProbabilityByPopularity(&$themeData, &$configData){
	AddActionLog("CalculateThemeSelectionProbabilityByPopularity");
	StartTimer("CalculateThemeSelectionProbabilityByPopularity");

	$minimumVotes = $configData->ConfigModels["THEME_MIN_VOTES_TO_SCORE"]->Value;
	$totalPopularity = 0;

	$result = Array();
	$availableThemes = Array();

	$totalVotesDifference = 0;
	foreach($themeData->ThemeModels as $id => $themeModels){
		$themeOption = Array();
		$themeID = $themeModels->Id;
		$result[$themeID]["ThemeSelectionProbabilityByPopularity"] = 0;
		$result[$themeID]["ThemeSelectionProbabilityByPopularityText"] = "0%";

		if($themeModels->Banned == 1){
			continue;
		}

		$votesFor = $themeModels->VotesFor;
		$votesNeutral = $themeModels->VotesNeutral;
		$votesAgainst = $themeModels->VotesAgainst;
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

		$themeOption["theme"] = $themeModels->Theme;
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
