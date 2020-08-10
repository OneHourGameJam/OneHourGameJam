<?php

StartTimer("Dependencies");

//These correspond to the data which needs to be retrieved with LoadXYZ()
define("DEPENDENCY_NONE",           0);
define("DEPENDENCY_CONFIG",         pow(2, 0));
define("DEPENDENCY_COOKIES",        pow(2, 1));
define("DEPENDENCY_ADMIN_LOG",      pow(2, 2));
define("DEPENDENCY_USERS",          pow(2, 3));
define("DEPENDENCY_GAMES",          pow(2, 4));
define("DEPENDENCY_JAMS",           pow(2, 5));
define("DEPENDENCY_ADMIN_VOTES",    pow(2, 6));
define("DEPENDENCY_LOGGED_IN_USER_ADMIN_VOTES", pow(2, 7));
define("DEPENDENCY_SATISFACTION",   pow(2, 8));
define("DEPENDENCY_THEMES",         pow(2, 9));
define("DEPENDENCY_LOGGED_IN_USER_THEME_VOTES", pow(2, 10));
define("DEPENDENCY_THEMES_BY_VOTE_DIFFERENCE",  pow(2, 11));
define("DEPENDENCY_THEMES_BY_POPULARITY",       pow(2, 12));
define("DEPENDENCY_ASSETS",         pow(2, 13));
define("DEPENDENCY_POLLS",          pow(2, 14));
define("DEPENDENCY_LOGGED_IN_USER_POLL_VOTES",  pow(2, 15));
define("DEPENDENCY_MESSAGES",       pow(2, 16));
define("DEPENDENCY_PLATFORMS",       pow(2, 17));

//define("DEPENDENCY_", pow(2, 17));
//define("DEPENDENCY_", pow(2, 18));
//define("DEPENDENCY_", pow(2, 19));
//define("DEPENDENCY_", pow(2, 20));
//define("DEPENDENCY_", pow(2, 21));
//define("DEPENDENCY_", pow(2, 22));
//define("DEPENDENCY_", pow(2, 23));

define("DEPENDENCY_LOGGED_IN_USER", pow(2, 99));



define("RENDER_DEPTH_NONE",         0);
define("RENDER_DEPTH_USERS",        pow(2, 0));
define("RENDER_DEPTH_JAMS",         pow(2, 1));
define("RENDER_DEPTH_GAMES",        pow(2, 2));
define("RENDER_DEPTH_THEME_IDEAS",  pow(2, 3));
define("RENDER_DEPTH_JAMS_GAMES",   RENDER_DEPTH_JAMS + RENDER_DEPTH_GAMES);
define("RENDER_DEPTH_USERS_GAMES",  RENDER_DEPTH_USERS + RENDER_DEPTH_GAMES);

define("PAGE_MAIN", "main");
define("PAGE_LOGIN", "login");
define("PAGE_REGISTER", "register");
define("PAGE_FORGOT_PASSWORD", "forgotpassword");
define("PAGE_SUBMIT", "submit");
define("PAGE_NEW_JAM", "newjam");
define("PAGE_ASSETS", "assets");
define("PAGE_EDIT_ASSET", "editasset");
define("PAGE_RULES", "rules");
define("PAGE_CONFIG", "config");
define("PAGE_EDIT_CONTENT", "editcontent");
define("PAGE_EDIT_JAM", "editjam");
define("PAGE_EDIT_ENTRY", "editentry");
define("PAGE_EDIT_USERS", "editusers");
define("PAGE_EDIT_USER", "edituser");
define("PAGE_THEMES", "themes");
define("PAGE_MANAGE_THEMES", "managethemes");
define("PAGE_USER_SETTINGS", "usersettings");
define("PAGE_ENTRIES", "entries");
define("PAGE_JAM", "jam");
define("PAGE_JAMS", "jams");
define("PAGE_AUTHOR", "author");
define("PAGE_AUTHORS", "authors");
define("PAGE_PRIVACY", "privacy");
define("PAGE_USER_DATA", "userdata");
define("PAGE_ADMIN_LOG", "adminlog");
define("PAGE_POLLS", "polls");
define("PAGE_EDIT_PLATFORMS", "editplatforms");

