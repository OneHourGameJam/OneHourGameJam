<?php

function CastVoteForAdmin($subjectUsername, $voteType){
	global $dbConn, $ip, $userAgent, $loggedInUser, $actionResult;

	//Authorize user (logged in)
	$user = IsAdmin();
	if($user === false){
		$actionResult = "NOT_LOGGED_IN";
		AddAuthorizationWarning("Not logged in.", false);
		return;
	}

	//Authorize user (is admin)
	if(IsAdmin() === false){
		$actionResult = "NOT_AHTORIZED";
		AddAuthorizationWarning("Only admins can delete themes.", false);
		return;
	}

	$escapedIP = mysqli_real_escape_string($dbConn, $ip);
	$escapedUserAgent = mysqli_real_escape_string($dbConn, $userAgent);
	$escapedVoterUsername = mysqli_real_escape_string($dbConn, $loggedInUser["username"]);
	$escapedSubjectUsername = mysqli_real_escape_string($dbConn, $subjectUsername);
	$escapedVoteType = mysqli_real_escape_string($dbConn, $voteType);

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
				WHERE vote_voter_username = '$escapedVoterUsername'
				  AND vote_type = '$escapedVoteType'
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
			$actionResult = "INVALID_VOTE_TYPE";
			AddDataWarning("Failed to cast vote for admin: Invalid vote type", false);
			return;
	}

	$sql = "
		SELECT vote_id
		FROM admin_vote
		WHERE vote_voter_username = '$escapedVoterUsername'
		  AND vote_subject_username = '$escapedSubjectUsername'
	";
	$data = mysqli_query($dbConn, $sql);
	$sql = "";

	if($info = mysqli_fetch_array($data)){
		//A vote already exists, update it
		$sql = "
			UPDATE admin_vote
			SET vote_type = '$escapedVoteType'
			WHERE vote_voter_username = '$escapedVoterUsername'
			  AND vote_subject_username = '$escapedSubjectUsername'
		";
		$data = mysqli_query($dbConn, $sql);
		$sql = "";
		$actionResult = "SUCESS_UPDATE";
		AddDataSuccess("Admin vote updated", false);
	}else{
		//New vote for admin
		$sql = "
			INSERT INTO admin_vote
			(vote_id, vote_datetime, vote_ip, vote_user_agent, vote_voter_username, vote_subject_username, vote_type)
			VALUES
			(null,
			Now(),
			'$escapedIP',
			'$escapedUserAgent',
			'$escapedVoterUsername',
			'$escapedSubjectUsername',
			'$escapedVoteType');
		";
		$data = mysqli_query($dbConn, $sql);
		$sql = "";
		$actionResult = "SUCESS_INSERT";
		AddDataSuccess("Admin vote cast", false);
	}
}

if(IsAdmin()){
    $voteSubjectUsername = $_POST["adminVoteSubjectUsername"];
    $voteType = $_POST["adminVoteType"];
    CastVoteForAdmin($voteSubjectUsername, $voteType);
}

?>