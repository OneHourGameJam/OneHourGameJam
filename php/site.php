<?php
//This file is the site's entry point, called directly from the main index.php
//All other files in the /php dirrectory are included from here.

//Fetch plugins
include_once("plugins/plugins.php");

//Global variable definition
session_start();
include_once("php/global.php");

//Global functions
include_once("php/helpers.php");
include_once("php/sanitize.php");
include_once("php/config.php");
include_once("php/db.php");
include_once("php/authentication.php");
include_once("php/entries.php");
include_once("php/themes.php");
include_once("php/assets.php");
include_once("php/polls.php");
include_once("php/stream.php");

//Initialization. This is where configuration is loaded
include_once("php/init.php");


?>