define("RENDER_CONFIG", "RenderConfig");
define("RENDER_ADMIN_LOG", "RenderAdminLog");
define("RENDER_USERS", "RenderUsers");
define("RENDER_ALL_JAMS", "RenderAllJams");
define("RENDER_JAMS", "RenderJams");
define("RENDER_GAMES", "RenderGames");
define("RENDER_THEMES", "RenderThemes");
define("RENDER_ASSETS", "RenderAssets");
define("RENDER_POLLS", "RenderPolls");
define("RENDER_COOKIES", "RenderCookies");
define("RENDER_MESSAGES", "RenderMessages");
define("RENDER_STREAM", "RenderStream");
define("RENDER_PLATFORMS", "RenderPlatforms");
define("RENDER_LOGGED_IN_USER", "RenderLoggedInUser");

define("AUTHORIZATION_LEVEL_NONE", "NONE");
define("AUTHORIZATION_LEVEL_USER", "USER");
define("AUTHORIZATION_LEVEL_ADMIN", "ADMIN");

$commonDependencies = Array(
    "header" => Array(
        RENDER_CONFIG => RENDER_DEPTH_NONE, 
        RENDER_USERS => RENDER_DEPTH_NONE, 
        RENDER_GAMES => RENDER_DEPTH_NONE, 
        RENDER_JAMS => RENDER_DEPTH_NONE),
    "message" => Array(RENDER_MESSAGES => RENDER_DEPTH_NONE),
    "menu" => Array(
        RENDER_CONFIG => RENDER_DEPTH_NONE, 
        RENDER_LOGGED_IN_USER => RENDER_DEPTH_NONE, 
        RENDER_COOKIES => RENDER_DEPTH_NONE, 
        RENDER_THEMES => RENDER_DEPTH_NONE, 
        RENDER_JAMS => RENDER_DEPTH_NONE, 
        RENDER_USERS => RENDER_DEPTH_USERS),
    "footer" => Array(),
    "poll" => Array(
        RENDER_LOGGED_IN_USER => RENDER_DEPTH_NONE, 
        RENDER_POLLS => RENDER_DEPTH_NONE),
    "notification" => Array(
        RENDER_CONFIG => RENDER_DEPTH_NONE),
);

