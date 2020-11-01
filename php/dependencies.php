<?php

StartTimer("Dependencies");

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
define("PAGE_EDIT_ASSETS", "editassets");
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
define("PAGE_POLLS", "polls");
define("PAGE_EDIT_PLATFORMS", "editplatforms");

define("RENDER_CONFIG", "RenderConfig");
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
define("RENDER_FORMS", "RenderForms");

define("AUTHORIZATION_LEVEL_NONE", "NONE");
define("AUTHORIZATION_LEVEL_USER", "USER");
define("AUTHORIZATION_LEVEL_ADMIN", "ADMIN");

$commonDependencies = Array(
    "header" => Array(
        RENDER_CONFIG => RENDER_DEPTH_NONE, 
        RENDER_USERS => RENDER_DEPTH_NONE, 
        RENDER_GAMES => RENDER_DEPTH_NONE, 
        RENDER_JAMS => RENDER_DEPTH_NONE, 
        RENDER_FORMS => RENDER_DEPTH_NONE),
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
        "template_file" => $templateBasePath."main.html",
        "dependencies" => Array(RENDER_JAMS => RENDER_DEPTH_JAMS_GAMES, RENDER_LOGGED_IN_USER => RENDER_DEPTH_NONE, RENDER_STREAM => RENDER_DEPTH_NONE),
    ), 
    PAGE_LOGIN => Array(
        "page_title" => "Login",
        "authorization_level" => AUTHORIZATION_LEVEL_NONE,
        "template_file" => $templateBasePath."login.html",
        "dependencies" => Array(RENDER_CONFIG => RENDER_DEPTH_NONE),
    ),
    PAGE_REGISTER => Array(
        "page_title" => "Register",
        "authorization_level" => AUTHORIZATION_LEVEL_NONE,
        "template_file" => $templateBasePath."register.html",
        "dependencies" => Array(RENDER_CONFIG => RENDER_DEPTH_NONE),
    ),
    PAGE_FORGOT_PASSWORD => Array(
        "page_title" => "Forgot Your Password?",
        "authorization_level" => AUTHORIZATION_LEVEL_NONE,
        "template_file" => $templateBasePath."forgotpassword.html",
        "dependencies" => Array(RENDER_CONFIG => RENDER_DEPTH_NONE),
    ),
    PAGE_SUBMIT => Array(
        "page_title" => "Submit Game",
        "authorization_level" => AUTHORIZATION_LEVEL_USER,
        "template_file" => $templateBasePath."submit.html",
        "dependencies" => Array(RENDER_CONFIG => RENDER_DEPTH_NONE),
    ),  
    PAGE_NEW_JAM => Array(
        "page_title" => "Schedule New Jam",
        "authorization_level" => AUTHORIZATION_LEVEL_ADMIN,
        "template_file" => $templateBasePath."newjam.html",
        "dependencies" => Array(RENDER_THEMES => RENDER_DEPTH_NONE, RENDER_CONFIG => RENDER_DEPTH_NONE),
    ),  
    PAGE_ASSETS => Array(
        "page_title" => "Assets",
        "authorization_level" => AUTHORIZATION_LEVEL_NONE,
        "template_file" => $templateBasePath."assets.html",
        "dependencies" => Array(RENDER_LOGGED_IN_USER => RENDER_DEPTH_NONE, RENDER_ASSETS => RENDER_DEPTH_NONE),
    ),  
    PAGE_EDIT_ASSETS => Array(
        "page_title" => "Manage Assets",
        "authorization_level" => AUTHORIZATION_LEVEL_ADMIN,
        "template_file" => $templateBasePath."editassets.html",
        "dependencies" => Array(RENDER_LOGGED_IN_USER => RENDER_DEPTH_NONE, RENDER_ASSETS => RENDER_DEPTH_NONE),
    ),  
    PAGE_EDIT_ASSET => Array(
        "page_title" => "Edit Asset",
        "authorization_level" => AUTHORIZATION_LEVEL_ADMIN,
        "template_file" => $templateBasePath."editasset.html",
        "dependencies" => Array(RENDER_CONFIG => RENDER_DEPTH_NONE),
    ),
    PAGE_RULES => Array(
        "page_title" => "Rules",
        "authorization_level" => AUTHORIZATION_LEVEL_NONE,
        "template_file" => $templateBasePath."rules.html",
        "dependencies" => Array(RENDER_CONFIG => RENDER_DEPTH_NONE),
    ),  
    PAGE_CONFIG => Array(
        "page_title" => "Configuration",
        "authorization_level" => AUTHORIZATION_LEVEL_ADMIN,
        "template_file" => $templateBasePath."config.html",
        "dependencies" => Array(RENDER_CONFIG => RENDER_DEPTH_NONE),
    ),
    PAGE_EDIT_CONTENT => Array(
        "page_title" => "Manage Content",
        "authorization_level" => AUTHORIZATION_LEVEL_ADMIN,
        "template_file" => $templateBasePath."editcontent.html",
        "dependencies" => Array(RENDER_ALL_JAMS => RENDER_DEPTH_JAMS_GAMES),
    ),  
    PAGE_EDIT_JAM => Array(
        "page_title" => "Edit Jam",
        "authorization_level" => AUTHORIZATION_LEVEL_ADMIN,
        "template_file" => $templateBasePath."editjam.html",
        "dependencies" => Array(),
    ),  
    PAGE_EDIT_ENTRY => Array(
        "page_title" => "Edit Entry",
        "authorization_level" => AUTHORIZATION_LEVEL_ADMIN,
        "template_file" => $templateBasePath."editentry.html",
        "dependencies" => Array(  ),
    ),  
    PAGE_EDIT_USERS => Array(
        "page_title" => "Manage Users",
        "authorization_level" => AUTHORIZATION_LEVEL_ADMIN,
        "template_file" => $templateBasePath."editusers.html",
        "dependencies" => Array(RENDER_CONFIG => RENDER_DEPTH_NONE, RENDER_USERS => RENDER_DEPTH_USERS),
    ),  
    PAGE_EDIT_USER => Array(
        "page_title" => "Edit User",
        "authorization_level" => AUTHORIZATION_LEVEL_ADMIN,
        "template_file" => $templateBasePath."edituser.html",
        "dependencies" => Array(RENDER_CONFIG => RENDER_DEPTH_NONE),
    ),
    PAGE_THEMES => Array(
        "page_title" => "Theme Voting",
        "authorization_level" => AUTHORIZATION_LEVEL_USER,
        "template_file" => $templateBasePath."themes.html",
        "dependencies" => Array(RENDER_LOGGED_IN_USER => RENDER_DEPTH_NONE, RENDER_COOKIES => RENDER_DEPTH_NONE, RENDER_THEMES => RENDER_DEPTH_THEME_IDEAS),
    ),
    PAGE_MANAGE_THEMES => Array(
        "page_title" => "Manage Themes",
        "authorization_level" => AUTHORIZATION_LEVEL_ADMIN,
        "template_file" => $templateBasePath."managethemes.html",
        "dependencies" => Array(RENDER_LOGGED_IN_USER => RENDER_DEPTH_NONE, RENDER_COOKIES => RENDER_DEPTH_NONE, RENDER_THEMES => RENDER_DEPTH_NONE),
    ), 
    PAGE_USER_SETTINGS => Array(
        "page_title" => "User Settings",
        "authorization_level" => AUTHORIZATION_LEVEL_USER,
        "template_file" => $templateBasePath."usersettings.html",
        "dependencies" => Array(RENDER_CONFIG => RENDER_DEPTH_NONE, RENDER_LOGGED_IN_USER => RENDER_DEPTH_NONE),
    ), 
    PAGE_ENTRIES => Array(
        "page_title" => "Entries",
        "authorization_level" => AUTHORIZATION_LEVEL_NONE,
        "template_file" => $templateBasePath."entries.html",
        "dependencies" => Array(RENDER_GAMES => RENDER_DEPTH_GAMES),
    ),  
    PAGE_JAM => Array(
        "page_title" => "Jam",
        "authorization_level" => AUTHORIZATION_LEVEL_NONE,
        "template_file" => $templateBasePath."jam.html",
        "dependencies" => Array(RENDER_CONFIG => RENDER_DEPTH_NONE),
    ),  
    PAGE_JAMS => Array(
        "page_title" => "Jams",
        "authorization_level" => AUTHORIZATION_LEVEL_NONE,
        "template_file" => $templateBasePath."jams.html",
        "dependencies" => Array(RENDER_ALL_JAMS => RENDER_DEPTH_JAMS),
    ),  
    PAGE_AUTHOR => Array(
        "page_title" => "Author",
        "authorization_level" => AUTHORIZATION_LEVEL_NONE,
        "template_file" => $templateBasePath."author.html",
        "dependencies" => Array(  ),
    ),  
    PAGE_AUTHORS => Array(
        "page_title" => "Authors",
        "authorization_level" => AUTHORIZATION_LEVEL_NONE,
        "template_file" => $templateBasePath."authors.html",
        "dependencies" => Array(RENDER_CONFIG => RENDER_DEPTH_USERS),
    ),  
    PAGE_PRIVACY => Array(
        "page_title" => "Privacy",
        "authorization_level" => AUTHORIZATION_LEVEL_NONE,
        "template_file" => $templateBasePath."privacy.html",
        "dependencies" => Array(  ),
    ),  
    PAGE_USER_DATA => Array(
        "page_title" => "User Data",
        "authorization_level" => AUTHORIZATION_LEVEL_USER,
        "template_file" => $templateBasePath."userdata.html",
        "dependencies" => Array(  ),
    ),
    PAGE_POLLS => Array(
        "page_title" => "Poll Results",
        "authorization_level" => AUTHORIZATION_LEVEL_ADMIN,
        "template_file" => $templateBasePath."polls.html",
        "dependencies" => Array(RENDER_POLLS => RENDER_DEPTH_NONE),
    ), 
    PAGE_EDIT_PLATFORMS => Array(
        "page_title" => "Manage Platforms",
        "authorization_level" => AUTHORIZATION_LEVEL_ADMIN,
        "template_file" => $templateBasePath."editplatforms.html",
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