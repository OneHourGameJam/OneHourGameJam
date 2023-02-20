<?php

class ThemePresenter{
	public static function RenderActiveThemes(ConfigData &$configData, JamData &$jamData, UserData &$userData, ThemeData &$themeData, ThemeIdeaData &$themeIdeaData, &$themesByVoteDifference, &$themesByPopularity, &$loggedInUser, &$renderDepth){
		AddActionLog("RenderThemes");
		StartTimer("RenderThemes");
		
		$themesViewModel = new ThemesViewModel();

		$themesViewModel->suggested_themes = Array();

		$themesViewModel->has_own_themes = false;
		$themesViewModel->has_other_themes = false;

		$jsFormattedThemesPopularityThemeList = Array();
		$jsFormattedThemesPopularityPopularityList = Array();
		$jsFormattedThemesPopularityFillColorList = Array();
		$jsFormattedThemesPopularityBorderColorList = Array();

		$themesUserHasNotVotedFor = 0;

		foreach($themeData->ActiveThemeModels as $i => $themeModel){

			$themeID = intval($themeModel->Id);
			$themeText = $themeModel->Theme;
			$banned = $themeModel->Banned;
			$votesFor = $themeModel->VotesFor;
			$votesNeutral = $themeModel->VotesNeutral;
			$votesAgainst = $themeModel->VotesAgainst;
			$votesTotal = $votesFor + $votesNeutral + $votesAgainst;
			$userCastAVoteForThisTheme = false;

			$themeViewModel = new ThemeViewModel();
			$themeViewModel->theme = $themeText;
			$themeViewModel->votes_for = $votesFor;
			$themeViewModel->votes_neutral = $votesNeutral;
			$themeViewModel->votes_against = $votesAgainst;
			$themeViewModel->votes_report = $themeModel->VotesReport;
			$themeViewModel->votes_total = $votesTotal;
			$themeViewModel->votes_popularity = "?";
			$themeViewModel->votes_apathy = "?";
			$themeViewModel->popularity_num = 0;
			$themeViewModel->apathy_num = 0;
			$themeViewModel->has_enough_votes = false;
			$themeViewModel->top_theme = 0;
			$themeViewModel->keep_theme = false;
			$themeViewModel->apathy_color = "#ffffff";
			$themeViewModel->popularity_color = "#ffffff";
			$themeViewModel->banned = $banned;
			$themeViewModel->author_user_id = $themeModel->AuthorUserId;
			$themeViewModel->theme_id = $themeID;
			$themeViewModel->ThemeSelectionProbabilityByVoteDifferenceText = $themesByVoteDifference[$themeID]["ThemeSelectionProbabilityByVoteDifferenceText"];
			if($votesTotal < $configData->ConfigModels[CONFIG_THEME_MIN_VOTES_TO_SCORE]->Value){
				if($configData->ConfigModels[CONFIG_THEME_MIN_VOTES_TO_SCORE]->Value == 1){
					$themeViewModel->UserThemeSelectionProbabilityByVoteDifferenceText = "$votesTotal / ".$configData->ConfigModels[CONFIG_THEME_MIN_VOTES_TO_SCORE]->Value ." Vote";
				}else{
					$themeViewModel->UserThemeSelectionProbabilityByVoteDifferenceText = "$votesTotal / ".$configData->ConfigModels[CONFIG_THEME_MIN_VOTES_TO_SCORE]->Value ." Votes";
				}
			}else{
				$themeViewModel->UserThemeSelectionProbabilityByVoteDifferenceText = $themesByVoteDifference[$themeID]["ThemeSelectionProbabilityByVoteDifferenceText"];
			}
			$themeViewModel->ThemeSelectionProbabilityByPopularityText = $themesByPopularity[$themeID]["ThemeSelectionProbabilityByPopularityText"];
			$themeViewModel->days_ago = $themeModel->DaysAgo;
			if($loggedInUser !== false){
				$themeViewModel->is_own_theme = $themeModel->AuthorUserId == $loggedInUser->Id;
				if($banned == 0){
					if ($themeViewModel->is_own_theme) {
						$themesViewModel->has_own_themes = true;
					}else{
						$themesViewModel->has_other_themes = true;
					}
				}
			}

			if($themeModel->AuthorUserId) {
                $themeViewModel->author_username = $userData->UserModels[$themeModel->AuthorUserId]->Username;
                $themeViewModel->author_display_name = $userData->UserModels[$themeModel->AuthorUserId]->DisplayName;
            }else{
                $themeViewModel->author_username = "Legacy theme";
                $themeViewModel->author_display_name = "Legacy theme";
            }
			
			//Generate theme vote button ID
			$themeBtnID = preg_replace("/[^A-Za-z0-9]/", '', $themeText);
			$themeViewModel->theme_button_id = $themeBtnID;

			//Visibility of banned themes
			if($banned == 1){
				$themeViewModel->banned = 1;
				if(IsAdmin($loggedInUser) !== false){
					$themeViewModel->theme_visible = 1;
				}
			}else{
				$themeViewModel->theme_visible = 1;
			}

			//User theme vote
			if(isset($themeData->LoggedInUserThemeVotes[$themeID])){
				$userThemeVoteKey = ThemePresenter::UserThemeVoteTypeToKey($themeData->LoggedInUserThemeVotes[$themeID]);
				$themeViewModel->$userThemeVoteKey = 1;
				$userCastAVoteForThisTheme = true;
			}
			
			//Mark theme as old
			$themeViewModel->is_old = intval($themeViewModel->days_ago) >= intval($configData->ConfigModels[CONFIG_THEME_DAYS_MARK_AS_OLD]->Value);
			$themeViewModel->is_recent = ThemePresenter::IsRecentTheme($jamData, $configData, $themeText);

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

				$jsFormattedThemesPopularityThemeList["".$themeSelectionProbabilityByVoteDifference] = "\"".str_replace("\"", "\\\"", htmlspecialchars_decode($themeViewModel->theme, ENT_COMPAT | ENT_HTML401 | ENT_QUOTES))."\"";
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
			if($votesTotal >= intval($configData->ConfigModels[CONFIG_THEME_MIN_VOTES_TO_SCORE]->Value)){
				$themeViewModel->has_enough_votes = true;

				$oppinionatedVotesTotal = $votesFor + $votesAgainst;
				$unopinionatedVotesTotal = $votesNeutral;

				//Popularity
				if($oppinionatedVotesTotal > 0){
					$votesPopularity = $votesFor / $oppinionatedVotesTotal;
					$themeViewModel->popularity_num = $votesPopularity;
					$themeViewModel->votes_popularity = round($votesPopularity * 100) . "%";
				}
				if($votesPopularity >= 0.5){
					//Popularity color >50%: yellow to green
					$themeViewModel->popularity_color = "#".(str_pad(dechex(0xFF - (0xFF * 2 * ($votesPopularity - 0.5))), 2, "0", STR_PAD_LEFT))."FF00";
				}else{
					//Popularity color <50%: red to yellow
					$themeViewModel->popularity_color = "#ff".str_pad(dechex((0xFF * 2 * $votesPopularity)), 2, "0", STR_PAD_LEFT)."00";
				}

				//Apathy
				if($votesTotal > 0){
					$votesApathy = $unopinionatedVotesTotal / $votesTotal;
					$themeViewModel->apathy_num = $votesApathy;
					$themeViewModel->votes_apathy = round($votesApathy * 100) . "%";
				}
				//Apathy color: blue to red
				$themeViewModel->apathy_color = "#".str_pad(dechex(0xBB + round(0x44 * $votesApathy)), 2, "0", STR_PAD_LEFT)."DD".str_pad(dechex(0xBB + round(0x44 * (1 - $votesApathy))), 2, "0", STR_PAD_LEFT);
			}

			if(($renderDepth & RENDER_DEPTH_THEME_IDEAS) > 0){
				foreach($themeIdeaData->ThemeIdeas as $i => $themeIdea){
					if($themeIdea->ThemeId == $themeID){
						$themeViewModel->ideas = $themeIdea->Ideas;
					}
				}
			}
			
			$themesViewModel->suggested_themes[] = $themeViewModel;
		}
		// Used for theme pruning notification
		$themesViewModel->themes_must_be_pruned = 0;

		if(IsAdmin($loggedInUser) !== false){
			//Determine top themes and themes to mark to keep
			usort($themesViewModel->suggested_themes, "ThemePresenter::CmpArrayByPropertyPopularityNum");
			$count = 0;
			foreach($themesViewModel->suggested_themes as $i => $theme){
				if($count < intval($configData->ConfigModels[CONFIG_THEME_NUMBER_TO_MARK_TOP]->Value)){
					$themesViewModel->suggested_themes[$i]->top_theme = 1;
					$themesViewModel->top_themes[] = $theme;
				}
				if($count < intval($configData->ConfigModels[CONFIG_THEME_NUMBER_TO_MARK_KEEP]->Value) || !$themeViewModel->has_enough_votes){
					$themesViewModel->suggested_themes[$i]->keep_theme = 1;
				}
				$count++;
			}

			//Determine which themes to mark for automatic deletion
			foreach($themesViewModel->suggested_themes as $i => $themeViewModel) {
				if($themeViewModel->banned){
					$themesViewModel->suggested_themes[$i]->is_marked_for_deletion = 0;
					continue;
				}

				if(!$themeViewModel->keep_theme || $themeViewModel->is_old || $themeViewModel->is_recent) {
					$themesViewModel->suggested_themes[$i]->is_marked_for_deletion = 1;
					if ($themeViewModel->is_recent) {
						$themesViewModel->themes_must_be_pruned = 1;
					}
				} else {
				}
			} 
		}

		//Add "Themes need a vote" notification
		if($themesUserHasNotVotedFor != 0){
			$themesViewModel->user_has_not_voted_for_all_themes = 1;
			$themesViewModel->themes_user_has_not_voted_for = $themesUserHasNotVotedFor;
			if($themesUserHasNotVotedFor != 0 && $themesUserHasNotVotedFor != 1){
				$themesViewModel->themes_user_has_not_voted_for_plural = 1;
			}
		}

		//Finalize JavaScript formatted data for the pie chart
		krsort($jsFormattedThemesPopularityThemeList);
		krsort($jsFormattedThemesPopularityPopularityList);
		krsort($jsFormattedThemesPopularityFillColorList);
		krsort($jsFormattedThemesPopularityBorderColorList);

		$themesViewModel->js_formatted_themes_popularity_themes_list = implode(",", $jsFormattedThemesPopularityThemeList);
		$themesViewModel->js_formatted_themes_popularity_popularity_list = implode(",", $jsFormattedThemesPopularityPopularityList);
		$themesViewModel->js_formatted_themes_popularity_fill_color_list = implode(",", $jsFormattedThemesPopularityFillColorList);
		$themesViewModel->js_formatted_themes_popularity_border_color_list = implode(",", $jsFormattedThemesPopularityBorderColorList);

		StopTimer("RenderThemes");
		return $themesViewModel;
	}

