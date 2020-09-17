<?php

class JamController{
	public static function ProcessJamStates(MessageService &$messageService, &$jamData, &$themeData, &$configData){
		AddActionLog("ProcessJamStates");
		StartTimer("ProcessJamStates");
	
		foreach($jamData->JamModels as $i => $jamModel){
			if($jamModel->Deleted == 1){
				if($jamModel->State != JAM_STATE_DELETED){
					$jamData->UpdateJamStateInDatabase($jamModel->Id, JAM_STATE_DELETED);
				}
				continue;
			}
			
			//Hide theme of not-yet-started jams
			$now = new DateTime("UTC");
			$jamStartTime = new DateTime($jamModel->StartTime . " UTC");
			$jamDurationInMinutes = intval($configData->ConfigModels[CONFIG_JAM_DURATION]->Value);
			$jamEndTime = clone $jamStartTime;
			$jamEndTime->add(new DateInterval("PT".$jamDurationInMinutes."M"));
	
			if($now > $jamEndTime){
				//Past Jam (jam's over)
				if($jamModel->State != JAM_STATE_COMPLETED){
					$jamData->UpdateJamStateInDatabase($jamModel->Id, JAM_STATE_COMPLETED);
				}
			}else if($now > $jamStartTime){
				//Present Jam (started, hasn't finished yet)
				if($jamModel->State != JAM_STATE_ACTIVE){
					$jamData->UpdateJamStateInDatabase($jamModel->Id, JAM_STATE_ACTIVE);
					ThemeController::PruneThemes($messageService, $themeData, $jamData, $configData);
				}
			}else{
				//Future Jam (not yet started)
				if($jamModel->State != JAM_STATE_SCHEDULED){
					$jamData->UpdateJamStateInDatabase($jamModel->Id, JAM_STATE_SCHEDULED);
				}
			}
		}
		
		StopTimer("ProcessJamStates");
	}
	