$pageSettings = Array(
    PAGE_MAIN => Array(
        "page_title" => "Main Page",
        "authorization_level" => AUTHORIZATION_LEVEL_NONE,
        "template_file" => "main.html",
        "dependencies" => Array(RENDER_JAMS => RENDER_DEPTH_JAMS_GAMES, RENDER_LOGGED_IN_USER => RENDER_DEPTH_NONE, RENDER_STREAM => RENDER_DEPTH_NONE),
    ), 
    PAGE_LOGIN => Array(
        "page_title" => "Login",
        "authorization_level" => AUTHORIZATION_LEVEL_NONE,
        "template_file" => "login.html",
        "dependencies" => Array(RENDER_CONFIG => RENDER_DEPTH_NONE),
    ),
    PAGE_REGISTER => Array(
        "page_title" => "Register",
        "authorization_level" => AUTHORIZATION_LEVEL_NONE,
        "template_file" => "register.html",
        "dependencies" => Array(RENDER_CONFIG => RENDER_DEPTH_NONE),
    ),
    PAGE_FORGOT_PASSWORD => Array(
        "page_title" => "Forgot Your Password?",
        "authorization_level" => AUTHORIZATION_LEVEL_NONE,
        "template_file" => "forgotpassword.html",
        "dependencies" => Array(RENDER_CONFIG => RENDER_DEPTH_NONE),
    ),
    PAGE_SUBMIT => Array(
        "page_title" => "Submit Game",
        "authorization_level" => AUTHORIZATION_LEVEL_USER,
        "template_file" => "submit.html",
        "dependencies" => Array(RENDER_CONFIG => RENDER_DEPTH_NONE),
    ),  
    PAGE_NEW_JAM => Array(
        "page_title" => "Schedule New Jam",
        "authorization_level" => AUTHORIZATION_LEVEL_ADMIN,
        "template_file" => "newjam.html",
        "dependencies" => Array(RENDER_THEMES => RENDER_DEPTH_NONE, RENDER_CONFIG => RENDER_DEPTH_NONE),
    ),  
    PAGE_ASSETS => Array(
        "page_title" => "Assets",
        "authorization_level" => AUTHORIZATION_LEVEL_NONE,
        "template_file" => "assets.html",
        "dependencies" => Array(RENDER_LOGGED_IN_USER => RENDER_DEPTH_NONE, RENDER_ASSETS => RENDER_DEPTH_NONE),
    ),  
    PAGE_EDIT_ASSET => Array(
        "page_title" => "Edit Asset",
        "authorization_level" => AUTHORIZATION_LEVEL_ADMIN,
        "template_file" => "editasset.html",
        "dependencies" => Array(RENDER_CONFIG => RENDER_DEPTH_NONE),
    ),
    PAGE_RULES => Array(
        "page_title" => "Rules",
        "authorization_level" => AUTHORIZATION_LEVEL_NONE,
        "template_file" => "rules.html",
        "dependencies" => Array(RENDER_CONFIG => RENDER_DEPTH_NONE),
    ),  
    PAGE_CONFIG => Array(
        "page_title" => "Configuration",
        "authorization_level" => AUTHORIZATION_LEVEL_ADMIN,
        "template_file" => "config.html",
        "dependencies" => Array(RENDER_CONFIG => RENDER_DEPTH_NONE),
    ),
    PAGE_EDIT_CONTENT => Array(
        "page_title" => "Manage Content",
        "authorization_level" => AUTHORIZATION_LEVEL_ADMIN,
        "template_file" => "editcontent.html",
        "dependencies" => Array(RENDER_ALL_JAMS => RENDER_DEPTH_JAMS_GAMES),
    ),  
    PAGE_EDIT_JAM => Array(
        "page_title" => "Edit Jam",
        "authorization_level" => AUTHORIZATION_LEVEL_ADMIN,
        "template_file" => "editjam.html",
        "dependencies" => Array(),
    ),  
    PAGE_EDIT_ENTRY => Array(
        "page_title" => "Edit Entry",
        "authorization_level" => AUTHORIZATION_LEVEL_ADMIN,
        "template_file" => "editentry.html",
        "dependencies" => Array(  ),
    ),  
    PAGE_EDIT_USERS => Array(
        "page_title" => "Manage Users",
        "authorization_level" => AUTHORIZATION_LEVEL_ADMIN,
        "template_file" => "editusers.html",
        "dependencies" => Array(RENDER_CONFIG => RENDER_DEPTH_NONE, RENDER_USERS => RENDER_DEPTH_USERS),
    ),  
    PAGE_EDIT_USER => Array(
        "page_title" => "Edit User",
        "authorization_level" => AUTHORIZATION_LEVEL_ADMIN,
        "template_file" => "edituser.html",
        "dependencies" => Array(RENDER_CONFIG => RENDER_DEPTH_NONE),
    ),
    PAGE_THEMES => Array(
        "page_title" => "Theme Voting",
        "authorization_level" => AUTHORIZATION_LEVEL_USER,
        "template_file" => "themes.html",
        "dependencies" => Array(RENDER_LOGGED_IN_USER => RENDER_DEPTH_NONE, RENDER_COOKIES => RENDER_DEPTH_NONE, RENDER_THEMES => RENDER_DEPTH_THEME_IDEAS),
    ),
    PAGE_MANAGE_THEMES => Array(
        "page_title" => "Manage Themes",
        "authorization_level" => AUTHORIZATION_LEVEL_ADMIN,
        "template_file" => "managethemes.html",
        "dependencies" => Array(RENDER_LOGGED_IN_USER => RENDER_DEPTH_NONE, RENDER_COOKIES => RENDER_DEPTH_NONE, RENDER_THEMES => RENDER_DEPTH_NONE),
    ), 
    PAGE_USER_SETTINGS => Array(
        "page_title" => "User Settings",
        "authorization_level" => AUTHORIZATION_LEVEL_USER,
        "template_file" => "usersettings.html",
        "dependencies" => Array(RENDER_CONFIG => RENDER_DEPTH_NONE, RENDER_LOGGED_IN_USER => RENDER_DEPTH_NONE),
    ), 
    PAGE_ENTRIES => Array(
        "page_title" => "Entries",
        "authorization_level" => AUTHORIZATION_LEVEL_NONE,
        "template_file" => "entries.html",
        "dependencies" => Array(RENDER_GAMES => RENDER_DEPTH_GAMES),
    ),  
    PAGE_JAM => Array(
        "page_title" => "Jam",
        "authorization_level" => AUTHORIZATION_LEVEL_NONE,
        "template_file" => "jam.html",
        "dependencies" => Array(RENDER_CONFIG => RENDER_DEPTH_NONE),
    ),  
    PAGE_JAMS => Array(
        "page_title" => "Jams",
        "authorization_level" => AUTHORIZATION_LEVEL_NONE,
        "template_file" => "jams.html",
        "dependencies" => Array(RENDER_ALL_JAMS => RENDER_DEPTH_JAMS),
    ),  
    PAGE_AUTHOR => Array(
        "page_title" => "Author",
        "authorization_level" => AUTHORIZATION_LEVEL_NONE,
        "template_file" => "author.html",
        "dependencies" => Array(  ),
    ),  
    PAGE_AUTHORS => Array(
        "page_title" => "Authors",
        "authorization_level" => AUTHORIZATION_LEVEL_NONE,
        "template_file" => "authors.html",
        "dependencies" => Array(RENDER_CONFIG => RENDER_DEPTH_USERS),
    ),  
    PAGE_PRIVACY => Array(
        "page_title" => "Privacy",
        "authorization_level" => AUTHORIZATION_LEVEL_NONE,
        "template_file" => "privacy.html",
        "dependencies" => Array(  ),
    ),  
    PAGE_USER_DATA => Array(
        "page_title" => "User Data",
        "authorization_level" => AUTHORIZATION_LEVEL_USER,
        "template_file" => "userdata.html",
        "dependencies" => Array(  ),
    ),
    PAGE_ADMIN_LOG => Array(
        "page_title" => "Admin Log",
        "authorization_level" => AUTHORIZATION_LEVEL_ADMIN,
        "template_file" => "adminlog.html",
        "dependencies" => Array(RENDER_ADMIN_LOG => RENDER_DEPTH_NONE),
    ),
    PAGE_POLLS => Array(
        "page_title" => "Poll Results",
        "authorization_level" => AUTHORIZATION_LEVEL_ADMIN,
        "template_file" => "polls.html",
        "dependencies" => Array(RENDER_POLLS => RENDER_DEPTH_NONE),
    ), 
    PAGE_EDIT_PLATFORMS => Array(
        "page_title" => "Manage Platforms",
        "authorization_level" => AUTHORIZATION_LEVEL_ADMIN,
        "template_file" => "editplatforms.html",
        "dependencies" => Array(RENDER_PLATFORMS => RENDER_DEPTH_NONE),
    ), 
);