    public static function RenderAllThemes(ConfigData &$configData, UserData &$userData, ThemeData &$themeData){
        $render = Array();

        foreach($themeData->AllThemeModels as $i => $themeModel){
            $themeID = intval($themeModel->Id);
            $themeText = $themeModel->Theme;
            $banned = $themeModel->Banned;
            $votesFor = $themeModel->VotesFor;
            $votesNeutral = $themeModel->VotesNeutral;
            $votesAgainst = $themeModel->VotesAgainst;
            $votesTotal = $votesFor + $votesNeutral + $votesAgainst;

            $themeViewModel = new ThemeSmallViewModel();
            $themeViewModel->theme = $themeText;
            $themeViewModel->votes_for = $votesFor;
            $themeViewModel->votes_neutral = $votesNeutral;
            $themeViewModel->votes_against = $votesAgainst;
            $themeViewModel->votes_report = $themeModel->VotesReport;
            $themeViewModel->votes_total = $votesTotal;
            $themeViewModel->votes_popularity = "?";
            $themeViewModel->votes_apathy = "?";
            $themeViewModel->popularity_num = 0;
            $themeViewModel->apathy_num = 0;
            $themeViewModel->apathy_color = "#ffffff";
            $themeViewModel->popularity_color = "#ffffff";
            $themeViewModel->banned = $banned;
            $themeViewModel->author_user_id = $themeModel->AuthorUserId;
            $themeViewModel->theme_id = $themeID;
            $themeViewModel->days_ago = $themeModel->DaysAgo;

            if($themeModel->AuthorUserId) {
                $themeViewModel->author_username = $userData->UserModels[$themeModel->AuthorUserId]->Username;
                $themeViewModel->author_display_name = $userData->UserModels[$themeModel->AuthorUserId]->DisplayName;
            }else{
                $themeViewModel->author_username = "Legacy theme";
                $themeViewModel->author_display_name = "Legacy theme";
            }

            //Calculate popularity and apathy
            if($votesTotal >= intval($configData->ConfigModels[CONFIG_THEME_MIN_VOTES_TO_SCORE]->Value)){
                $themeViewModel->has_enough_votes = true;

                $oppinionatedVotesTotal = $votesFor + $votesAgainst;
                $unopinionatedVotesTotal = $votesNeutral;

                //Popularity
                if($oppinionatedVotesTotal > 0){
                    $votesPopularity = $votesFor / $oppinionatedVotesTotal;
                    $themeViewModel->popularity_num = $votesPopularity;
                    $themeViewModel->votes_popularity = round($votesPopularity * 100) . "%";
                }
                if($votesPopularity >= 0.5){
                    //Popularity color >50%: yellow to green
                    $themeViewModel->popularity_color = "#".(str_pad(dechex(0xFF - (0xFF * 2 * ($votesPopularity - 0.5))), 2, "0", STR_PAD_LEFT))."FF00";
                }else{
                    //Popularity color <50%: red to yellow
                    $themeViewModel->popularity_color = "#ff".str_pad(dechex((0xFF * 2 * $votesPopularity)), 2, "0", STR_PAD_LEFT)."00";
                }

                //Apathy
                if($votesTotal > 0){
                    $votesApathy = $unopinionatedVotesTotal / $votesTotal;
                    $themeViewModel->apathy_num = $votesApathy;
                    $themeViewModel->votes_apathy = round($votesApathy * 100) . "%";
                }
                //Apathy color: blue to red
                $themeViewModel->apathy_color = "#".str_pad(dechex(0xBB + round(0x44 * $votesApathy)), 2, "0", STR_PAD_LEFT)."DD".str_pad(dechex(0xBB + round(0x44 * (1 - $votesApathy))), 2, "0", STR_PAD_LEFT);
            }

            $render[] = $themeViewModel;
        }

        usort($render, function($a, $b){ return $a->popularity_num < $b->popularity_num; });

        return $render;
    }

