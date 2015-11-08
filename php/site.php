<?php
//This file is the site's entry point, called directly from the main index.php
//All other files in the /php dirrectory are included from here.

//Fetch plugins
include_once("plugins/plugins.php");

//Global variable definition
session_start();
include_once("global.php");

//Global functions
include_once("helpers.php");
include_once("config.php");
include_once("authentication.php");
include_once("entries.php");
include_once("sanitize.php");

//Initialization. This is where configuration is loaded
include_once("init.php");



?>