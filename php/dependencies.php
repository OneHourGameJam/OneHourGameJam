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

//These correspond to which data is necessary to perform the processing or rendering of each module
$rendererDependencies = Array(
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
    "RenderAllJams" =>     Array(
        "BaseDependencies" => 0, 
        "Dependencies" => Array("RenderJams")),
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
    "RenderMessages" => Array(
        "BaseDependencies" => DEPENDENCY_MESSAGES, 
        "Dependencies" => Array()),
    "RenderCookies" => Array(
        "BaseDependencies" => DEPENDENCY_COOKIES, 
        "Dependencies" => Array()),
    "RenderStream" => Array(
        "BaseDependencies" => DEPENDENCY_NONE, 
        "Dependencies" => Array()),
);

$commonDependencies = Array(
    "header" => Array("RenderConfig" => RENDER_DEPTH_NONE, "RenderUsers" => RENDER_DEPTH_NONE, "RenderGames" => RENDER_DEPTH_NONE, "RenderJams" => RENDER_DEPTH_NONE),
    "message" => Array("RenderMessages" => RENDER_DEPTH_NONE),
    "menu" => Array("RenderConfig" => RENDER_DEPTH_NONE, "RenderLoggedInUser" => RENDER_DEPTH_NONE, "RenderCookies" => RENDER_DEPTH_NONE, "RenderThemes" => RENDER_DEPTH_NONE, "RenderJams" => RENDER_DEPTH_NONE, "RenderUsers" => RENDER_DEPTH_USERS),
    "footer" => Array(),
    "poll" => Array("RenderLoggedInUser" => RENDER_DEPTH_NONE, "RenderPolls" => RENDER_DEPTH_NONE),
    "notification" => Array("RenderConfig" => RENDER_DEPTH_NONE),
);

