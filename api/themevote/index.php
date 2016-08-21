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

if(!isset($_GET["themeID"])){
	print json_encode(Array("ERROR" => "Theme ID not set"));
	die();
}

if(!isset($_GET["vote"])){
	print json_encode(Array("ERROR" => "Vote type not set"));
	die();
}

$voteThemeID = intval(trim($_GET["themeID"]));
$vote = intval($_GET["vote"]);


//Check if the theme exists
$sql = "SELECT theme_id FROM theme WHERE theme_deleted != 1 AND theme_id = $voteThemeID";
$data = mysqli_query($dbConn, $sql);
$sql = "";

if(mysqli_num_rows($data) == 0){
	print json_encode(Array("ERROR" => "Theme does not exist."));
	die();
}

//Check if there is already a vote by this user for this theme
$sql = "SELECT themevote_id FROM themevote WHERE themevote_theme_id = $voteThemeID AND themevote_username = '$clean_username'";
$data = mysqli_query($dbConn, $sql);
$sql = "";

if($themeVote = mysqli_fetch_array($data)){
	//Undelete themevote
	$themeVoteID = $themeVote["themevote_id"];
	$sql = "UPDATE themevote SET themevote_type = $vote WHERE themevote_id = $themeVoteID";
	$data = mysqli_query($dbConn, $sql);
	$sql = "";
	print json_encode(Array("SUCCESS" => "Vote cast."));
}else{
	//Insert new themevote
	$sql = "
	INSERT INTO themevote
		(themevote_datetime, themevote_ip, themevote_user_agent, themevote_theme_id, themevote_username, themevote_type)
		VALUES
		(Now(), '$clean_ip', '$clean_userAgent', $voteThemeID, '$clean_username', $vote);";
	$data = mysqli_query($dbConn, $sql);
	$sql = "";
	print json_encode(Array("SUCCESS" => "Vote updated."));
}

?>