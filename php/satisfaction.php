<?php

function SubmitSatisfaction(&$loggedInUser, $satisfactionQuestionId, $score){
	global $dbConn, $ip, $userAgent;
	AddActionLog("SubmitSatisfaction");
	StartTimer("SubmitSatisfaction");

	if($score < -5){
		StopTimer("SubmitSatisfaction");
		return;
	}
	if($score > 5){
		StopTimer("SubmitSatisfaction");
		return;
	}

	$username = trim($loggedInUser->Username);

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

	StopTimer("SubmitSatisfaction");
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

?>