$pageSettings = Array(
    "main" => Array(
        "page_title" => "Main Page",
        "authorization_level" => "NONE",
        "template_file" => "main.html",
        "dependencies" => Array("RenderJams" => RENDER_DEPTH_JAMS_GAMES, "RenderLoggedInUser" => RENDER_DEPTH_NONE, "RenderStream" => RENDER_DEPTH_NONE),
    ), 
    "login" => Array(
        "page_title" => "Login",
        "authorization_level" => "NONE",
        "template_file" => "login.html",
        "dependencies" => Array("RenderConfig" => RENDER_DEPTH_NONE),
    ),
    "forgotpassword" => Array(
        "page_title" => "Forgot Your Password?",
        "authorization_level" => "NONE",
        "template_file" => "forgotpassword.html",
        "dependencies" => Array("RenderConfig" => RENDER_DEPTH_NONE),
    ),
    "submit" => Array(
        "page_title" => "Submit Game",
        "authorization_level" => "USER",
        "template_file" => "submit.html",
        "dependencies" => Array("RenderConfig" => RENDER_DEPTH_NONE),
    ),  
    "newjam" => Array(
        "page_title" => "Schedule New Jam",
        "authorization_level" => "ADMIN",
        "template_file" => "newjam.html",
        "dependencies" => Array("RenderThemes" => RENDER_DEPTH_NONE, "RenderConfig" => RENDER_DEPTH_NONE),
    ),  
    "assets" => Array(
        "page_title" => "Assets",
        "authorization_level" => "NONE",
        "template_file" => "assets.html",
        "dependencies" => Array("RenderLoggedInUser" => RENDER_DEPTH_NONE, "RenderAssets" => RENDER_DEPTH_NONE),
    ),  
    "editasset" => Array(
        "page_title" => "Edit Asset",
        "authorization_level" => "ADMIN",
        "template_file" => "editasset.html",
        "dependencies" => Array("RenderConfig" => RENDER_DEPTH_NONE),
    ),
    "rules" => Array(
        "page_title" => "Rules",
        "authorization_level" => "NONE",
        "template_file" => "rules.html",
        "dependencies" => Array("RenderConfig" => RENDER_DEPTH_NONE),
    ),  
    "config" => Array(
        "page_title" => "Configuration",
        "authorization_level" => "ADMIN",
        "template_file" => "config.html",
        "dependencies" => Array("RenderConfig" => RENDER_DEPTH_NONE),
    ),
    "editcontent" => Array(
        "page_title" => "Manage Content",
        "authorization_level" => "ADMIN",
        "template_file" => "editcontent.html",
        "dependencies" => Array("RenderAllJams" => RENDER_DEPTH_JAMS_GAMES),
    ),  
    "editjam" => Array(
        "page_title" => "Edit Jam",
        "authorization_level" => "ADMIN",
        "template_file" => "editjam.html",
        "dependencies" => Array(),
    ),  
    "editentry" => Array(
        "page_title" => "Edit Entry",
        "authorization_level" => "ADMIN",
        "template_file" => "editentry.html",
        "dependencies" => Array(  ),
    ),  
    "editusers" => Array(
        "page_title" => "Manage Users",
        "authorization_level" => "ADMIN",
        "template_file" => "editusers.html",
        "dependencies" => Array("RenderConfig" => RENDER_DEPTH_NONE, "RenderUsers" => RENDER_DEPTH_USERS),
    ),  
    "edituser" => Array(
        "page_title" => "Edit User",
        "authorization_level" => "ADMIN",
        "template_file" => "edituser.html",
        "dependencies" => Array("RenderConfig" => RENDER_DEPTH_NONE),
    ),
    "themes" => Array(
        "page_title" => "Theme Voting",
        "authorization_level" => "USER",
        "template_file" => "themes.html",
        "dependencies" => Array("RenderLoggedInUser" => RENDER_DEPTH_NONE, "RenderCookies" => RENDER_DEPTH_NONE, "RenderThemes" => RENDER_DEPTH_THEME_IDEAS),
    ),
    "managethemes" => Array(
        "page_title" => "Manage Themes",
        "authorization_level" => "ADMIN",
        "template_file" => "managethemes.html",
        "dependencies" => Array("RenderLoggedInUser" => RENDER_DEPTH_NONE, "RenderCookies" => RENDER_DEPTH_NONE, "RenderThemes" => RENDER_DEPTH_NONE),
    ), 
    "usersettings" => Array(
        "page_title" => "User Settings",
        "authorization_level" => "USER",
        "template_file" => "usersettings.html",
        "dependencies" => Array("RenderConfig" => RENDER_DEPTH_NONE, "RenderLoggedInUser" => RENDER_DEPTH_NONE),
    ), 
    "entries" => Array(
        "page_title" => "Entries",
        "authorization_level" => "NONE",
        "template_file" => "entries.html",
        "dependencies" => Array("RenderGames" => RENDER_DEPTH_GAMES),
    ),  
    "jam" => Array(
        "page_title" => "Jam",
        "authorization_level" => "NONE",
        "template_file" => "jam.html",
        "dependencies" => Array("RenderConfig" => RENDER_DEPTH_NONE),
    ),  
    "jams" => Array(
        "page_title" => "Jams",
        "authorization_level" => "NONE",
        "template_file" => "jams.html",
        "dependencies" => Array("RenderAllJams" => RENDER_DEPTH_JAMS),
    ),  
    "author" => Array(
        "page_title" => "Author",
        "authorization_level" => "NONE",
        "template_file" => "author.html",
        "dependencies" => Array(  ),
    ),  
    "authors" => Array(
        "page_title" => "Authors",
        "authorization_level" => "NONE",
        "template_file" => "authors.html",
        "dependencies" => Array("RenderUsers" => RENDER_DEPTH_USERS),
    ),  
    "privacy" => Array(
        "page_title" => "Privacy",
        "authorization_level" => "NONE",
        "template_file" => "privacy.html",
        "dependencies" => Array(  ),
    ),  
    "userdata" => Array(
        "page_title" => "User Data",
        "authorization_level" => "USER",
        "template_file" => "userdata.html",
        "dependencies" => Array(  ),
    ),
    "adminlog" => Array(
        "page_title" => "Admin Log",
        "authorization_level" => "ADMIN",
        "template_file" => "adminlog.html",
        "dependencies" => Array("RenderAdminLog" => RENDER_DEPTH_NONE),
    ),
    "polls" => Array(
        "page_title" => "Poll Results",
        "authorization_level" => "ADMIN",
        "template_file" => "polls.html",
        "dependencies" => Array("RenderPolls" => RENDER_DEPTH_NONE),
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
                $dependencies[$depIndex]["RenderDepth"] = $dependencyKey | $dependencyRenderDepth;
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
                $dependencies[$depIndex]["RenderDepth"] = $dependencyKey | $dependencyRenderDepth;
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