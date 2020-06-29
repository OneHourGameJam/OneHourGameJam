<?php

function CastVoteForAdmin($subjectUserId, $voteType){
	global $dbConn, $ip, $userAgent, $loggedInUser;

	//Authorize user (logged in)
	if($loggedInUser === false){
		return "NOT_LOGGED_IN";
	}

	//Authorize user (is admin)
	if(IsAdmin($loggedInUser) === false){
		return "NOT_AUTHORIZED";
	}

	$escapedIP = mysqli_real_escape_string($dbConn, $ip);
	$escapedUserAgent = mysqli_real_escape_string($dbConn, $userAgent);
	$escapedVoterId = mysqli_real_escape_string($dbConn, $loggedInUser->Id);
	$escapedSubjectId = mysqli_real_escape_string($dbConn, $subjectUserId);
	$escapedVoteType = mysqli_real_escape_string($dbConn, $voteType);

	//Delete an admin's existing vote for the same user, if this exists
	$sql = "
		SELECT vote_id
		FROM admin_vote
		WHERE vote_voter_user_id = $escapedVoterId
			AND vote_subject_user_id = $escapedSubjectId;
	";
	$data = mysqli_query($dbConn, $sql);
	$sql = "";

	if($info = mysqli_fetch_array($data)){
		$voteID = $info["vote_id"];
		$escapedVoteID = mysqli_real_escape_string($dbConn, $voteID);

		$sql = "
			DELETE FROM admin_vote
			WHERE vote_id = $escapedVoteID
		";
		$data = mysqli_query($dbConn, $sql);
		$sql = "";
	}

	switch($voteType){
		case "FOR":
		case "NEUTRAL":
		case "AGAINST":
			break;
		case "SPONSOR":
		case "VETO":
			//Each admin can sponsor and veto only one candidate
			$sql = "
				SELECT vote_id
				FROM admin_vote
				WHERE vote_voter_user_id = $escapedVoterId
				  AND vote_type = $escapedVoteType
			";
			$data = mysqli_query($dbConn, $sql);
			$sql = "";

			if($info = mysqli_fetch_array($data)){
				$voteID = $info["vote_id"];
				$escapedVoteID = mysqli_real_escape_string($dbConn, $voteID);

				$sql = "
					DELETE FROM admin_vote
					WHERE vote_id = $escapedVoteID
				";
				$data = mysqli_query($dbConn, $sql);
				$sql = "";
			}
			break;
		default:
			return "INVALID_VOTE_TYPE";
	}

	$sql = "
		SELECT vote_id
		FROM admin_vote
		WHERE vote_voter_user_id = $escapedVoterId
		  AND vote_subject_user_id = $escapedSubjectId
	";
	$data = mysqli_query($dbConn, $sql);
	$sql = "";

	//Cast Vote
	$sql = "
		INSERT INTO admin_vote
		(vote_id, vote_datetime, vote_ip, vote_user_agent, vote_voter_user_id, vote_subject_user_id, vote_type)
		VALUES
		(null,
		Now(),
		'$escapedIP',
		'$escapedUserAgent',
		$escapedVoterId,
		$escapedSubjectId,
		'$escapedVoteType');
	";
	$data = mysqli_query($dbConn, $sql);
	$sql = "";
	
	return "SUCESS_INSERT";
}

function PerformAction($loggedInUser){
	global $_POST;

	if(IsAdmin($loggedInUser) !== false){
		$voteSubjectUserId = $_POST["adminVoteSubjectUserId"];
		$voteType = $_POST["adminVoteType"];
		return CastVoteForAdmin($voteSubjectUserId, $voteType);
	}
}

?>