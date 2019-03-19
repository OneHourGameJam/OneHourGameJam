<?php

function LoadAdminVotes(){
	global $dbConn;
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

function LoadLoggedInUsersAdminVotes(&$loggedInUser){
	global $dbConn;
	AddActionLog("LoadLoggedInUsersAdminVotes");
	StartTimer("LoadLoggedInUsersAdminVotes");

	$loggedInUserAdminVotes = Array();

	if($loggedInUser == false){
		return $loggedInUserAdminVotes;
	}

	$escapedVoterUsername = mysqli_real_escape_string($dbConn, $loggedInUser->Username);

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