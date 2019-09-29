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

include_once("php/anti-csrf.php");
include_once("php/global.php");
include_once("php/dependencies.php");

//Models
include_once("php/models/UserModel.php");
include_once("php/models/ThemeModel.php");
include_once("php/models/SiteActionModel.php");
include_once("php/models/SatisfactionModel.php");
include_once("php/models/PollModel.php");
include_once("php/models/MessageModel.php");
include_once("php/models/JamModel.php");
include_once("php/models/GameModel.php");
include_once("php/models/CookieModel.php");
include_once("php/models/ConfigModel.php");
include_once("php/models/AssetModel.php");
include_once("php/models/AdminVoteModel.php");
include_once("php/models/AdminLogModel.php");
include_once("php/models/ThemeIdeasModel.php");

//Presenters
include_once("php/presenters/AdminLogPresenter.php");

//Global functions
include_once("php/sanitize.php");
include_once("php/page.php");
include_once("php/actions.php");
include_once("php/cookies.php");
include_once("php/messages.php");
include_once("php/config.php");
include_once("php/db.php");
include_once("php/authentication.php");
include_once("php/users.php");
include_once("php/jams.php");
include_once("php/games.php");
include_once("php/themes.php");
include_once("php/assets.php");
include_once("php/polls.php");
include_once("php/stream.php");
StopTimer("site.php - Include");

//Initialization. This is where configuration is loaded
StartTimer("site.php - Init");
include_once("php/init.php");
StopTimer("site.php - Init");

StopTimer("site.php");

?>