	public static function UserThemeVoteTypeToKey($themeVoteType){
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
	
	//Numeric comparator for arrays based on property
	public static function CmpArrayByPropertyPopularityNum($a, $b)
	{
		AddActionLog("CmpArrayByPropertyPopularityNum");

		if(isset($a->banned) && $a->banned == 1){
			return 1;
		}
		return ThemePresenter::CmpArrayByProperty($a, $b, "popularity_num");
	}

	//Numeric comparator for arrays based on property
	public static function CmpArrayByProperty($a, $b, $property)
	{
		AddActionLog("CmpArrayByProperty");

		return $a->$property < $b->$property;
	}

	public static function IsRecentTheme(&$jamData, &$configData, $theme) {
		AddActionLog("IsRecentTheme");
		StartTimer("IsRecentTheme");
		$jamNumber = 1; // Number of non deleted jams traversed
		$theme = preg_replace("/[^0-9a-zA-Z ]/m", "", strtolower($theme));
		$theme = preg_replace("/ /", "-", $theme);
		foreach ($jamData->JamModels as $i => $jamModel) {
			$currentTime = new DateTime("UTC");
			$startTime = new DateTime($jamModel->StartTime . " UTC");
			if ($jamModel->Deleted == 0 && $startTime < $currentTime) {
				$jamModelTheme = preg_replace("/[^0-9a-zA-Z ]/m", "", strtolower($jamModel->Theme));
				$jamModelTheme = preg_replace("/ /", "-", $jamModelTheme);

				if ($jamModelTheme == $theme){
					StopTimer("IsRecentTheme");
					return "THEME_RECENTLY_USED";
				}
				if (++$jamNumber > $configData->ConfigModels[CONFIG_JAM_THEMES_CONSIDERED_RECENT]->Value){
					break;
				}
			}
		}
		StopTimer("IsRecentTheme");
	}
}

?>