	//Checks if a jam is scheduled. If not and a jam is coming up, one is scheduled automatically.
	public static function CheckNextJamSchedule(MessageService &$messageService, &$configData, &$jamData, &$ThemeData, $nextScheduledJamTime, $nextSuggestedJamTime){
		AddActionLog("CheckNextJamSchedule");
		StartTimer("CheckNextJamSchedule"); 

		//print "<br>CHECK JAM SCHEDULING";

		if($configData->ConfigModels[CONFIG_JAM_AUTO_SCHEDULER_ENABLED]->Value == 0){
			//print "<br>AUTO SCHEDULER DISABLED";
			StopTimer("CheckNextJamSchedule");
			return;
		}

		//print "<br>AUTO SCHEDULER ENABLED";
		$autoScheduleThreshold = $configData->ConfigModels[CONFIG_JAM_AUTO_SCHEDULER_MINUTES_BEFORE_JAM]->Value * 60;

		$now = time();
		$timeToNextScheduledJam = $nextScheduledJamTime - $now;
		$timeToNextSuggestedJam = $nextSuggestedJamTime - $now;

		$isTimeToScheduleJam = $timeToNextSuggestedJam > 0 && $timeToNextSuggestedJam <= $autoScheduleThreshold;
		$isAJamAlreadyScheduled = $timeToNextScheduledJam > 0;
		$isAJamScheduledInAutoSchedulerThresholdTime = $isAJamAlreadyScheduled && $timeToNextScheduledJam <= $autoScheduleThreshold;

		//print "<br>nextScheduledJamTime = ".gmdate("Y-m-d H:i:s", $nextScheduledJamTime);
		//print "<br>nextSuggestedJamTime = ".gmdate("Y-m-d H:i:s", $nextSuggestedJamTime);
		//print "<br>now = ".gmdate("Y-m-d H:i:s", $now);
		//print "<br>timeToNextScheduledJam = $timeToNextScheduledJam";
		//print "<br>timeToNextSuggestedJam = $timeToNextSuggestedJam";
		//print "<br>autoScheduleThreshold = $autoScheduleThreshold";
		//print "<br>isTimeToScheduleJam = ".($isTimeToScheduleJam ? "YES" : "NO");
		//print "<br>isAJamAlreadyScheduled = ".($isAJamAlreadyScheduled ? "YES" : "NO");
		//print "<br>isAJamScheduledInAutoSchedulerThresholdTime = ".($isAJamScheduledInAutoSchedulerThresholdTime ? "YES" : "NO");

		$colors = "e38484|e3b684|dee384|ade384|84e38d|84e3be|84d6e3|84a4e3|9684e3|c784e3";

		if($isTimeToScheduleJam){
			//print "<br>IT IS TIME TO SCHEDULE A JAM";

			if($isAJamScheduledInAutoSchedulerThresholdTime){
				//A future jam is already scheduled
				//print "<br>A JAM IS ALREADY SCHEDULED";
				StopTimer("CheckNextJamSchedule");
				return;
			}

			$selectedThemeId = -1;
			$selectedTheme = "";

			$selectedThemeId = JamController::SelectRandomThemeByVoteDifference($ThemeData, $configData);
			if($selectedThemeId == -1){
				$selectedThemeId = JamController::SelectRandomThemeByPopularity($ThemeData, $configData);
			}
			if($selectedThemeId == -1){
				$selectedThemeId = JamController::SelectRandomTheme($ThemeData);
			}
			if($selectedThemeId == -1){
				//Failed to find a theme
				$selectedTheme = "Any theme";
			}else{
				$selectedTheme = $ThemeData->ThemeModels[$selectedThemeId]->Theme;
			}

			//print "<br>A THEME WAS SELECTED";

			$currentJam = GetCurrentJamNumberAndId();
			$jamNumber = intval($currentJam["NUMBER"] + 1);
			//print "<br>A JAM NUMBER WAS SELECTED: ".$jamNumber;

			$startTime = gmdate("Y-m-d H:i", $nextSuggestedJamTime);
			$defaultEntryIconUrl = $configData->ConfigModels[CONFIG_DEFAULT_GAME_ICON_URL]->Value;

			$jamData->AddJamToDatabase("127.0.0.1", "AUTO", -1, $jamNumber, $selectedThemeId, $selectedTheme, $startTime, $colors, $defaultEntryIconUrl);

			$messageService->SendMessage(LogMessage::SystemLogMessage(
				"JAM_ADDED", 
				"Jam scheduled with values: JamNumber: $jamNumber, Theme: '$selectedTheme', StartTime: '$startTime', Colors: $colors, Default entry icon url: $defaultEntryIconUrl", 
				OVERRIDE_AUTOMATIC)
			);
		}
		
		StopTimer("CheckNextJamSchedule");
	}

	//Selects a random theme (or "" if none can be selected) by calculating the difference between positive and negative votes and
	//selecting a proportional random theme by this difference
	public static function SelectRandomThemeByVoteDifference(&$ThemeData, &$configData){
		AddActionLog("SelectRandomThemeByVoteDifference");
		StartTimer("SelectRandomThemeByVoteDifference");

		$minimumVotes = $configData->ConfigModels[CONFIG_THEME_MIN_VOTES_TO_SCORE]->Value;

		$selectedThemeId = -1;

		$availableThemes = Array();
		$totalVotesDifference = 0;
		foreach($ThemeData->ThemeModels as $id => $themeModel){
			$themeOption = Array();

			if($themeModel->Banned){
				continue;
			}

			$votesFor = $themeModel->VotesFor;
			$votesNeutral = $themeModel->VotesNeutral;
			$votesAgainst = $themeModel->VotesAgainst;
			$votesDifference = $votesFor - $votesAgainst;

			$votesTotal = $votesFor + $votesNeutral + $votesAgainst;
			$votesOpinionatedTotal = $votesFor + $votesAgainst;

			if($votesOpinionatedTotal <= 0){
				continue;
			}

			$votesPopularity = $votesFor / ($votesOpinionatedTotal);

			if($votesTotal <= 0 || $votesTotal <= $minimumVotes){
				continue;
			}

			$themeOption["theme_id"] = $themeModel->Id;
			$themeOption["votes_for"] = $votesFor;
			$themeOption["votes_difference"] = $votesDifference;
			$themeOption["popularity"] = $votesPopularity;
			$totalVotesDifference += max(0, $votesDifference);

			$availableThemes[] = $themeOption;
		}

		if($totalVotesDifference > 0 && count($availableThemes) > 0){
			$selectedVote = rand(0, $totalVotesDifference);

			$runningVoteNumber = $selectedVote;
			foreach($availableThemes as $i => $availableTheme){
				$runningVoteNumber -= $availableTheme["votes_difference"];
				if($runningVoteNumber <= 0){
					$selectedThemeId = $availableTheme["theme_id"];
					break;
				}
			}
		}

		StopTimer("SelectRandomThemeByVoteDifference");
		return $selectedThemeId;
	}

