<?php
chdir("../../");
include_once("php/site.php");

if($loggedInUser == false){
	print json_encode(Array("ERROR" => "Not logged in"));
	die();
}

$clean_ip = mysqli_real_escape_string($dbConn, $ip);
$clean_userAgent = mysqli_real_escape_string($dbConn, $userAgent);
$clean_user_id = mysqli_real_escape_string($dbConn, $loggedInUser->Id);

if(!isset($_GET["themeID"])){
	print json_encode(Array("ERROR" => "Theme ID not set"));
	die();
}

$themeID = intval(trim($_GET["themeID"]));

//Check if the theme exists
$sql = "SELECT theme_id FROM theme WHERE theme_deleted != 1 AND theme_id = $themeID";
$data = mysqli_query($dbConn, $sql);
$sql = "";

if(mysqli_num_rows($data) == 0){
	print json_encode(Array("ERROR" => "Theme does not exist."));
	die();
}

$ideas = "";
if(isset($_GET["ideas"])){
	$ideas = $_GET["ideas"];
}

if(strlen($ideas) > 240){
	print json_encode(Array("ERROR" => "Ideas text too long"));
	die();
}

$clean_ideas = mysqli_real_escape_string($dbConn, $ideas);

//Check if there is already an entry with ideas
$sql = "SELECT idea_id FROM theme_ideas WHERE idea_theme_id = $themeID AND idea_user_id = $clean_user_id";
$data = mysqli_query($dbConn, $sql);
$sql = "";

if($themeIdeas = mysqli_fetch_array($data)){
	//Update themes ideas to user's new ideas
	$ideasId = $themeIdeas["idea_id"];
	$sql = "UPDATE theme_ideas SET idea_ideas = '$clean_ideas' WHERE idea_id = $ideasId";
	$data = mysqli_query($dbConn, $sql);
	$sql = "";
	print json_encode(Array("SUCCESS" => "Theme ideas updated."));
}else{
	//Insert new theme ideas
	$sql = "
	INSERT INTO theme_ideas
		(idea_datetime, idea_ip, idea_user_agent, idea_theme_id, idea_user_id, idea_ideas)
		VALUES
		(Now(), '$clean_ip', '$clean_userAgent', $themeID, $clean_user_id, '$clean_ideas');";
	$data = mysqli_query($dbConn, $sql);
	$sql = "";
	print json_encode(Array("SUCCESS" => "Vote cast."));
}
?>