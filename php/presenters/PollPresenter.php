<?php

class PollPresenter{
	public static function RenderPolls(&$pollData){
		AddActionLog("RenderPolls");
		StartTimer("RenderPolls");
		
		$pollsViewModel = new PollsViewModel();

		foreach($pollData->PollModels as $pollID => $pollModel){
			$pollViewModel = new PollViewModel();

			$pollID = $pollModel->Id;
			$totalVotes = 0;
			$usersVotedInPoll = $pollModel->UsersVotedInPoll;

			$pollViewModel->QUESTION = $pollModel->Question;
			$pollViewModel->POLL_ID = $pollID;
			$pollViewModel->USER_VOTED_IN_POLL = false;
			$pollViewModel->OPTIONS = Array();
			$pollViewModel->IS_ACTIVE = $pollModel->IsActive;
			$pollViewModel->USERS_VOTED_IN_POLL = $usersVotedInPoll;
			$pollViewModel->DATE_STARTED = $pollModel->DateStart;
			$pollViewModel->DATE_ENDED = $pollModel->DateEnd;
			
			foreach($pollModel->Options as $optionID => $optionModel){
				$pollOptionsViewModel = new PollOptionsViewModel();
				
				$optionID = $optionModel->Id;

				$pollOptionsViewModel->OPTION_ID = $optionID;
				$pollOptionsViewModel->USER_VOTED = false;
				$pollOptionsViewModel->TEXT = $optionModel->Text;
				$pollOptionsViewModel->VOTES = $optionModel->Votes;

				if(isset($pollData->LoggedInUserPollVotes[$pollID][$optionID]) && $pollData->LoggedInUserPollVotes[$pollID][$optionID] == true){
					$pollOptionsViewModel->USER_VOTED = true;
					$pollViewModel->USER_VOTED_IN_POLL = true;
				}

				$totalVotes += $optionModel->Votes;

				$pollViewModel->OPTIONS[] = $pollOptionsViewModel;
			}

			$pollViewModel->TOTAL_VOTES = $totalVotes;

			//Compute percentages
			if($totalVotes > 0){
				foreach($pollViewModel->OPTIONS as $i => $pollOptionModel){
					$optionPercentage = $pollOptionModel->VOTES / $totalVotes;
					$pollViewModel->OPTIONS[$i]->PERCENTAGE_OF_ALL_VOTES = $optionPercentage;
					$pollViewModel->OPTIONS[$i]->PERCENTAGE_OF_ALL_VOTES_DISPLAY = (intval($optionPercentage * 100)) . "%";
				}
			}
			if($usersVotedInPoll > 0){
				foreach($pollViewModel->OPTIONS as $i => $pollOptionModel){
					$optionPercentage = $pollOptionModel->VOTES / $usersVotedInPoll;
					$pollViewModel->OPTIONS[$i]->PERCENTAGE_OF_USERS_VOTES = $optionPercentage;
					$pollViewModel->OPTIONS[$i]->PERCENTAGE_OF_USERS_VOTES_DISPLAY = (intval($optionPercentage * 100)) . "%";
				}
			}
			

			$jsFormattedPollOptionsList = Array();
			$jsFormattedPollVotesList = Array();
			$jsFormattedPollFillColorList = Array();
			$jsFormattedPollBorderColorList = Array();
			$jsFormattedPollUserPercentagesList = Array();

			//Format for JavaScript charts
			foreach($pollModel->Options as $optionID => $optionModel){
				$votes = $optionModel->Votes;
				$percentage = 0;
				if($totalVotes > 0){
					$percentage = $votes / $totalVotes;
				}
				$userVotesPercentage = 0;
				if($usersVotedInPoll > 0){
					$userVotesPercentage = $votes / $usersVotedInPoll;
				}
				
				$optionText = $optionModel->Text;

				$listEntryId = $votes;
				$safety = 100;
				while(true){
					$listEntryId += 0.0001;
					$safety -= 1;
					if(!isset($jsFormattedPollOptionsList["".$listEntryId])){
						break;
					}
					if($safety <= 0){
						break;
					}
				}

				$jsFormattedPollOptionsList["".$listEntryId] = "\"".str_replace("\"", "\\\"", htmlspecialchars_decode($optionText, ENT_COMPAT | ENT_HTML401 | ENT_QUOTES))."\"";
				$jsFormattedPollVotesList["".$listEntryId] = $votes;
				$jsFormattedPollUserPercentagesList["".$listEntryId] = intval($userVotesPercentage * 100)/100;
				
				$randomR = 0x10 + (rand(0,14) * 0x10);
				$randomG = 0x10 + (rand(0,14) * 0x10);
				$randomB = 0x10 + (rand(0,14) * 0x10);
				$jsFormattedPollFillColorList["".$listEntryId] = "'rgba(".$randomR.", ".$randomG.", ".$randomB.", 0.2)'";
				$jsFormattedPollBorderColorList["".$listEntryId] = "'rgba(".$randomR.", ".$randomG.", ".$randomB.", 1)'";
			}
			krsort($jsFormattedPollOptionsList);
			krsort($jsFormattedPollVotesList);
			krsort($jsFormattedPollFillColorList);
			krsort($jsFormattedPollBorderColorList);
			krsort($jsFormattedPollUserPercentagesList);
			$pollViewModel->js_formatted_options_list = implode(",", $jsFormattedPollOptionsList);
			$pollViewModel->js_formatted_votes_list = implode(",", $jsFormattedPollVotesList);
			$pollViewModel->js_formatted_fill_color_list = implode(",", $jsFormattedPollFillColorList);
			$pollViewModel->js_formatted_border_color_list = implode(",", $jsFormattedPollBorderColorList);
			$pollViewModel->js_formatted_user_votes_percentage_list = implode(",", $jsFormattedPollUserPercentagesList);
				
			if($pollViewModel->IS_ACTIVE){
				$pollsViewModel->ACTIVE_POLLS[] = $pollViewModel;
			}
			$pollsViewModel->LIST[] = $pollViewModel;
		}

		StopTimer("RenderPolls");
		return $pollsViewModel;
	}
}

?>