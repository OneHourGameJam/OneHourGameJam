<?php

class ThemeController{

	public static function CalculateThemeSelectionProbabilityByVoteDifference(&$themeData, &$configData){
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

	public static function CalculateThemeSelectionProbabilityByPopularity(&$themeData, &$configData){
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

	public static function PruneThemes(&$themeData, &$jamData, &$configData, &$adminLogData){

		$themesWithEnoughVotes = Array();

		foreach($themeData->ThemeModels as $i => $themeModel){
			$theme = Array();

			$theme["id"] = $themeModel->Id;
			$theme["theme"] = $themeModel->Theme;

			$votesFor = $themeModel->VotesFor;
			$votesOpinionated = $themeModel->VotesFor + $themeModel->VotesAgainst;

			$votesTotal = $themeModel->VotesFor + $themeModel->VotesNeutral + $themeModel->VotesAgainst;

			if($votesTotal < intval($configData->ConfigModels["THEME_MIN_VOTES_TO_SCORE"]->Value)){
				//not enough votes
				continue;
			}
			
			$theme["is_old"] = intval($themeModel->DaysAgo) >= intval($configData->ConfigModels["THEME_DAYS_MARK_AS_OLD"]->Value);
			$theme["is_recent"] = ThemePresenter::IsRecentTheme($jamData, $configData, $themeModel->Theme);
			$theme["popularity"] = 0;
			if($votesOpinionated > 0){
				$theme["popularity"] = $votesFor / $votesOpinionated;
			}

			$themesWithEnoughVotes[] = $theme;
		}
		

		usort($themesWithEnoughVotes, function ($item1, $item2) {
			if($item1['popularity'] >= $item2['popularity']){
				return -1;
			}else if($item1['popularity'] <= $item2['popularity']){
				return 1;
			}else{
				return 0;
			}
		});

		$themesToDelete = Array();
		$themesToKeepRemaining = intval($configData->ConfigModels["THEME_NUMBER_TO_MARK_KEEP"]->Value);
		foreach($themesWithEnoughVotes as $i => $theme){
			if($theme["is_old"]){
				$theme["delete_reason"] = "Old";
				$themesToDelete[] = $theme;
				continue;
			}
			if($theme["is_recent"]){
				$theme["delete_reason"] = "Used in recent jam";
				$themesToDelete[] = $theme;
				continue;
			}
			$themesToKeepRemaining--;
			if($themesToKeepRemaining < 0){
				$theme["delete_reason"] = "Unpopular";
				$themesToDelete[] = $theme;
				continue;
			}
		}

		foreach($themesToDelete as $i => $theme){
			$themeData->SoftDeleteThemeInDatabase($theme["id"]);
			$removedTheme = $theme["theme"];
			$deletionReason = $theme["delete_reason"];
			$adminLogData->AddToAdminLog("THEME_SOFT_DELETED", "Theme '$removedTheme' soft deleted. Reason: $deletionReason", "NULL", "NULL", "AUTOMATIC PRUNING");
		}
	}
}

?>
