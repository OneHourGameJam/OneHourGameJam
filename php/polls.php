<?php

function LoadPolls(){
	global $dbConn, $dictionary, $polls, $loggedInUser;
	
	//Clear public lists which get updated by this function
	$dictionary["polls"] = Array();
	$dictionary["active_polls"] = Array();
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
	
	//Get data
	while($info = mysqli_fetch_array($data)){
		$pollID = intval($info["poll_id"]);
		$pollQuestion = $info["poll_question"];
		$pollType = $info["poll_type"];
		$pollDateStart = $info["poll_start_datetime"];
		$pollDateEnd = $info["poll_end_datetime"];
		$pollIsActive = intval($info["is_active"]);
		$optionID = intval($info["option_id"]);
		$optionText = $info["option_poll_text"];
		$optionVotes = intval($info["vote_num"]);
		
		if(!isset($polls[$pollID])){
			$polls[$pollID] = Array();
			$polls[$pollID]["POLL_ID"] = $pollID;
			$polls[$pollID]["QUESTION"] = $pollQuestion;
			$polls[$pollID]["TYPE"] = $pollType;
			$polls[$pollID]["DATE_START"] = $pollDateStart;
			$polls[$pollID]["DATE_END"] = $pollDateEnd;
			$polls[$pollID]["IS_ACTIVE"] = $pollIsActive;
			
			$polls[$pollID]["OPTIONS"] = Array();
		}
		
		$polls[$pollID]["OPTIONS"][$optionID] = Array();
		$polls[$pollID]["OPTIONS"][$optionID]["OPTION_ID"] = $optionID;
		$polls[$pollID]["OPTIONS"][$optionID]["TEXT"] = $optionText;
		$polls[$pollID]["OPTIONS"][$optionID]["VOTES"] = $optionVotes;
	}
	
	//Get data about logged in user's votes
	if(IsLoggedIn()){
		$sql = "
			SELECT o.option_poll_id, o.option_id
			FROM poll_vote v, poll_option o
			WHERE v.vote_option_id = o.option_id
			  AND v.vote_deleted != 1
			  AND v.vote_username = '".$loggedInUser["username"]."'
		";
		$data = mysqli_query($dbConn, $sql);
		$sql = "";
		
		//Get data
		while($info = mysqli_fetch_array($data)){
			$votePollID = intval($info["option_poll_id"]);
			$voteOptionID = intval($info["option_id"]);
			if(isset($polls[$votePollID])){
				$polls[$votePollID]["OPTIONS"][$voteOptionID]["USER_VOTED"] = 1;
				$polls[$votePollID]["USER_VOTED_IN_POLL"] = 1;
			}
		}
	}
	
	//Process data
	foreach($polls as $pollID => $poll){
		$polls[$pollID]["TOTAL_VOTES"] = 0;
		
		//Compute total votes for each poll
		foreach($poll["OPTIONS"] as $optionID => $option){
			$polls[$pollID]["TOTAL_VOTES"] += $option["VOTES"];
		}
		
		//Compute percentages
		foreach($poll["OPTIONS"] as $optionID => $option){
			$polls[$pollID]["OPTIONS"][$optionID]["PERCENTAGE"] = $option["VOTES"] / $polls[$pollID]["TOTAL_VOTES"];
			$polls[$pollID]["OPTIONS"][$optionID]["PERCENTAGE_DISPLAY"] = (intval($polls[$pollID]["OPTIONS"][$optionID]["PERCENTAGE"] * 100)) . "%";
		}
	}
    
	//Insert into dictionary
	foreach($polls as $pollID => $poll){
		//Remap options so they go from 0..n instead of over their option IDs (Mustache expects this)
		$poll["OPTIONS"] = Array();
		foreach($polls[$pollID]["OPTIONS"] as $optionID => $option){
			$poll["OPTIONS"][] = $option;
		}
		
		$dictionary["polls"][] = $poll;
		if($poll["IS_ACTIVE"]){
			$dictionary["active_polls"][] = $poll;
		}
	}
}


function GetPollVotesOfUserFormatted($username){
	global $dbConn;
	
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
	
	return ArrayToHTML(MySQLDataToArray($data)); 
}


?>