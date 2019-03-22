<?php

function RenderPolls(&$pollData){
	AddActionLog("RenderPolls");
	StartTimer("RenderPolls");
	
	$render = Array();

	foreach($pollData->PollModels as $pollID => $pollModel){
		$poll = Array();

		$pollID = $pollModel->Id;
		$totalVotes = 0;

		$poll["QUESTION"] = $pollModel->Question;
		$poll["POLL_ID"] = $pollID;
		$poll["USER_VOTED_IN_POLL"] = false;
		$poll["OPTIONS"] = Array();
		$poll["IS_ACTIVE"] = $pollModel->IsActive;
		
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

		$poll["TOTAL_Votes"] = $totalVotes;

		//Compute percentages
		if($totalVotes > 0){
			foreach($poll["OPTIONS"] as $i => $pollOptionModel){
				$optionPercentage = $pollOptionModel["VOTES"] / $totalVotes;
				$poll["OPTIONS"][$i]["PERCENTAGE"] = $optionPercentage;
				$poll["OPTIONS"][$i]["PERCENTAGE_DISPLAY"] = (intval($optionPercentage * 100)) . "%";
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