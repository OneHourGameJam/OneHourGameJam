<?php

class JamPresenter{
	public static function RenderJam(&$configData, &$userData, &$gameData, &$jamModel, &$jamData, &$platformData, &$platformGameData, &$satisfactionData, &$loggedInUser, $nonDeletedJamCounter, $renderDepth){
		AddActionLog("RenderJam");
		StartTimer("RenderJam");

		$jamViewModel = new JamViewModel();

		$streamerUserId = $jamModel->StreamerUserId;
		$streamerTwitchUsername = $jamModel->StreamerTwitchUsername;

		$jamViewModel->jam_id = $jamModel->Id;
		$jamViewModel->scheduler_user_id = $jamModel->SchedulerUserId;
		$jamViewModel->jam_number = $jamModel->JamNumber;
		$jamViewModel->theme_id = $jamModel->ThemeId;
		$jamViewModel->theme = $jamModel->Theme;
		$jamViewModel->start_time = $jamModel->StartTime;
		$jamViewModel->state = $jamModel->State;
		$jamViewModel->streamer_user_id = $jamModel->StreamerUserId;
		$jamViewModel->streamer_twitch_username = preg_replace("/[^0-9a-zA-Z ]/m", "", $jamModel->StreamerTwitchUsername);
		$jamViewModel->default_icon_url = $jamModel->DefaultIconUrl;

		if($streamerUserId != "" && $streamerTwitchUsername != ""){
			$jamViewModel->streamer_is_set = 1;
			$jamViewModel->streamer_username = $userData->UserModels[$streamerUserId]->Username;
			$jamViewModel->streamer_user_display_name = $userData->UserModels[$streamerUserId]->DisplayName;
			if($loggedInUser->Id == $streamerUserId){
				$jamViewModel->user_is_streamer_for_jam = 1;
			}
		}
		
		$now = new DateTime();
		$datetime = new DateTime($jamModel->StartTime . " UTC");
		$timeSinceJamEndedInSeconds = $now->getTimestamp() - ($datetime->getTimestamp() + ($configData->ConfigModels[CONFIG_JAM_DURATION]->Value * 60));
		
		if($timeSinceJamEndedInSeconds > 0 && $timeSinceJamEndedInSeconds < ($configData->ConfigModels[CONFIG_TWITCH_CHECK_STREAM_AFTER_JAM_END_MINUTES]->Value * 60)){
			$jamViewModel->in_straming_period = 1;
		}

		if($jamModel->SchedulerUserId == OVERRIDE_AUTOMATIC_NUM){
			$jamViewModel->scheduler_username = OVERRIDE_AUTOMATIC;
			$jamViewModel->scheduler_display_name = OVERRIDE_AUTOMATIC;
		} else if($jamModel->SchedulerUserId == OVERRIDE_LEGACY_NUM){
			$jamViewModel->scheduler_username = OVERRIDE_LEGACY;
			$jamViewModel->scheduler_display_name = OVERRIDE_LEGACY;
		} else {
			$jamViewModel->scheduler_username = $userData->UserModels[$jamModel->SchedulerUserId]->Username;
			$jamViewModel->scheduler_display_name = $userData->UserModels[$jamModel->SchedulerUserId]->DisplayName;
		}

		if($jamModel->Deleted == 1){
			$jamViewModel->jam_deleted = 1;
		}

		$jamViewModel->theme_visible = $jamModel->Theme; //Theme is visible to admins
		$jamViewModel->jam_number_ordinal = ordinal(intval($jamModel->JamNumber));
		$jamViewModel->date = date("F jS Y", strtotime($jamModel->StartTime));
		$jamViewModel->time = date("H:i", strtotime($jamModel->StartTime));

		//Jam Colors
		$jamViewModel->colors = Array();
		foreach($jamModel->Colors as $num => $color){
			$jamViewModel->colors[] = Array("number" => $num, "color" => "#".$color, "color_hex" => $color);
		}
		$jamViewModel->colors_input_string = implode("-", $jamModel->Colors);

		$jamViewModel->minutes_to_jam = floor((strtotime($jamModel->StartTime ." UTC") - time()) / 60);

		//Games in jam
		$jamViewModel->entries = Array();
		$jamViewModel->entries_count = 0;
		foreach($gameData->GameModels as $j => $gameModel){
			if($gameModel->JamId == $jamViewModel->jam_id){
				if(($renderDepth & RENDER_DEPTH_GAMES) > 0){
					$jamViewModel->entries[] = GamePresenter::RenderGame($userData, $gameModel, $jamData, $platformData, $platformGameData, $renderDepth & ~RENDER_DEPTH_JAMS);
				}

				if(!$gameModel->Deleted){
					//Has logged in user participated in this jam?
					if($loggedInUser !== false){
						if($loggedInUser->Id == $gameModel->AuthorUserId){
							$jamViewModel->user_participated_in_jam = 1;
						}
					}

					//Count non-deleted entries in jam
					$jamViewModel->entries_count += 1;
				}
			}
		}
		$jamViewModel->entries = array_reverse($jamViewModel->entries);

		//Hide theme of not-yet-started jams
		$now = new DateTime();
		$datetime = new DateTime($jamViewModel->start_time . " UTC");
		$timeUntilJam = date_diff($datetime, $now);

		$jamViewModel->first_jam = $nonDeletedJamCounter == 1;
		$jamViewModel->entries_visible = $nonDeletedJamCounter <= 2;

		if($datetime > $now){
			$jamViewModel->theme = "Not yet announced";
			$jamViewModel->jam_started = false;
			if($timeUntilJam->days > 0){
				$jamViewModel->time_left = $timeUntilJam->format("%a days %H:%I:%S");
			}else if($timeUntilJam->h > 0){
				$jamViewModel->time_left = $timeUntilJam->format("%H:%I:%S");
			}else  if($timeUntilJam->i > 0){
				$jamViewModel->time_left = $timeUntilJam->format("%I:%S");
			}else if($timeUntilJam->s > 0){
				$jamViewModel->time_left = $timeUntilJam->format("%S seconds");
			}else{
				$jamViewModel->time_left = "Now!";
			}
		}else{
			$jamViewModel->jam_started = true;
		}
		
		$jamViewModel->satisfaction = "No Data";
		if(isset($satisfactionData->SatisfactionModels["JAM_".$jamViewModel->jam_number])){
			$arrayId = "JAM_".$jamViewModel->jam_number;

			$satisfactionSum = 0;
			$satisfactionCount = 0;
			foreach($satisfactionData->SatisfactionModels[$arrayId]->Scores as $score => $votes){
				$satisfactionSum += $score * $votes;
				$satisfactionCount += $votes;
			}
			$satisfactionAverage = $satisfactionSum / $satisfactionCount;

			$jamViewModel->satisfaction_average_score = $satisfactionAverage;
			$jamViewModel->satisfaction_submitted_scores = $satisfactionCount;
			$jamViewModel->enough_scores_to_show_satisfaction = $satisfactionCount >= $configData->ConfigModels[CONFIG_SATISFACTION_RATINGS_TO_SHOW_SCORE]->Value;
			$jamViewModel->score_minus_5 = $satisfactionData->SatisfactionModels[$arrayId]->Scores[-5];
			$jamViewModel->score_minus_4 = $satisfactionData->SatisfactionModels[$arrayId]->Scores[-4];
			$jamViewModel->score_minus_3 = $satisfactionData->SatisfactionModels[$arrayId]->Scores[-3];
			$jamViewModel->score_minus_2 = $satisfactionData->SatisfactionModels[$arrayId]->Scores[-2];
			$jamViewModel->score_minus_1 = $satisfactionData->SatisfactionModels[$arrayId]->Scores[-1];
			$jamViewModel->score_0 = $satisfactionData->SatisfactionModels[$arrayId]->Scores[0];
			$jamViewModel->score_plus_1 = $satisfactionData->SatisfactionModels[$arrayId]->Scores[1];
			$jamViewModel->score_plus_2 = $satisfactionData->SatisfactionModels[$arrayId]->Scores[2];
			$jamViewModel->score_plus_3 = $satisfactionData->SatisfactionModels[$arrayId]->Scores[3];
			$jamViewModel->score_plus_4 = $satisfactionData->SatisfactionModels[$arrayId]->Scores[4];
			$jamViewModel->score_plus_5 = $satisfactionData->SatisfactionModels[$arrayId]->Scores[5];
		}

		$jamViewModel->timer_code = gmdate("Y-m-d", strtotime($jamModel->StartTime. " UTC"))."T".gmdate("H:i", strtotime($jamModel->StartTime. " UTC")).":00Z";

		StopTimer("RenderJam");
		return $jamViewModel;
	}

