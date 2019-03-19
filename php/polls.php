<?php

function LoadPolls(){
	global $dbConn;
	AddActionLog("LoadPolls");
	StartTimer("LoadPolls");

	$polls = Array();
	
	$sql = "
		SELECT * FROM
		(SELECT *, NOW() BETWEEN p.poll_start_datetime AND p.poll_end_datetime AS is_active FROM poll p, poll_option o WHERE p.poll_deleted = 0 and p.poll_id = o.option_poll_id) a
		LEFT JOIN
		(SELECT vote_option_id, count(1) AS vote_num FROM poll_vote v WHERE vote_deleted = 0 GROUP BY v.vote_option_id) b
		ON (a.option_id = b.vote_option_id)
	";
	$data = mysqli_query($dbConn, $sql);
	$sql = "";

	while($pollData = mysqli_fetch_array($data)){
		$pollID = intval($pollData["poll_id"]);
		$pollQuestion = $pollData["poll_question"];
		$pollType = $pollData["poll_type"];
		$pollDateStart = $pollData["poll_start_datetime"];
		$pollDateEnd = $pollData["poll_end_datetime"];
		$pollIsActive = intval($pollData["is_active"]);
		$optionID = intval($pollData["option_id"]);
		$optionText = $pollData["option_poll_text"];
		$optionVotes = intval($pollData["vote_num"]);

		if(!isset($polls[$pollID])){
			$poll = Array();

			$poll["POLL_ID"] = $pollID;
			$poll["QUESTION"] = $pollQuestion;
			$poll["TYPE"] = $pollType;
			$poll["DATE_START"] = $pollDateStart;
			$poll["DATE_END"] = $pollDateEnd;
			$poll["IS_ACTIVE"] = $pollIsActive;
			$poll["OPTIONS"] = Array();

			$polls[$pollID] = $poll;
		}

		$polls[$pollID]["OPTIONS"][$optionID] = Array();
		$polls[$pollID]["OPTIONS"][$optionID]["OPTION_ID"] = $optionID;
		$polls[$pollID]["OPTIONS"][$optionID]["TEXT"] = $optionText;
		$polls[$pollID]["OPTIONS"][$optionID]["VOTES"] = $optionVotes;
	}

	StopTimer("LoadPolls");
	return $polls;
}

function LoadLoggedInUserPollVotes(&$loggedInUser){
	global $dbConn;
	AddActionLog("LoadLoggedInUserPollVotes");
	StartTimer("LoadLoggedInUserPollVotes");

	$loggedInUserPollVotes = Array();
	
	//Get data about logged in user's votes
	if($loggedInUser !== false){
		$escapedUsername = mysqli_real_escape_string($dbConn, $loggedInUser->Username);

		$sql = "
			SELECT o.option_poll_id, o.option_id
			FROM poll_vote v, poll_option o
			WHERE v.vote_option_id = o.option_id
			  AND v.vote_deleted != 1
			  AND v.vote_username = '".$escapedUsername."'
		";
		$data = mysqli_query($dbConn, $sql);
		$sql = "";

		//Get data
		while($userVoteData = mysqli_fetch_array($data)){
			$votePollID = intval($userVoteData["option_poll_id"]);
			$voteOptionID = intval($userVoteData["option_id"]);
			$loggedInUserPollVotes[$votePollID][$voteOptionID] = true;
		}
	}

	StopTimer("LoadLoggedInUserPollVotes");
	return $loggedInUserPollVotes;
}

function RenderPolls(&$polls, &$loggedInUserPollVotes){
	AddActionLog("RenderPolls");
	StartTimer("RenderPolls");
	
	$render = Array();

	//Process data
	foreach($polls as $pollID => $pollData){
		$poll = Array();

		$pollID = $pollData["POLL_ID"];
		$totalVotes = 0;

		$poll["QUESTION"] = $pollData["QUESTION"];
		$poll["POLL_ID"] = $pollID;
		$poll["USER_VOTED_IN_POLL"] = false;
		$poll["OPTIONS"] = Array();
		$poll["IS_ACTIVE"] = $pollData["IS_ACTIVE"];
		
		foreach($pollData["OPTIONS"] as $optionID => $optionData){
			$option = Array();
			
			$optionID = $optionData["OPTION_ID"];

			$option["OPTION_ID"] = $optionID;
			$option["USER_VOTED"] = false;
			$option["TEXT"] = $optionData["TEXT"];
			$option["VOTES"] = $optionData["VOTES"];

			if(isset($loggedInUserPollVotes[$pollID][$optionID]) && $loggedInUserPollVotes[$pollID][$optionID] == true){
				$option["USER_VOTED"] = true;
				$poll["USER_VOTED_IN_POLL"] = true;
			}

			$totalVotes += $optionData["VOTES"];

			$poll["OPTIONS"][] = $option;
		}

		$poll["TOTAL_Votes"] = $totalVotes;

		//Compute percentages
		if($totalVotes > 0){
			foreach($poll["OPTIONS"] as $i => $optionData){
				$optionPercentage = $optionData["VOTES"] / $totalVotes;
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

function GetPollVotesOfUserFormatted($username){
	global $dbConn;
	AddActionLog("GetPollVotesOfUserFormatted");
	StartTimer("GetPollVotesOfUserFormatted");

	$escapedUsername = mysqli_real_escape_string($dbConn, $username);
	$sql = "
		SELECT poll.poll_question, poll_option.option_poll_text, poll_vote.*
		FROM poll_vote, poll_option, poll
		WHERE poll_vote.vote_option_id = poll_option.option_id
		  AND poll_option.option_poll_id = poll.poll_id
		  AND poll_vote.vote_username = '$escapedUsername';
	";
	$data = mysqli_query($dbConn, $sql);
	$sql = "";

	StopTimer("GetPollVotesOfUserFormatted");
	return ArrayToHTML(MySQLDataToArray($data));
}


?>