	//Selects a random theme (or "" if none can be selected) proportionally based on its popularity.
	public static function SelectRandomThemeByPopularity(&$ThemeData, &$configData){
		AddActionLog("SelectRandomThemeByPopularity");
		StartTimer("SelectRandomThemeByPopularity");

		$minimumVotes = $configData->ConfigModels[CONFIG_THEME_MIN_VOTES_TO_SCORE]->Value;

		$selectedThemeId = -1;

		$availableThemes = Array();
		$totalPopularity = 0;
		foreach($ThemeData->ThemeModels as $id => $themeModel){
			$themeOption = Array();

			if($themeModel->Banned){
				continue;
			}

			$votesFor = $themeModel->VotesFor;
			$votesNeutral = $themeModel->VotesNeutral;
			$votesAgainst = $themeModel->VotesAgainst;
			$votesDifference = $votesFor - $votesAgainst;

			$votesTotal = $votesFor + $votesNeutral + $votesAgainst;
			$votesOpinionatedTotal = $votesFor + $votesAgainst;

			if($votesOpinionatedTotal <= 0){
				continue;
			}

			$votesPopularity = $votesFor / ($votesOpinionatedTotal);

			if($votesTotal <= 0 || $votesTotal <= $minimumVotes){
				continue;
			}

			$themeOption["theme_id"] = $themeModel->Id;
			$themeOption["votes_for"] = $votesFor;
			$themeOption["votes_difference"] = $votesDifference;
			$themeOption["popularity"] = $votesPopularity;
			$totalPopularity += max(0, $votesPopularity);

			$availableThemes[] = $themeOption;
		}

		if($totalPopularity > 0 && count($availableThemes) > 0){
			$selectedPopularity = (rand(0, 100000) / 100000) * $totalPopularity;

			$runningPopularity = $selectedPopularity;
			foreach($availableThemes as $i => $availableTheme){
				$runningPopularity -= $availableTheme["popularity"];
				if($runningPopularity <= 0){
					$selectedThemeId = $availableTheme["theme_id"];
					break;
				}
			}
		}

		StopTimer("SelectRandomThemeByPopularity");
		return $selectedThemeId;
	}

	//Selects a random theme with equal probability for all themes, not caring for number of votes
	public static function SelectRandomTheme(&$ThemeData){
		AddActionLog("SelectRandomTheme");
		StartTimer("SelectRandomTheme");

		$selectedThemeId = -1;

		$availableThemes = Array();
		foreach($ThemeData->ThemeModels as $id => $themeModel){
			$themeOption = Array();

			if($themeModel->Banned){
				continue;
			}

			$themeOption["theme_id"] = $themeModel->Id;

			$availableThemes[] = $themeOption;
		}

		if(count($availableThemes) > 0){
			$selectedIndex = rand(0, count($availableThemes) -1);
			$selectedThemeId = $availableThemes[$selectedIndex]["theme_id"];
		}

		StopTimer("SelectRandomTheme");
		return $selectedThemeId;
	}
}

?>
