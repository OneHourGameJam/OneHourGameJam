<?php

function LoadPolls(){
	global $dbConn, $dictionary, $polls, $loggedInUser;
	AddActionLog("LoadPolls");
	StartTimer("LoadPolls");

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
	StopTimer("LoadPolls");
}

function LoadSatisfaction(&$config){
	global $dbConn;
	AddActionLog("LoadSatisfaction");
	StartTimer("LoadSatisfaction");

	$satisfaction = Array();

	$sql = "
		SELECT
			satisfaction_question_id,
			AVG(satisfaction_score) AS average_score,
			COUNT(satisfaction_score) AS submitted_scores
		FROM satisfaction
		GROUP BY satisfaction_question_id
	";
	$data = mysqli_query($dbConn, $sql);
	$sql = "";

	//Get data
	while($info = mysqli_fetch_array($data)){
		$row = Array();

		$questionId = $info["satisfaction_question_id"];
		$averageScore = $info["average_score"];
		$submittedScores = $info["submitted_scores"];

		$row["question_id"] = $questionId;
		$row["average_score"] = $averageScore;
		$row["submitted_scores"] = $submittedScores;
		$row["enough_scores_to_show_satisfaction"] = $submittedScores >= $config["SATISFACTION_RATINGS_TO_SHOW_SCORE"]["VALUE"];

		$satisfaction[$questionId] = $row;
	}

	$sql = "
		SELECT
			satisfaction_question_id,
			satisfaction_score,
			COUNT(1) AS votes_for_score
		FROM satisfaction
		GROUP BY satisfaction_question_id, satisfaction_score
	";
	$data = mysqli_query($dbConn, $sql);
	$sql = "";

	//Get data
	while($info = mysqli_fetch_array($data)){
		$questionId = $info["satisfaction_question_id"];
		$satisfactionScore = $info["satisfaction_score"];
		$votesForScore = $info["votes_for_score"];

		$satisfaction[$questionId]["scores"][$satisfactionScore] = $votesForScore;
	}

	StopTimer("LoadSatisfaction");
	return $satisfaction;
}

function SubmitSatisfaction($satisfactionQuestionId, $score){
	global $dbConn, $ip, $userAgent, $loggedInUser;
	AddActionLog("SubmitSatisfaction");
	StartTimer("SubmitSatisfaction");

	if($score < -5){
		AddDataWarning("Invalid satisfaction score", false);
		StopTimer("SubmitSatisfaction");
		return;
	}
	if($score > 5){
		AddDataWarning("Invalid satisfaction score", false);
		StopTimer("SubmitSatisfaction");
		return;
	}

	$username = trim($loggedInUser["username"]);

	$escapedSatisfactionQuestionId = mysqli_real_escape_string($dbConn, $satisfactionQuestionId);
	$escapedIP = mysqli_real_escape_string($dbConn, $ip);
	$escapedUserAgent = mysqli_real_escape_string($dbConn, $userAgent);
	$escapedUsername = mysqli_real_escape_string($dbConn, $username);
	$escapedScore = mysqli_real_escape_string($dbConn, $score);

	$sql = "
		INSERT INTO satisfaction
		(satisfaction_id,
		satisfaction_datetime,
		satisfaction_ip,
		satisfaction_user_agent,
		satisfaction_question_id,
		satisfaction_username,
		satisfaction_score)
		VALUES
		(null,
		Now(),
		'$escapedIP',
		'$escapedUserAgent',
		'$escapedSatisfactionQuestionId',
		'$escapedUsername',
		'$escapedScore');";
	$data = mysqli_query($dbConn, $sql);
	$sql = "";

	AddDataSuccess("Satisfaction score submitted", false);
	StopTimer("SubmitSatisfaction");
}

function LoadLoggedInUsersAdminVotes(&$loggedInUser){
	global $dbConn, $dictionary;
	AddActionLog("LoadLoggedInUsersAdminVotes");
	StartTimer("LoadLoggedInUsersAdminVotes");

	$loggedInUserAdminVotes = Array();

	$escapedVoterUsername = mysqli_real_escape_string($dbConn, $loggedInUser["username"]);

	$sql = "
		SELECT vote_subject_username, vote_type
		FROM admin_vote
		WHERE vote_voter_username = '$escapedVoterUsername';
	";
	$data = mysqli_query($dbConn, $sql);
	$sql = "";

	while($info = mysqli_fetch_array($data)){
		$adminVoteData = Array();

		$adminVoteData["subject_username"] = $info["vote_subject_username"];
		$adminVoteData["vote_type"] = $info["vote_type"];

		$loggedInUserAdminVotes[] = $adminVoteData;
    }

	StopTimer("LoadLoggedInUsersAdminVotes");
	return $loggedInUserAdminVotes;
}

function LoadAdminVotes(){
	global $dbConn, $dictionary;
	AddActionLog("LoadAdminVotes");
	StartTimer("LoadAdminVotes");

	$adminVotes = Array();

	$sql = "
		SELECT v.vote_subject_username, v.vote_type
		FROM admin_vote v, user u
        WHERE v.vote_voter_username = u.user_username
          AND u.user_role = 1
	";
	$data = mysqli_query($dbConn, $sql);
	$sql = "";

	while($info = mysqli_fetch_array($data)){
		$adminVoteData = Array();

		$adminVoteData["subject_username"] = $info["vote_subject_username"];
		$adminVoteData["vote_type"] = $info["vote_type"];

		$adminVotes[] = $adminVoteData;
	}

	StopTimer("LoadAdminVotes");
	return $adminVotes;
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

function GetSatisfactionVotesOfUserFormatted($username){
	global $dbConn;
	AddActionLog("GetSatisfactionVotesOfUserFormatted");
	StartTimer("GetSatisfactionVotesOfUserFormatted");

	$escapedUsername = mysqli_real_escape_string($dbConn, $username);
	$sql = "
		SELECT *
		FROM satisfaction
		WHERE satisfaction_username = '$escapedUsername';
	";
	$data = mysqli_query($dbConn, $sql);
	$sql = "";

	StopTimer("GetSatisfactionVotesOfUserFormatted");
	return ArrayToHTML(MySQLDataToArray($data));
}

function GetAdminVotesCastByUserFormatted($username){
	global $dbConn;
	AddActionLog("GetAdminVotesCastByUserFormatted");
	StartTimer("GetAdminVotesCastByUserFormatted");

	$escapedUsername = mysqli_real_escape_string($dbConn, $username);
	$sql = "
		SELECT *
		FROM admin_vote
		WHERE vote_voter_username = '$escapedUsername';
	";
	$data = mysqli_query($dbConn, $sql);
	$sql = "";

	StopTimer("GetAdminVotesCastByUserFormatted");
	return ArrayToHTML(MySQLDataToArray($data));
}

function GetAdminVotesForSubjectUserFormatted($username){
	global $dbConn;
	AddActionLog("GetAdminVotesForSubjectUserFormatted");
	StartTimer("GetAdminVotesForSubjectUserFormatted");

	$escapedUsername = mysqli_real_escape_string($dbConn, $username);
	$sql = "
        SELECT vote_id, vote_datetime, 'redacted' AS vote_voter_username, vote_subject_username, 'redacted' AS vote_type
        FROM admin_vote
        WHERE vote_subject_username = '$escapedUsername';
	";
	$data = mysqli_query($dbConn, $sql);
	$sql = "";

	StopTimer("GetAdminVotesForSubjectUserFormatted");
	return ArrayToHTML(MySQLDataToArray($data));
}


?>