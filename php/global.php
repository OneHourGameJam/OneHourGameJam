<?php
//This file contains all the globally accessible variables and constants.

//Global variables
$page = "main";
$partials = Array();
$warnings = Array();
$loggedInUser = "";
$loginChecked = false;
$configData;
$dictionary = Array();
$userData;
$jamData;
$authors = Array();
$gameData;
$platformData;
$platformGameData;
$ThemeData;
$assetData;
$pollData;
$satisfactionData;
$adminVoteData;
$themesByVoteDifference = Array();
$themesByPopularity = Array();
$cookieData;
$messageData;
$themeIdeaData;
$notificationData;

$userDbInterface;
$sessionDbInterface;
$themeDbInterface;
$themeVoteDbInterface;
$themeIdeaDbInterface;
$satisfactionDbInterface;
$pollDbInterface;
$pollOptionDbInterface;
$pollVoteDbInterface;
$platformDbInterface;
$platformGameDbInterface;
$jamDbInterface;
$gameDbInterface;
$configDbInterface;
$assetDbInterface;
$adminVoteDbInterface;
$notificationDbInterface;

$ip = $_SERVER['REMOTE_ADDR'];
$userAgent = $_SERVER['HTTP_USER_AGENT'];
$nextScheduledJamTime = "";
$nextSuggestedJamDateTime = "";

require __DIR__ . '/../dependencies/mustache/Autoloader.php';
Mustache_Autoloader::register();
$mustache = new Mustache_Engine();



?>