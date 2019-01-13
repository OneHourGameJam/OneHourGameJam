<?php


chdir("../../");
include_once("php/site.php");

$usr = IsLoggedIn();

if($usr == false){
	print json_encode(Array("ERROR" => "Not logged in"));
	die();
}

$clean_ip = mysqli_real_escape_string($dbConn, $ip);
$clean_userAgent = mysqli_real_escape_string($dbConn, $userAgent);
$clean_username = mysqli_real_escape_string($dbConn, $usr["username"]);

if(!isset($_GET["pollID"])){
	print json_encode(Array("ERROR" => "poll ID not set"));
	die();
}

if(!isset($_GET["optionID"])){
	print json_encode(Array("ERROR" => "Option ID not set"));
	die();
}

$pollID = intval(trim($_GET["pollID"]));
$optionID = intval(trim($_GET["optionID"]));

//Check if the poll/option combination exists, that the poll wasn't deleted, that it's already started and that it hasn't expired
$sql = "SELECT 1 FROM poll p, poll_option o WHERE p.poll_deleted != 1 AND NOW() BETWEEN p.poll_start_datetime AND p.poll_end_datetime AND p.poll_id = o.option_poll_id AND poll_id = $pollID AND o.option_id = $optionID";
$data = mysqli_query($dbConn, $sql);
$sql = "";

if(mysqli_num_rows($data) == 0){
	print json_encode(Array("ERROR" => "Poll / option combination does not exist."));
	die();
}

//Check if there is already a vote by this user for this poll/option combination
$sql = "SELECT vote_id, vote_deleted FROM poll_vote WHERE vote_option_id = $optionID and vote_username = '$clean_username'";
$data = mysqli_query($dbConn, $sql);
$sql = "";

if($pollVote = mysqli_fetch_array($data)){
	//toggle poll vote

	$newVote = 0;
	if(intval($pollVote["vote_deleted"]) == 0){
		$newVote = 1;
	}

	$voteID = $pollVote["vote_id"];
	$sql = "UPDATE poll_vote SET vote_deleted = $newVote WHERE vote_id = $voteID";
	$data = mysqli_query($dbConn, $sql);
	$sql = "";
	print json_encode(Array("SUCCESS" => "Vote updated."));
}else{
	//Insert new poll vote
	$sql = "
	INSERT INTO poll_vote
	(vote_id, vote_option_id, vote_username, vote_deleted)
	VALUES
	(null, $optionID, '$clean_username', 0);";

	$data = mysqli_query($dbConn, $sql);
	$sql = "";
	print json_encode(Array("SUCCESS" => "Vote cast."));
}

?>