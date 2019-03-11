<?php

function LoadSiteActions(&$config){
	AddActionLog("LoadSiteActions");
	StartTimer("LoadSiteActions");
    
    //Actions data: The data in this list governs how site actions are performed
    $actions = Array(
        Array(
            "POST_REQUEST" => "login",
            "PHP_FILE" => "php/actions/authentication/login.php",
            "REDIRECT_AFTER_EXECUTION" => "?page=main",
            "ACTION_RESULT" => Array(
                "SUCCESS" => Array("REDIRECT_URL" => "?page=main", "MESSAGE_TYPE" => "success", "MESSAGE_TEXT" => "Logged in successfully"),
                "INVALID_PASSWORD_LENGTH" => Array("REDIRECT_URL" => "?page=login", "MESSAGE_TYPE" => "warning", "MESSAGE_TEXT" => "Incorrect password length. Must be between ".$config["MINIMUM_PASSWORD_LENGTH"]["VALUE"]." and ".$config["MAXIMUM_PASSWORD_LENGTH"]["VALUE"]." characters long."),
                "INVALID_USERNAME_LENGTH" => Array("REDIRECT_URL" => "?page=login", "MESSAGE_TYPE" => "warning", "MESSAGE_TEXT" => "Incorrect username length. Must be between ".$config["MINIMUM_USERNAME_LENGTH"]["VALUE"]." and ".$config["MAXIMUM_USERNAME_LENGTH"]["VALUE"]." characters long."),
                "USERNAME_ALREADY_REGISTERED" => Array("REDIRECT_URL" => "?page=login", "MESSAGE_TYPE" => "error", "MESSAGE_TEXT" => "There is already a user with that username. Please log in or choose another."),
                "USER_DOES_NOT_EXIST" => Array("REDIRECT_URL" => "?page=login", "MESSAGE_TYPE" => "error", "MESSAGE_TEXT" => "The user does not exist."),
                "INCORRECT_PASSWORD" => Array("REDIRECT_URL" => "?page=login", "MESSAGE_TYPE" => "warning", "MESSAGE_TEXT" => "Incorrect username/password combination."),
            )
        ),
        Array(
            "POST_REQUEST" => "logout",
            "PHP_FILE" => "php/actions/authentication/logout.php",
            "REDIRECT_AFTER_EXECUTION" => "?page=main",
            "ACTION_RESULT" => Array(
                "SUCCESS" => Array("REDIRECT_URL" => "?page=main", "MESSAGE_TYPE" => "success", "MESSAGE_TEXT" => "Logged out successfully")
            )
        ),
        Array(
            "POST_REQUEST" => "submit",
            "PHP_FILE" => "php/actions/games/submit.php",
            "REDIRECT_AFTER_EXECUTION" => "?page=main",
            "ACTION_RESULT" => Array(
                "SUCCESS_ENTRY_ADDED" => Array("REDIRECT_URL" => "?page=main", "MESSAGE_TYPE" => "success", "MESSAGE_TEXT" => "Game Added."),
                "SUCCESS_ENTRY_UPDATED" => Array("REDIRECT_URL" => "?page=main", "MESSAGE_TYPE" => "success", "MESSAGE_TEXT" => "Game Updated."),
                "NOT_LOGGED_IN" => Array("REDIRECT_URL" => "?page=login", "MESSAGE_TYPE" => "warning", "MESSAGE_TEXT" => "Not Logged In."),
                "MISSING_GAME_NAME" => Array("REDIRECT_URL" => "?page=submit", "MESSAGE_TYPE" => "warning", "MESSAGE_TEXT" => "Missing Game Name."),
                "INVALID_GAME_URL" => Array("REDIRECT_URL" => "?page=submit", "MESSAGE_TYPE" => "warning", "MESSAGE_TEXT" => "Invalid Game URL."),
                "INVALID_DESCRIPTION" => Array("REDIRECT_URL" => "?page=submit", "MESSAGE_TYPE" => "warning", "MESSAGE_TEXT" => "Missing Description."),
                "INVALID_JAM_NUMBER" => Array("REDIRECT_URL" => "?page=submit", "MESSAGE_TYPE" => "error", "MESSAGE_TEXT" => "Invalid Jam Number, please contact administrators."),
                "NO_JAM_TO_SUBMIT_TO" => Array("REDIRECT_URL" => "?page=submit", "MESSAGE_TYPE" => "error", "MESSAGE_TEXT" => "There is no active jam to submit to, please contact administrators."),
                "INVALID_COLOR" => Array("REDIRECT_URL" => "?page=submit", "MESSAGE_TYPE" => "warning", "MESSAGE_TEXT" => "The selected color is not valid."),
                "SCREENSHOT_NOT_AN_IMAGE" => Array("REDIRECT_URL" => "?page=submit", "MESSAGE_TYPE" => "warning", "MESSAGE_TEXT" => "The uploaded screenshot is not an image."),
                "SCREENSHOT_TOO_BIG" => Array("REDIRECT_URL" => "?page=submit", "MESSAGE_TYPE" => "warning", "MESSAGE_TEXT" => "The uploaded screenshot is too big."),
                "SCREENSHOT_WRONG_FILE_TYPE" => Array("REDIRECT_URL" => "?page=submit", "MESSAGE_TYPE" => "warning", "MESSAGE_TEXT" => "Screenshot is not of a valid file type."),
                "CANNOT_SUBMIT_TO_PAST_JAM" => Array("REDIRECT_URL" => "?page=submit", "MESSAGE_TYPE" => "error", "MESSAGE_TEXT" => "Cannot submit to a past jam, please contact administrators."),
            )
        ),
        Array(
            "POST_REQUEST" => "newjam",
            "PHP_FILE" => "php/actions/jam/newjam.php",
            "REDIRECT_AFTER_EXECUTION" => "?page=main",
            "ACTION_RESULT" => Array(
                "SUCCESS" => Array("REDIRECT_URL" => "?page=editcontent", "MESSAGE_TYPE" => "success", "MESSAGE_TEXT" => "Jam scheduled."),
                "INVALID_TIME" => Array("REDIRECT_URL" => "?page=newjam", "MESSAGE_TYPE" => "warning", "MESSAGE_TEXT" => "Time is not valid."),
                "INVALID_DATE" => Array("REDIRECT_URL" => "?page=newjam", "MESSAGE_TYPE" => "warning", "MESSAGE_TEXT" => "Date is not valid."),
                "INVALID_THEME" => Array("REDIRECT_URL" => "?page=newjam", "MESSAGE_TYPE" => "warning", "MESSAGE_TEXT" => "Theme is not valid."),
                "INVALID_JAM_NUMBER" => Array("REDIRECT_URL" => "?page=newjam", "MESSAGE_TYPE" => "error", "MESSAGE_TEXT" => "Jam number is not valid."),
                "NOT_AUTHORIZED" => Array("REDIRECT_URL" => "?page=main", "MESSAGE_TYPE" => "error", "MESSAGE_TEXT" => "Only admins can perform this action."),
                "NOT_LOGGED_IN" => Array("REDIRECT_URL" => "?page=login", "MESSAGE_TYPE" => "warning", "MESSAGE_TEXT" => "Not logged in."),
            )
        ),
        Array(
            "POST_REQUEST" => "deletejam",
            "PHP_FILE" => "php/actions/jam/deletejam.php",
            "REDIRECT_AFTER_EXECUTION" => "?page=main",
            "ACTION_RESULT" => Array(
                "SUCCESS" => Array("REDIRECT_URL" => "?page=editcontent", "MESSAGE_TYPE" => "success", "MESSAGE_TEXT" => "Jam Deleted."),
                "NO_JAMS_EXIST" => Array("REDIRECT_URL" => "?page=editcontent", "MESSAGE_TYPE" => "error", "MESSAGE_TEXT" => "No jams exist."),
                "INVALID_JAM_ID" => Array("REDIRECT_URL" => "?page=editcontent", "MESSAGE_TYPE" => "error", "MESSAGE_TEXT" => "Invalid jam id."),
                "CANNOT_DELETE_JAM" => Array("REDIRECT_URL" => "?page=editcontent", "MESSAGE_TYPE" => "error", "MESSAGE_TEXT" => "Cannot delete jam."),
                "NOT_AUTHORIZED" => Array("REDIRECT_URL" => "?page=editcontent", "MESSAGE_TYPE" => "error", "MESSAGE_TEXT" => "Only admins can perform this action."),
            )
        ),
        Array(
            "POST_REQUEST" => "deleteentry",
            "PHP_FILE" => "php/actions/games/deleteentry.php",
            "REDIRECT_AFTER_EXECUTION" => "?page=main",
            "ACTION_RESULT" => Array(
                "SUCCESS" => Array("REDIRECT_URL" => "?page=editcontent", "MESSAGE_TYPE" => "success", "MESSAGE_TEXT" => "Entry deleted."),
                "NO_JAMS_EXIST" => Array("REDIRECT_URL" => "?page=editcontent", "MESSAGE_TYPE" => "error", "MESSAGE_TEXT" => "No jams exist."),
                "INVALID_JAM_ID" => Array("REDIRECT_URL" => "?page=editcontent", "MESSAGE_TYPE" => "error", "MESSAGE_TEXT" => "Invalid jam id."),
                "CANNOT_DELETE_ENTRY" => Array("REDIRECT_URL" => "?page=editcontent", "MESSAGE_TYPE" => "error", "MESSAGE_TEXT" => "cannot delete entry."),
                "NOT_AUTHORIZED" => Array("REDIRECT_URL" => "?page=editcontent", "MESSAGE_TYPE" => "error", "MESSAGE_TEXT" => "Only admins can perform this action."),
            )
        ),
        Array(
            "POST_REQUEST" => "saveconfig",
            "PHP_FILE" => "php/actions/config/saveconfig.php",
            "REDIRECT_AFTER_EXECUTION" => "?page=main",
            "ACTION_RESULT" => Array(
                "SUCCESS" => Array("REDIRECT_URL" => "?page=config", "MESSAGE_TYPE" => "success", "MESSAGE_TEXT" => "Config updated."),
                "NOT_AUTHORIZED" => Array("REDIRECT_URL" => "?page=main", "MESSAGE_TYPE" => "error", "MESSAGE_TEXT" => "Only admins can perform this action."),
                "NO_CHANGE" => Array("REDIRECT_URL" => "?page=config", "MESSAGE_TYPE" => "warning", "MESSAGE_TEXT" => "No changes to config."),
            )
        ),
        Array(
            "POST_REQUEST" => "saveassetedits",
            "PHP_FILE" => "php/actions/asset/saveassetedits.php",
            "REDIRECT_AFTER_EXECUTION" => "?page=main",
            "ACTION_RESULT" => Array(
                "SUCCESS_INSERTED" => Array("REDIRECT_URL" => "?page=assets", "MESSAGE_TYPE" => "success", "MESSAGE_TEXT" => "Asset added."),
                "SUCCESS_UPDATED" => Array("REDIRECT_URL" => "?page=assets", "MESSAGE_TYPE" => "success", "MESSAGE_TEXT" => "Asset updated"),
                "COULD_NOT_DETERMINE_URL" => Array("REDIRECT_URL" => "?page=assets", "MESSAGE_TYPE" => "error", "MESSAGE_TEXT" => "Could not find a URL for the asset. Please look at the assets folder on the web server."),
                "UNLOADED_ASSET_TOO_BIG" => Array("REDIRECT_URL" => "?page=assets", "MESSAGE_TYPE" => "warning", "MESSAGE_TEXT" => "Asset too big."),
                "COULD_NOT_FIND_VALID_FILE_NAME" => Array("REDIRECT_URL" => "?page=assets", "MESSAGE_TYPE" => "warning", "MESSAGE_TEXT" => "Could not find a file name for the asset. Please look at the assets folder on the web server."),
                "INVALID_ASSET_TYPE" => Array("REDIRECT_URL" => "?page=assets", "MESSAGE_TYPE" => "warning", "MESSAGE_TEXT" => "Invalid asset type."),
                "ASSET_TYPE_EMPTY" => Array("REDIRECT_URL" => "?page=assets", "MESSAGE_TYPE" => "warning", "MESSAGE_TEXT" => "Missing asset type."),
                "INVALID_DESCRIPTION" => Array("REDIRECT_URL" => "?page=assets", "MESSAGE_TYPE" => "warning", "MESSAGE_TEXT" => "Invalid description."),
                "INVALID_TITLE" => Array("REDIRECT_URL" => "?page=assets", "MESSAGE_TYPE" => "warning", "MESSAGE_TEXT" => "Invalid title."),
                "INVALID_AUTHOR" => Array("REDIRECT_URL" => "?page=assets", "MESSAGE_TYPE" => "warning", "MESSAGE_TEXT" => "Invalid author - must match a username of a registered user."),
                "AUTHOR_EMPTY" => Array("REDIRECT_URL" => "?page=assets", "MESSAGE_TYPE" => "warning", "MESSAGE_TEXT" => "missing author."),
                "NOT_AUTHORIZED" => Array("REDIRECT_URL" => "?page=main", "MESSAGE_TYPE" => "error", "MESSAGE_TEXT" => "Only admins can perform this action."),
            )
        ),
        Array(
            "POST_REQUEST" => "deleteasset",
            "PHP_FILE" => "php/actions/asset/deleteasset.php",
            "REDIRECT_AFTER_EXECUTION" => "?page=main",
            "ACTION_RESULT" => Array(
                "SUCCESS" => Array("REDIRECT_URL" => "?page=assets", "MESSAGE_TYPE" => "success", "MESSAGE_TEXT" => "Asset deleted."),
                "ASSET_DOES_NOT_EXIST" => Array("REDIRECT_URL" => "?page=assets", "MESSAGE_TYPE" => "error", "MESSAGE_TEXT" => "asset does not exist."),
                "NOT_AUTHORIZED" => Array("REDIRECT_URL" => "?page=assets", "MESSAGE_TYPE" => "error", "MESSAGE_TEXT" => "Only admins can perform this action."),
            )
        ),
        Array(
            "POST_REQUEST" => "savejamedits",
            "PHP_FILE" => "php/actions/jam/savejamedits.php",
            "REDIRECT_AFTER_EXECUTION" => "?page=main",
            "ACTION_RESULT" => Array(
                "SUCCESS" => Array("REDIRECT_URL" => "?page=editcontent", "MESSAGE_TYPE" => "success", "MESSAGE_TEXT" => "Jam updated."),
                "INVALID_TIME" => Array("REDIRECT_URL" => "?page=editcontent", "MESSAGE_TYPE" => "warning", "MESSAGE_TEXT" => "Invalid time."),
                "INVALID_DATE" => Array("REDIRECT_URL" => "?page=editcontent", "MESSAGE_TYPE" => "warning", "MESSAGE_TEXT" => "Invalid date."),
                "INVALID_THEME" => Array("REDIRECT_URL" => "?page=editcontent", "MESSAGE_TYPE" => "warning", "MESSAGE_TEXT" => "Invalid theme."),
                "INVALID_JAM_NUMBER" => Array("REDIRECT_URL" => "?page=editcontent", "MESSAGE_TYPE" => "error", "MESSAGE_TEXT" => "Invalid jam number"),
                "NOT_AUTHORIZED" => Array("REDIRECT_URL" => "?page=main", "MESSAGE_TYPE" => "error", "MESSAGE_TEXT" => "Only admins can perform this action."),
                "NO_JAMS_EXIST" => Array("REDIRECT_URL" => "?page=editcontent", "MESSAGE_TYPE" => "error", "MESSAGE_TEXT" => "No jams exist."),
                "INVALID_JAM_ID" => Array("REDIRECT_URL" => "?page=editcontent", "MESSAGE_TYPE" => "error", "MESSAGE_TEXT" => "Invalid jam id."),
                "INVALID_COLOR" => Array("REDIRECT_URL" => "?page=editcontent", "MESSAGE_TYPE" => "warning", "MESSAGE_TEXT" => "Invalid colors."),
            )
        ),
        Array(
            "POST_REQUEST" => "saveuseredits",
            "PHP_FILE" => "php/actions/user/saveuseredits.php",
            "REDIRECT_AFTER_EXECUTION" => "?page=main",
            "ACTION_RESULT" => Array(
                "SUCCESS" => Array("REDIRECT_URL" => "?page=editusers", "MESSAGE_TYPE" => "success", "MESSAGE_TEXT" => "User successfully edited"),
                "USER_DOES_NOT_EXIST" => Array("REDIRECT_URL" => "?page=editusers", "MESSAGE_TYPE" => "error", "MESSAGE_TEXT" => "User does not exist."),
                "INVALID_ISADMIN" => Array("REDIRECT_URL" => "?page=editusers", "MESSAGE_TYPE" => "error", "MESSAGE_TEXT" => "Invalid IsAdmin."),
                "NOT_AUTHORIZED" => Array("REDIRECT_URL" => "?page=main", "MESSAGE_TYPE" => "error", "MESSAGE_TEXT" => "Only admins can perform this action."),
            )
        ),
        Array(
            "POST_REQUEST" => "savenewuserpassword",
            "PHP_FILE" => "php/actions/user/savenewuserpassword.php",
            "REDIRECT_AFTER_EXECUTION" => "?page=main",
            "ACTION_RESULT" => Array(
                "SUCCESS" => Array("REDIRECT_URL" => "?page=editusers", "MESSAGE_TYPE" => "success", "MESSAGE_TEXT" => "Password Updated."),
                "USER_DOES_NOT_EXIST" => Array("REDIRECT_URL" => "?page=editusers", "MESSAGE_TYPE" => "error", "MESSAGE_TEXT" => "User does not exist."),
                "INVALID_PASSWORD_LENGTH" => Array("REDIRECT_URL" => "?page=editusers", "MESSAGE_TYPE" => "warning", "MESSAGE_TEXT" => "Incorrect password length. Must be between ".$config["MINIMUM_PASSWORD_LENGTH"]["VALUE"]." and ".$config["MAXIMUM_PASSWORD_LENGTH"]["VALUE"]." characters long."),
                "PASSWORDS_DONT_MATCH" => Array("REDIRECT_URL" => "?page=editusers", "MESSAGE_TYPE" => "warning", "MESSAGE_TEXT" => "Passwords do not match."),
                "NOT_AUTHORIZED" => Array("REDIRECT_URL" => "?page=main", "MESSAGE_TYPE" => "error", "MESSAGE_TEXT" => "Only admins can perform this action."),
            )
        ),
        Array(
            "POST_REQUEST" => "changepassword",
            "PHP_FILE" => "php/actions/user/changepassword.php",
            "REDIRECT_AFTER_EXECUTION" => "?page=main",
            "ACTION_RESULT" => Array(
                "SUCCESS" => Array("REDIRECT_URL" => "?page=usersettings", "MESSAGE_TYPE" => "success", "MESSAGE_TEXT" => "Password Updated."),
                "USER_DOES_NOT_EXIST" => Array("REDIRECT_URL" => "?page=usersettings", "MESSAGE_TYPE" => "error", "MESSAGE_TEXT" => "User does not exist."),
                "INVALID_PASSWORD_LENGTH" => Array("REDIRECT_URL" => "?page=usersettings", "MESSAGE_TYPE" => "warning", "MESSAGE_TEXT" => "Incorrect password length. Must be between ".$config["MINIMUM_PASSWORD_LENGTH"]["VALUE"]." and ".$config["MAXIMUM_PASSWORD_LENGTH"]["VALUE"]." characters long."),
                "PASSWORDS_DONT_MATCH" => Array("REDIRECT_URL" => "?page=usersettings", "MESSAGE_TYPE" => "warning", "MESSAGE_TEXT" => "Passwords do not match."),
                "INCORRECT_PASSWORD" => Array("REDIRECT_URL" => "?page=usersettings", "MESSAGE_TYPE" => "warning", "MESSAGE_TEXT" => "Old password is not correct."),
                "NOT_LOGGED_IN" => Array("REDIRECT_URL" => "?page=login", "MESSAGE_TYPE" => "warning", "MESSAGE_TEXT" => "Not logged in."),
            )
        ),
        Array(
            "POST_REQUEST" => "saveuserchanges",
            "PHP_FILE" => "php/actions/user/saveuserchanges.php",
            "REDIRECT_AFTER_EXECUTION" => "?page=main",
            "ACTION_RESULT" => Array(
                "SUCCESS" => Array("REDIRECT_URL" => "?page=usersettings", "MESSAGE_TYPE" => "success", "MESSAGE_TEXT" => "User settings updated."),
                "INVALID_EMAIL" => Array("REDIRECT_URL" => "?page=usersettings", "MESSAGE_TYPE" => "warning", "MESSAGE_TEXT" => "Email is not valid."),
                "INVALID_DISPLAY_NAME" => Array("REDIRECT_URL" => "?page=usersettings", "MESSAGE_TYPE" => "warning", "MESSAGE_TEXT" => "Incorrect display name length. Must be between ".$config["MINIMUM_DISPLAY_NAME_LENGTH"]["VALUE"]." and ".$config["MAXIMUM_DISPLAY_NAME_LENGTH"]["VALUE"]." characters long."),
                "NOT_LOGGED_IN" => Array("REDIRECT_URL" => "?page=login", "MESSAGE_TYPE" => "warning", "MESSAGE_TEXT" => "Not logged in."),
            )
        ),
        Array(
            "POST_REQUEST" => "savenewtheme",
            "PHP_FILE" => "php/actions/theme/savenewtheme.php",
            "REDIRECT_AFTER_EXECUTION" => "?page=main",
            "ACTION_RESULT" => Array(
                "SUCCESS" => Array("REDIRECT_URL" => "?page=themes", "MESSAGE_TYPE" => "success", "MESSAGE_TEXT" => "Theme added."),
                "THEME_ALREADY_SUGGESTED" => Array("REDIRECT_URL" => "?page=themes", "MESSAGE_TYPE" => "warning", "MESSAGE_TEXT" => "Theme is already suggested."),
                "INVALID_THEME" => Array("REDIRECT_URL" => "?page=themes", "MESSAGE_TYPE" => "warning", "MESSAGE_TEXT" => "Theme is not valid."),
                "NOT_LOGGED_IN" => Array("REDIRECT_URL" => "?page=login", "MESSAGE_TYPE" => "warning", "MESSAGE_TEXT" => "Not logged in."),
                "THEME_RECENTLY_USED" => Array("REDIRECT_URL" => "?page=themes", "MESSAGE_TYPE" => "warning", "MESSAGE_TEXT" => "Theme has been used in a recent jam.")
            )
        ),
        Array(
            "POST_REQUEST" => "deletetheme",
            "PHP_FILE" => "php/actions/theme/deletetheme.php",
            "REDIRECT_AFTER_EXECUTION" => "?page=main",
            "ACTION_RESULT" => Array(
                "SUCCESS" => Array("REDIRECT_URL" => "?page=themes", "MESSAGE_TYPE" => "success", "MESSAGE_TEXT" => "Theme deleted."),
                "INVALID_THEME" => Array("REDIRECT_URL" => "?page=themes", "MESSAGE_TYPE" => "warning", "MESSAGE_TEXT" => "Theme is not valid."),
                "THEME_DOES_NOT_EXIST" => Array("REDIRECT_URL" => "?page=themes", "MESSAGE_TYPE" => "warning", "MESSAGE_TEXT" => "Theme does not exist."),
                "NOT_AUTHORIZED" => Array("REDIRECT_URL" => "?page=main", "MESSAGE_TYPE" => "error", "MESSAGE_TEXT" => "Only admins can perform this action."),
                "NOT_LOGGED_IN" => Array("REDIRECT_URL" => "?page=login", "MESSAGE_TYPE" => "warning", "MESSAGE_TEXT" => "Not logged in."),
            )
        ),
        Array(
            "POST_REQUEST" => "deletethemes",
            "PHP_FILE" => "php/actions/theme/deletethemes.php",
            "REDIRECT_AFTER_EXECUTION" => "?page=main",
            "ACTION_RESULT" => Array(
                "SUCCESS" => Array("REDIRECT_URL" => "?page=themes", "MESSAGE_TYPE" => "success", "MESSAGE_TEXT" => "Themes deleted."),
                "FAILURE" => Array("REDIRECT_URL" => "?page=themes", "MESSAGE_TYPE" => "warning", "MESSAGE_TEXT" => "One or more themes couldn't be deleted."),
                "NOT_AUTHORIZED" => Array("REDIRECT_URL" => "?page=main", "MESSAGE_TYPE" => "error", "MESSAGE_TEXT" => "Only admins can perform this action."),
                "NOT_LOGGED_IN" => Array("REDIRECT_URL" => "?page=login", "MESSAGE_TYPE" => "warning", "MESSAGE_TEXT" => "Not logged in."),
            )
        ),
        Array(
            "POST_REQUEST" => "bantheme",
            "PHP_FILE" => "php/actions/theme/bantheme.php",
            "REDIRECT_AFTER_EXECUTION" => "?page=main",
            "ACTION_RESULT" => Array(
                "SUCCESS" => Array("REDIRECT_URL" => "?page=themes", "MESSAGE_TYPE" => "success", "MESSAGE_TEXT" => "Theme banned."),
                "INVALID_THEME" => Array("REDIRECT_URL" => "?page=themes", "MESSAGE_TYPE" => "warning", "MESSAGE_TEXT" => "Invalid theme."),
                "THEME_DOES_NOT_EXIST" => Array("REDIRECT_URL" => "?page=themes", "MESSAGE_TYPE" => "warning", "MESSAGE_TEXT" => "Theme does not exist."),
                "NOT_AUTHORIZED" => Array("REDIRECT_URL" => "?page=main", "MESSAGE_TYPE" => "error", "MESSAGE_TEXT" => "Only admins can perform this action."),
                "NOT_LOGGED_IN" => Array("REDIRECT_URL" => "?page=login", "MESSAGE_TYPE" => "warning", "MESSAGE_TEXT" => "Not logged in."),
            )
        ),
        Array(
            "POST_REQUEST" => "unbantheme",
            "PHP_FILE" => "php/actions/theme/unbantheme.php",
            "REDIRECT_AFTER_EXECUTION" => "?page=main",
            "ACTION_RESULT" => Array(
                "SUCCESS" => Array("REDIRECT_URL" => "?page=themes", "MESSAGE_TYPE" => "success", "MESSAGE_TEXT" => "Theme unbanned."),
                "INVALID_THEME" => Array("REDIRECT_URL" => "?page=themes", "MESSAGE_TYPE" => "warning", "MESSAGE_TEXT" => "Invalid theme."),
                "THEME_DOES_NOT_EXIST" => Array("REDIRECT_URL" => "?page=themes", "MESSAGE_TYPE" => "warning", "MESSAGE_TEXT" => "Theme does not exist"),
                "NOT_AUTHORIZED" => Array("REDIRECT_URL" => "?page=main", "MESSAGE_TYPE" => "error", "MESSAGE_TEXT" => "Only admins can perform this action."),
                "NOT_LOGGED_IN" => Array("REDIRECT_URL" => "?page=login", "MESSAGE_TYPE" => "warning", "MESSAGE_TEXT" => "Not logged in."),
            )
        ),
        Array(
            "POST_REQUEST" => "downloaddb",
            "PHP_FILE" => "php/actions/db/downloaddb.php",
            "REDIRECT_AFTER_EXECUTION" => "?page=main",
            "ACTION_RESULT" => Array(

            )
        ),
        Array(
            "POST_REQUEST" => "adminvote",
            "PHP_FILE" => "php/actions/adminvote/adminvote.php",
            "REDIRECT_AFTER_EXECUTION" => "?page=main",
            "ACTION_RESULT" => Array(
                "SUCESS_UPDATE" => Array("REDIRECT_URL" => "?page=editusers", "MESSAGE_TYPE" => "success", "MESSAGE_TEXT" => "Admin vote updated."),
                "SUCESS_INSERT" => Array("REDIRECT_URL" => "?page=editusers", "MESSAGE_TYPE" => "success", "MESSAGE_TEXT" => "Admin vote cast."),
                "INVALID_VOTE_TYPE" => Array("REDIRECT_URL" => "?page=editusers", "MESSAGE_TYPE" => "warning", "MESSAGE_TEXT" => "Invalid vote type."),
                "NOT_AUTHORIZED" => Array("REDIRECT_URL" => "?page=main", "MESSAGE_TYPE" => "error", "MESSAGE_TEXT" => "Only admins can perform this action."),
                "NOT_LOGGED_IN" => Array("REDIRECT_URL" => "?page=login", "MESSAGE_TYPE" => "warning", "MESSAGE_TEXT" => "Not logged in."),
            )
        )
    );

	StopTimer("LoadSiteActions");
    return $actions;
}

