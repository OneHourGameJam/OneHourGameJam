<?php
chdir("../../");
include_once("php/site.php");

if($loggedInUser == false){
	print json_encode(Array("ERROR" => "Not logged in"));
	die();
}

if(!isset($_GET["pollID"])){
	print json_encode(Array("ERROR" => "poll ID not set"));
	die();
}

if(!isset($_GET["optionID"])){
	print json_encode(Array("ERROR" => "Option ID not set"));
	die();
}

$pollId = intval(trim($_GET["pollID"]));
$pollOptionId = intval(trim($_GET["optionID"]));

//Check if the poll/option combination exists, that the poll wasn't deleted, that it's already started and that it hasn't expired
$data = $pollDbInterface->SelectIfPollAndPollOptionCombinationExists($pollId, $pollOptionId);

if(mysqli_num_rows($data) == 0){
	print json_encode(Array("ERROR" => "Poll / option combination does not exist."));
	die();
}

//Check if there is already a vote by this user for this poll/option combination
$data = $pollVoteDbInterface->SelectUserVoteForOption($loggedInUser->Id, $pollOptionId);

if($pollVote = mysqli_fetch_array($data)){
	//toggle poll vote

	$newVote = 0;
	if(intval($pollVote["vote_deleted"]) == 0){
		$newVote = 1;
	}

	$voteId = $pollVote["vote_id"];
	$pollVoteDbInterface->UpdateIsDeleted($voteId, $newVote);
	print json_encode(Array("SUCCESS" => "Vote updated."));
}else{
	//Insert new poll vote
	$pollVoteDbInterface->Insert($loggedInUser->Id, $pollOptionId);
	print json_encode(Array("SUCCESS" => "Vote cast."));
}

?>