<?php

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