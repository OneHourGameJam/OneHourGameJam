<?php
//This file is the site's entry point, called directly from the main index.php
//All other files in the /php dirrectory are included from here.

include_once("php/helpers.php");

//Fetch plugins
include_once("plugins/plugins.php");

StartTimer("site.php");
StartTimer("site.php - Include");
//Global variable definition
session_start();
include_once("php/global.php");
include_once("php/dependencies.php");

//Models
include_once("php/models/UserModel.php");

//Global functions
include_once("php/sanitize.php");
include_once("php/page.php");
include_once("php/actions.php");
include_once("php/cookies.php");
include_once("php/messages.php");
include_once("php/config.php");
include_once("php/db.php");
include_once("php/authentication.php");
include_once("php/adminlog.php");
include_once("php/users.php");
include_once("php/jams.php");
include_once("php/games.php");
include_once("php/themes.php");
include_once("php/assets.php");
include_once("php/polls.php");
include_once("php/stream.php");
include_once("php/adminvotes.php");
include_once("php/satisfaction.php");;
StopTimer("site.php - Include");

//Initialization. This is where configuration is loaded
StartTimer("site.php - Init");
include_once("php/init.php");
StopTimer("site.php - Init");

StopTimer("site.php");

?>