<?php
//This file contains all the globally accessible variables and constants.

//Global variables
$page = "main";
if(isset($_GET["page"])){
	$page = strtolower(trim($_GET["page"]));
}

$warnings = Array();
$loggedInUser = "";
$loginChecked = false;
$config = Array();
$dictionary = Array();
$users = Array();
$jams = Array();
$authors = Array();
$entries = Array();
$themes = Array();
$loggedInUserThemeVotes = Array();
$assets = Array();
$polls = Array();
$satisfaction = Array();
$adminVotes = Array();
$loggedInUserAdminVotes = Array();
$adminLog = Array();
$themesByVoteDifference = Array();
$themesByPopularity = Array();

$ip = $_SERVER['REMOTE_ADDR'];
$userAgent = $_SERVER['HTTP_USER_AGENT'];
$nextScheduledJamTime = "";
$nextSuggestedJamDateTime = "";

require __DIR__ . '/../vendor/Mustache/Autoloader.php';
Mustache_Autoloader::register();
$mustache = new Mustache_Engine;

?>