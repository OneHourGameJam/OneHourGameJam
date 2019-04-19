<?php

function RenderPolls(&$pollData){
	AddActionLog("RenderPolls");
	StartTimer("RenderPolls");
	
	$render = Array();

	foreach($pollData->PollModels as $pollID => $pollModel){
		$poll = Array();

		$pollID = $pollModel->Id;
		$totalVotes = 0;
		$usersVotedInPoll = $pollModel->UsersVotedInPoll;

		$poll["QUESTION"] = $pollModel->Question;
		$poll["POLL_ID"] = $pollID;
		$poll["USER_VOTED_IN_POLL"] = false;
		$poll["OPTIONS"] = Array();
		$poll["IS_ACTIVE"] = $pollModel->IsActive;
		$poll["USERS_VOTED_IN_POLL"] = $usersVotedInPoll;
		$poll["DATE_STARTED"] = $pollModel->DateStart;
		$poll["DATE_ENDED"] = $pollModel->DateEnd;
		
		foreach($pollModel->Options as $optionID => $optionModel){
			$option = Array();
			
			$optionID = $optionModel->Id;

			$option["OPTION_ID"] = $optionID;
			$option["USER_VOTED"] = false;
			$option["TEXT"] = $optionModel->Text;
			$option["VOTES"] = $optionModel->Votes;

			if(isset($pollData->LoggedInUserPollVotes[$pollID][$optionID]) && $pollData->LoggedInUserPollVotes[$pollID][$optionID] == true){
				$option["USER_VOTED"] = true;
				$poll["USER_VOTED_IN_POLL"] = true;
			}

			$totalVotes += $optionModel->Votes;

			$poll["OPTIONS"][] = $option;
		}

		$poll["TOTAL_VOTES"] = $totalVotes;

		//Compute percentages
		if($totalVotes > 0){
			foreach($poll["OPTIONS"] as $i => $pollOptionModel){
				$optionPercentage = $pollOptionModel["VOTES"] / $totalVotes;
				$poll["OPTIONS"][$i]["PERCENTAGE_OF_ALL_VOTES"] = $optionPercentage;
				$poll["OPTIONS"][$i]["PERCENTAGE_OF_ALL_VOTES_DISPLAY"] = (intval($optionPercentage * 100)) . "%";
			}
		}
		if($usersVotedInPoll > 0){
			foreach($poll["OPTIONS"] as $i => $pollOptionModel){
				$optionPercentage = $pollOptionModel["VOTES"] / $usersVotedInPoll;
				$poll["OPTIONS"][$i]["PERCENTAGE_OF_USERS_VOTES"] = $optionPercentage;
				$poll["OPTIONS"][$i]["PERCENTAGE_OF_USERS_VOTES_DISPLAY"] = (intval($optionPercentage * 100)) . "%";
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
		$poll["js_formatted_options_list"] = implode(",", $jsFormattedPollOptionsList);
		$poll["js_formatted_votes_list"] = implode(",", $jsFormattedPollVotesList);
		$poll["js_formatted_fill_color_list"] = implode(",", $jsFormattedPollFillColorList);
		$poll["js_formatted_border_color_list"] = implode(",", $jsFormattedPollBorderColorList);
		$poll["js_formatted_user_votes_percentage_list"] = implode(",", $jsFormattedPollUserPercentagesList);
			
		if($poll["IS_ACTIVE"]){
			$render["ACTIVE_POLLS"][] = $poll;
		}
		$render["LIST"][] = $poll;
	}

	StopTimer("RenderPolls");
	return $render;
}


?>