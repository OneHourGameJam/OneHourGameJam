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

$themeId = intval(trim($_GET["themeID"]));

//Check if the theme exists
$data = $themeDbInterface->SelectIfActive($themeId);

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

//Check if there is already an entry with ideas
$data = $themeIdeaDbInterface->SelectSingle($themeId, $loggedInUser->Id);

if($themeIdea = mysqli_fetch_array($data)){
	$ideasId = $themeIdea["idea_id"];
	$themeIdeaDbInterface->Update($ideasId, $ideas);
	print json_encode(Array("SUCCESS" => "Theme ideas updated."));
}else{
	$themeIdeaDbInterface->Insert($ip, $userAgent, $themeId, $loggedInUser->Id, $ideas);
	print json_encode(Array("SUCCESS" => "Vote cast."));
}
?>