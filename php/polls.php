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
			
		if($poll["IS_ACTIVE"]){
			$render["ACTIVE_POLLS"][] = $poll;
		}
		$render["LIST"][] = $poll;
	}

	StopTimer("RenderPolls");
	return $render;
}


?>