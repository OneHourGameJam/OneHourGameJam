<?php
//This file is the site's entry point, called directly from the main index.php
//All other files in the /php dirrectory are included from here.

include_once("php/install_page.php");
if(!IsDatabaseConfigFilePresent()){
    die("The site database has not yet been configured. Please set it up using the <a href='install.php'>install page</a>");
}

include_once("php/helpers.php");

//Fetch plugins
include_once("plugins/plugins.php");

StartTimer("site.php");
StartTimer("site.php - Include");

session_start();

//Message Service
include_once("php/message.php");

//Plugin Base
include_once("php/plugin.php");

//Plugins
include_once("php/adminlog/Initialise.php");
include_once("php/adminvote/Initialise.php");

//Global variable definition
include_once("php/anti-csrf.php");
include_once("php/global.php");
include_once("php/dependencies.php");

//Database Interface
include_once("php/databaseinterfaces/UserDbInterface.php");
include_once("php/databaseinterfaces/SessionDbInterface.php");
include_once("php/databaseinterfaces/ThemeDbInterface.php");
include_once("php/databaseinterfaces/ThemeVoteDbInterface.php");
include_once("php/databaseinterfaces/ThemeIdeaDbInterface.php");
include_once("php/databaseinterfaces/SatisfactionDbInterface.php");
include_once("php/databaseinterfaces/PollDbInterface.php");
include_once("php/databaseinterfaces/PollOptionDbInterface.php");
include_once("php/databaseinterfaces/PollVoteDbInterface.php");
include_once("php/databaseinterfaces/PlatformDbInterface.php");
include_once("php/databaseinterfaces/PlatformGameDbInterface.php");
include_once("php/databaseinterfaces/JamDbInterface.php");
include_once("php/databaseinterfaces/GameDbInterface.php");
include_once("php/databaseinterfaces/ConfigDbInterface.php");
include_once("php/databaseinterfaces/AssetDbInterface.php");

//Models
include_once("php/models/UserModel.php");
include_once("php/models/ThemeModel.php");
include_once("php/models/SiteActionModel.php");
include_once("php/models/SatisfactionModel.php");
include_once("php/models/PollModel.php");
include_once("php/models/MessageModel.php");
include_once("php/models/JamModel.php");
include_once("php/models/GameModel.php");
include_once("php/models/PlatformModel.php");
include_once("php/models/PlatformGameModel.php");
include_once("php/models/CookieModel.php");
include_once("php/models/ConfigModel.php");
include_once("php/models/AssetModel.php");
include_once("php/models/ThemeIdeaModel.php");
include_once("php/models/FormModel.php");

//ViewModels
include_once("php/viewmodels/UserViewModel.php");
include_once("php/viewmodels/ThemeViewModel.php");
include_once("php/viewmodels/AssetViewModel.php");
include_once("php/viewmodels/ConfigurationViewModel.php");
include_once("php/viewmodels/CookieViewModel.php");
include_once("php/viewmodels/GameViewModel.php");
include_once("php/viewmodels/JamViewModel.php");
include_once("php/viewmodels/PollViewModel.php");
include_once("php/viewmodels/StreamViewModel.php");
include_once("php/viewmodels/MessageViewModel.php");
include_once("php/viewmodels/PlatformViewModel.php");
include_once("php/viewmodels/FormViewModel.php");

//Presenters
include_once("php/presenters/UserPresenter.php");
include_once("php/presenters/ThemePresenter.php");
include_once("php/presenters/AssetPresenter.php");
include_once("php/presenters/ConfigurationPresenter.php");
include_once("php/presenters/CookiePresenter.php");
include_once("php/presenters/GamePresenter.php");
include_once("php/presenters/JamPresenter.php");
include_once("php/presenters/PollPresenter.php");
include_once("php/presenters/StreamPresenter.php");
include_once("php/presenters/MessagePresenter.php");
include_once("php/presenters/PlatformPresenter.php");
include_once("php/presenters/FormPresenter.php");

//Controllers
include_once("php/controllers/ThemeController.php");
include_once("php/controllers/CookieController.php");
include_once("php/controllers/JamController.php");
include_once("php/controllers/StreamController.php");

//Global functions
include_once("php/sanitize.php");
include_once("php/page.php");
include_once("php/actions.php");
include_once("php/Database.php");
include_once("php/authentication.php");
StopTimer("site.php - Include");

//Initialization. This is where configuration is loaded
StartTimer("site.php - Init");
include_once("php/init.php");
StopTimer("site.php - Init");

StopTimer("site.php");

?>