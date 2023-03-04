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

include_once( __DIR__ . "/../dependencies/mustache/Autoloader.php");
include_once( __DIR__ . "/../dependencies/mustache/Cache.php");
include_once( __DIR__ . "/../dependencies/mustache/Compiler.php");
include_once( __DIR__ . "/../dependencies/mustache/Context.php");
include_once( __DIR__ . "/../dependencies/mustache/Engine.php");
include_once( __DIR__ . "/../dependencies/mustache/Exception.php");
include_once( __DIR__ . "/../dependencies/mustache/HelperCollection.php");
include_once( __DIR__ . "/../dependencies/mustache/LambdaHelper.php");
include_once( __DIR__ . "/../dependencies/mustache/Loader.php");
include_once( __DIR__ . "/../dependencies/mustache/Logger.php");
include_once( __DIR__ . "/../dependencies/mustache/Parser.php");
include_once( __DIR__ . "/../dependencies/mustache/Source.php");
include_once( __DIR__ . "/../dependencies/mustache/Template.php");
include_once( __DIR__ . "/../dependencies/mustache/Tokenizer.php");
include_once( __DIR__ . "/../dependencies/mustache/Source/FilesystemSource.php");
include_once( __DIR__ . "/../dependencies/mustache/Logger/AbstractLogger.php");
include_once( __DIR__ . "/../dependencies/mustache/Logger/StreamLogger.php");
include_once( __DIR__ . "/../dependencies/mustache/Loader/MutableLoader.php");
include_once( __DIR__ . "/../dependencies/mustache/Loader/ArrayLoader.php");
include_once( __DIR__ . "/../dependencies/mustache/Loader/CascadingLoader.php");
include_once( __DIR__ . "/../dependencies/mustache/Loader/FilesystemLoader.php");
include_once( __DIR__ . "/../dependencies/mustache/Loader/InlineLoader.php");
include_once( __DIR__ . "/../dependencies/mustache/Loader/ProductionFilesystemLoader.php");
include_once( __DIR__ . "/../dependencies/mustache/Loader/StringLoader.php");
include_once( __DIR__ . "/../dependencies/mustache/Exception/InvalidArgumentException.php");
include_once( __DIR__ . "/../dependencies/mustache/Exception/LogicException.php");
include_once( __DIR__ . "/../dependencies/mustache/Exception/RuntimeException.php");
include_once( __DIR__ . "/../dependencies/mustache/Exception/SyntaxException.php");
include_once( __DIR__ . "/../dependencies/mustache/Exception/UnknownFilterException.php");
include_once( __DIR__ . "/../dependencies/mustache/Exception/UnknownHelperException.php");
include_once( __DIR__ . "/../dependencies/mustache/Exception/UnknownTemplateException.php");
include_once( __DIR__ . "/../dependencies/mustache/Cache/AbstractCache.php");
include_once( __DIR__ . "/../dependencies/mustache/Cache/FilesystemCache.php");
include_once( __DIR__ . "/../dependencies/mustache/Cache/NoopCache.php");
$mustache = new MustacheEngine();


?>