<?php

function CastVoteForAdmin($subjectUserId, $voteType){
	global $ip, $userAgent, $loggedInUser, $adminVoteDbInterface;

	//Authorize user (logged in)
	if($loggedInUser === false){
		return "NOT_LOGGED_IN";
	}

	//Authorize user (is admin)
	if(IsAdmin($loggedInUser) === false){
		return "NOT_AUTHORIZED";
	}

	//Delete an admin's existing vote for the same user, if this exists
	$data = $adminVoteDbInterface->SelectSingleVoteIdByVoterAndSubjectUserId($loggedInUser->Id, $subjectUserId);

	if($info = mysqli_fetch_array($data)){
		$voteId = $info["vote_id"];
		$adminVoteDbInterface->Delete($voteId);
	}

	switch($voteType){
		case "FOR":
		case "NEUTRAL":
		case "AGAINST":
			break;
		case "SPONSOR":
		case "VETO":
			//Each admin can sponsor and veto only one candidate
			$data = $adminVoteDbInterface->SelectSingleVoteIdByVoterUserIdAndVoteType($loggedInUser->Id, $voteType);

			if($info = mysqli_fetch_array($data)){
				$voteId = $info["vote_id"];
				$adminVoteDbInterface->Delete($voteId);
			}
			break;
		default:
			return "INVALID_VOTE_TYPE";
	}

	$adminVoteDbInterface->Insert($ip, $userAgent, $loggedInUser->Id, $subjectUserId, $voteType);
	
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