function PerformPendingSiteAction(&$config, &$actions, &$loggedInUser){
    global $_POST;
	AddActionLog("PerformPendingSiteAction");
	StartTimer("PerformPendingSiteAction");

    //Actions!
    if(isset($_POST["action"])){
        foreach($actions as $i => $action){
            $actionPostRequest = $action["POST_REQUEST"];
            $actionPhpFile = $action["PHP_FILE"];
            $actionRedirectAfterExecution = $action["REDIRECT_AFTER_EXECUTION"];

            if($_POST["action"] == $actionPostRequest){
                $actionResult = "PROCESSING";
                include_once($actionPhpFile);
                $actionResult = PerformAction($loggedInUser);

                if(isset($action["ACTION_RESULT"][$actionResult]["REDIRECT_URL"])){
                    setcookie("actionResultAction", $actionPostRequest, time() + 30);
                    setcookie("actionResult", $actionResult, time() + 30);
                    $redirectURL = $action["ACTION_RESULT"][$actionResult]["REDIRECT_URL"];
                    header("Location: ".$redirectURL);
                    die("Redirecting to <a href='$actionRedirectAfterExecution'>$actionRedirectAfterExecution</a>...");
                }

                die("Unknown action result $actionResult for action $actionPostRequest. Please report this error to administrators.  <a href='?page=main'>back to index</a>...");
            }
        }
    }

	StopTimer("PerformPendingSiteAction");
}

?>