function LoadDependencies($page, &$pageSettings, &$commonDependencies){
    $dependencies = Array();

    foreach($commonDependencies as $i => $dependency){
        foreach($dependency as $dependencyKey => $dependencyRenderDepth){
            $depIndex = false;
            foreach($dependencies as $j => $depEntry){
                if($depEntry["Key"] == $dependencyKey){
                    $depIndex = $j;
                    break;
                }
            }

            if($depIndex !== false){
                $dependencies[$depIndex]["RenderDepth"] = intval($dependencyKey) | intval($dependencyRenderDepth);
            }else{
                $dependencies[] = Array("Key" => $dependencyKey, "RenderDepth" => $dependencyRenderDepth);
            }
        }
    }

    if(isset($pageSettings[$page]) && isset($pageSettings[$page]["dependencies"])){
        foreach($pageSettings[$page]["dependencies"] as $dependencyKey => $dependencyRenderDepth){
            $depIndex = false;
            foreach($dependencies as $j => $depEntry){
                if($depEntry["Key"] == $dependencyKey){
                    $depIndex = $j;
                    break;
                }
            }

            if($depIndex !== false){
                $dependencies[$depIndex]["RenderDepth"] = intval($dependencyKey) | intval($dependencyRenderDepth);
            }else{
                $dependencies[] = Array("Key" => $dependencyKey, "RenderDepth" => $dependencyRenderDepth);
            }
        }
    }else{
        trigger_error("Unknown page: $page", E_USER_WARNING);
    }

    return $dependencies;
}

StopTimer("Dependencies");

function FindDependency($dependencyKey, &$dependencies){
	AddActionLog("FindDependency");
	StartTimer("FindDependency");
    foreach($dependencies as $j => $depEntry){
        if($depEntry["Key"] == $dependencyKey){
            StopTimer("FindDependency");
            return $depEntry;
        }
    }
	StopTimer("FindDependency");
    return false;
}


?>