	public static function RenderSubmitJam(&$configData, &$userData, &$gameData, &$jamModel, &$jamData, &$platformData, &$platformGameData, &$satisfactionData, &$loggedInUser, $renderDepth){
		AddActionLog("RenderSubmitJam");

		return JamPresenter::RenderJam($configData, $userData, $gameData, $jamModel, $jamData, $platformData, $platformGameData, $satisfactionData, $loggedInUser, 0, $renderDepth);
	}

	public static function RenderJams(&$configData, &$userData, &$gameData, &$jamData, &$platformData, &$platformGameData, &$satisfactionData, &$loggedInUser, $renderDepth, $loadAll){
		AddActionLog("RenderJams");
		StartTimer("RenderJams");

		$jamsViewModel = new JamsViewModel();
		$suggestedNextGameJamTime = GetSuggestedNextJamDateTime($configData);
		$jamsViewModel->next_jam_timer_code = gmdate("Y-m-d", $suggestedNextGameJamTime)."T".gmdate("H:i", $suggestedNextGameJamTime).":00Z";

		$nonDeletedJamCounter = 0;
		$latestStartedJamFound = false;
		$currentJam = GetCurrentJamNumberAndId();

		$jamsToLoad = $configData->ConfigModels[CONFIG_JAMS_TO_LOAD]->Value;

		$allJamsLoaded = true;
		$jamsViewModel->current_jam = $currentJam["NUMBER"] !== 0;

		foreach($jamData->JamModels as $i => $jamModel){
			if($jamModel->Deleted != 1){
				$nonDeletedJamCounter += 1;
			}

			if($loadAll || $nonDeletedJamCounter <= $jamsToLoad)
			{
				if(($renderDepth & RENDER_DEPTH_JAMS) > 0){
					$jamViewModel = JamPresenter::RenderJam($configData, $userData, $gameData, $jamModel, $jamData, $platformData, $platformGameData, $satisfactionData, $loggedInUser, $nonDeletedJamCounter, $renderDepth);
					
					if($configData->ConfigModels[CONFIG_CAN_SUBMIT_TO_PAST_JAMS]->Value){
						$jamViewModel->can_user_submit_to_jam = !$jamViewModel->user_participated_in_jam && $jamViewModel->jam_started;
					}

					$now = time();
					$datetime = strtotime($jamViewModel->start_time . " UTC");
					if($datetime > $now){
						$jamsViewModel->next_jam_timer_code = gmdate("Y-m-d", $datetime)."T".gmdate("H:i", $datetime).":00Z";
					}else{
						if(!isset($jamViewModel->jam_deleted)){
							if($latestStartedJamFound == false){
								$jamViewModel->is_latest_started_jam = 1;
								$jamViewModel->can_user_submit_to_jam = !$jamViewModel->user_participated_in_jam;
								$latestStartedJamFound = true;
							}
						}
					}
		
					$jamsViewModel->LIST[] = $jamViewModel;
				}
				if($currentJam["ID"] == $jamModel->Id){
					$jamsViewModel->current_jam = JamPresenter::RenderJam($configData, $userData, $gameData, $jamModel, $jamData, $platformData, $platformGameData, $satisfactionData, $loggedInUser, $nonDeletedJamCounter, $renderDepth);
				}
			}else{
				$allJamsLoaded = false;
				continue;
			}
		}

		$jamsViewModel->all_jams_loaded = $allJamsLoaded;
		$jamsViewModel->all_jams_count = $nonDeletedJamCounter;

		StopTimer("RenderJams");
		return $jamsViewModel;
	}
}

?>