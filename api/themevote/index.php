<?php
chdir("../../");
include_once("php/site.php");

if($loggedInUser == false){
	print json_encode(Array("ERROR" => "Not logged in"));
	die();
}

if(!isset($_GET["themeID"])){
	print json_encode(Array("ERROR" => "Theme ID not set"));
	die();
}

if(!isset($_GET["vote"])){
	print json_encode(Array("ERROR" => "Vote type not set"));
	die();
}

$themeId = intval(trim($_GET["themeID"]));
$vote = intval($_GET["vote"]);


//Check if the theme exists
$data = $themeDbInterface->SelectIfActive($themeId);

if(mysqli_num_rows($data) == 0){
	print json_encode(Array("ERROR" => "Theme does not exist."));
	die();
}

//Check if there is already a vote by this user for this theme
$data = $themeVoteDbInterface->SelectSingle($themeId, $loggedInUser->Id);

if($themeVote = mysqli_fetch_array($data)){
	$themeVoteId = $themeVote["themevote_id"];
	$data = $themeVoteDbInterface->Update($themeVoteId, $vote);
	print json_encode(Array("SUCCESS" => "Vote updated."));
}else{
	$data = $themeVoteDbInterface->Insert($ip, $userAgent, $themeId, $loggedInUser->Id, $vote);
	print json_encode(Array("SUCCESS" => "Vote cast."));
}

?>