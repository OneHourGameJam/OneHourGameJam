<?php

//These correspond to the data which needs to be retrieved with LoadXYZ()
define("DEPENDENCY_CONFIG", pow(2, 0));
define("DEPENDENCY_ADMIN_LOG", pow(2, 1));
define("DEPENDENCY_USERS", pow(2, 2));
define("DEPENDENCY_GAMES", pow(2, 3));
define("DEPENDENCY_JAMS", pow(2, 4));
define("DEPENDENCY_ADMIN_VOTES", pow(2, 5));
define("DEPENDENCY_LOGGED_IN_USER_ADMIN_VOTES", pow(2, 6));
define("DEPENDENCY_SATISFACTION", pow(2, 7));
define("DEPENDENCY_THEMES", pow(2, 8));
define("DEPENDENCY_LOGGED_IN_USER_THEME_VOTES", pow(2, 9));
define("DEPENDENCY_THEMES_BY_VOTE_DIFFERENCE", pow(2, 10));
define("DEPENDENCY_THEMES_BY_POPULARITY", pow(2, 11));
define("DEPENDENCY_ASSETS", pow(2, 12));
define("DEPENDENCY_POLLS", pow(2, 13));
define("DEPENDENCY_LOGGED_IN_USER_POLL_VOTES", pow(2, 14));
//define("DEPENDENCY_", pow(2, 15));
//define("DEPENDENCY_", pow(2, 16));
//define("DEPENDENCY_", pow(2, 17));
//define("DEPENDENCY_", pow(2, 18));
//define("DEPENDENCY_", pow(2, 19));
//define("DEPENDENCY_", pow(2, 20));
//define("DEPENDENCY_", pow(2, 21));
//define("DEPENDENCY_", pow(2, 22));
//define("DEPENDENCY_", pow(2, 23));

define("DEPENDENCY_LOGGED_IN_USER", pow(2, 99));

//These correspond to which data is necessary to perform the processing or rendering of each module
$dependencies = Array(
    "IsLoggedIn" =>   Array(
        "BaseDependencies" => DEPENDENCY_CONFIG | DEPENDENCY_USERS,
        "Dependencies" => Array()),
    "RenderConfig" =>   Array(
        "BaseDependencies" => DEPENDENCY_CONFIG, 
        "Dependencies" => Array()),
    "RenderAdminLog" => Array(
        "BaseDependencies" => DEPENDENCY_ADMIN_LOG, 
        "Dependencies" => Array()),
    "RenderUsers" =>    Array(
        "BaseDependencies" => DEPENDENCY_CONFIG | DEPENDENCY_USERS | DEPENDENCY_GAMES | DEPENDENCY_JAMS | DEPENDENCY_ADMIN_VOTES | DEPENDENCY_LOGGED_IN_USER_ADMIN_VOTES, 
        "Dependencies" => Array()),
    "RenderJams" =>     Array(
        "BaseDependencies" => DEPENDENCY_CONFIG | DEPENDENCY_USERS | DEPENDENCY_GAMES | DEPENDENCY_JAMS | DEPENDENCY_SATISFACTION | DEPENDENCY_LOGGED_IN_USER, 
        "Dependencies" => Array()),
    "RenderGames" =>    Array(
        "BaseDependencies" => DEPENDENCY_USERS | DEPENDENCY_GAMES | DEPENDENCY_JAMS, 
        "Dependencies" => Array()),
    "RenderThemes" =>   Array(
        "BaseDependencies" => DEPENDENCY_CONFIG | DEPENDENCY_THEMES | DEPENDENCY_LOGGED_IN_USER_THEME_VOTES | DEPENDENCY_THEMES_BY_VOTE_DIFFERENCE | DEPENDENCY_THEMES_BY_POPULARITY | DEPENDENCY_LOGGED_IN_USER, 
        "Dependencies" => Array()),
    "RenderAssets" =>   Array(
        "BaseDependencies" => DEPENDENCY_ASSETS, 
        "Dependencies" => Array()),
    "RenderPolls" =>    Array(
        "BaseDependencies" => DEPENDENCY_POLLS | DEPENDENCY_LOGGED_IN_USER_POLL_VOTES, 
        "Dependencies" => Array()),
    "RenderLoggedInUser" =>    Array(
        "BaseDependencies" => DEPENDENCY_CONFIG | DEPENDENCY_USERS | DEPENDENCY_GAMES | DEPENDENCY_JAMS | DEPENDENCY_ADMIN_VOTES | DEPENDENCY_LOGGED_IN_USER_ADMIN_VOTES | DEPENDENCY_LOGGED_IN_USER, 
        "Dependencies" => Array()),
    "CheckNextJamSchedule" =>  Array(
        "BaseDependencies" => DEPENDENCY_CONFIG | DEPENDENCY_JAMS | DEPENDENCY_THEMES, 
        "Dependencies" => Array(
            "GetNextJamDateAndTime", 
            "GetSuggestedNextJamDateTime")),
    "GetNextJamDateAndTime" => Array(
        "BaseDependencies" => DEPENDENCY_JAMS, 
        "Dependencies" => Array()),
    "GetSuggestedNextJamDateTime" => Array(
        "BaseDependencies" => DEPENDENCY_CONFIG, 
        "Dependencies" => Array()),
);

$pageDependencies = Array(
    "main", 
    "login", 
    "logout", 
    "submit", 
    "newjam", 
    "assets", 
    "editasset", 
    "rules", 
    "config", 
    "editcontent", 
    "editjam", 
    "editentry", 
    "editusers", 
    "edituser", 
    "themes", 
    "usersettings", 
    "entries", 
    "jam", 
    "jams", 
    "author", 
    "authors", 
    "privacy", 
    "userdata", 
    "adminlog"
)


?>