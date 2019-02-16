<?php
//ini_set('display_errors',1);
//ini_set('display_startup_errors',1);
//error_reporting(-1);
//
//print phpversion ();

include_once("php/site.php");
StartTimer("index.php");

//List allowed page identifiers here.
if(!(in_array($page, Array("main", "login", "submit", "newjam", "assets", "editasset", "rules", "config", "editcontent", "editjam", "editentry", "editusers", "edituser", "themes", "usersettings", "entries", "jam", "jams", "author", "authors", "privacy", "userdata", "adminlog")))){
	$page = "main";
}

//List of pages which require user to be logged in
if(in_array($page, Array("submit", "newjam", "editasset", "config", "editcontent", "editjam", "editentry", "editusers", "edituser", "themes", "usersettings", "userdata"))){
	if($loggedInUser === false){
		$page = "main";
	}
}

//List of pages which require administrator access
if(in_array($page, Array("newjam", "editasset", "config", "editcontent", "editjam", "editentry", "editusers", "edituser", "adminlog"))){
	if(IsAdmin($loggedInUser) === false){
		$page = "main";
	}
}

//Action data
$actions = Array(
	Array(
		"POST_REQUEST" => "login",
		"PHP_FILE" => "php/actions/login.php",
		"REDIRECT_AFTER_EXECUTION" => "?page=main",
		"ACTION_RESULT" => Array(
			"SUCCESS" => Array("REDIRECT_URL" => "?page=main", "MESSAGE_TYPE" => "success", "MESSAGE_TEXT" => "Logged in successfully"),
			"REGISTRATION_SUCCESS" => Array("REDIRECT_URL" => "?page=login", "MESSAGE_TYPE" => "error", "MESSAGE_TEXT" => "Registration succeeded but login failed. Please contact administrators for help."),
			"INVALID_PASSWORD_LENGTH" => Array("REDIRECT_URL" => "?page=login", "MESSAGE_TYPE" => "warning", "MESSAGE_TEXT" => "Incorrect password length. Must be between ".$config["MINIMUM_PASSWORD_LENGTH"]["VALUE"]." and ".$config["MAXIMUM_PASSWORD_LENGTH"]["VALUE"]." characters long."),
			"INVALID_USERNAME_LENGTH" => Array("REDIRECT_URL" => "?page=login", "MESSAGE_TYPE" => "warning", "MESSAGE_TEXT" => "Incorrect username length. Must be between ".$config["MINIMUM_USERNAME_LENGTH"]["VALUE"]." and ".$config["MAXIMUM_USERNAME_LENGTH"]["VALUE"]." characters long."),
			"USERNAME_ALREADY_REGISTERED" => Array("REDIRECT_URL" => "?page=login", "MESSAGE_TYPE" => "error", "MESSAGE_TEXT" => "There is already a user with that username. Please log in or choose another."),
			"USER_DOES_NOT_EXIST" => Array("REDIRECT_URL" => "?page=login", "MESSAGE_TYPE" => "error", "MESSAGE_TEXT" => "The user does not exist."),
			"INCORRECT_PASSWORD" => Array("REDIRECT_URL" => "?page=login", "MESSAGE_TYPE" => "warning", "MESSAGE_TEXT" => "Incorrect username/password combination."),
		)
	),
	Array(
		"POST_REQUEST" => "logout",
		"PHP_FILE" => "php/actions/logout.php",
		"REDIRECT_AFTER_EXECUTION" => "?page=main",
		"ACTION_RESULT" => Array(
			"SUCCESS" => Array("REDIRECT_URL" => "?page=main", "MESSAGE_TYPE" => "success", "MESSAGE_TEXT" => "Logged out successfully")
		)
	),
	Array(
		"POST_REQUEST" => "submit",
		"PHP_FILE" => "php/actions/submit.php",
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
		"PHP_FILE" => "php/actions/newjam.php",
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
		"PHP_FILE" => "php/actions/deletejam.php",
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
		"PHP_FILE" => "php/actions/deleteentry.php",
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
		"PHP_FILE" => "php/actions/saveconfig.php",
		"REDIRECT_AFTER_EXECUTION" => "?page=main",
		"ACTION_RESULT" => Array(
			"SUCCESS" => Array("REDIRECT_URL" => "?page=config", "MESSAGE_TYPE" => "success", "MESSAGE_TEXT" => "Config updated."),
			"NOT_AUTHORIZED" => Array("REDIRECT_URL" => "?page=main", "MESSAGE_TYPE" => "error", "MESSAGE_TEXT" => "Only admins can perform this action."),
			"NO_CHANGE" => Array("REDIRECT_URL" => "?page=config", "MESSAGE_TYPE" => "warning", "MESSAGE_TEXT" => "No changes to config."),
		)
	),
	Array(
		"POST_REQUEST" => "saveassetedits",
		"PHP_FILE" => "php/actions/saveassetedits.php",
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
		"PHP_FILE" => "php/actions/deleteasset.php",
		"REDIRECT_AFTER_EXECUTION" => "?page=main",
		"ACTION_RESULT" => Array(
			"SUCCESS" => Array("REDIRECT_URL" => "?page=assets", "MESSAGE_TYPE" => "success", "MESSAGE_TEXT" => "Asset deleted."),
			"ASSET_DOES_NOT_EXIST" => Array("REDIRECT_URL" => "?page=assets", "MESSAGE_TYPE" => "error", "MESSAGE_TEXT" => "asset does not exist."),
			"NOT_AUTHORIZED" => Array("REDIRECT_URL" => "?page=assets", "MESSAGE_TYPE" => "error", "MESSAGE_TEXT" => "Only admins can perform this action."),
		)
	),
	Array(
		"POST_REQUEST" => "savejamedits",
		"PHP_FILE" => "php/actions/savejamedits.php",
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
		"PHP_FILE" => "php/actions/saveuseredits.php",
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
		"PHP_FILE" => "php/actions/savenewuserpassword.php",
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
		"PHP_FILE" => "php/actions/changepassword.php",
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
		"PHP_FILE" => "php/actions/saveuserchanges.php",
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
		"PHP_FILE" => "php/actions/savenewtheme.php",
		"REDIRECT_AFTER_EXECUTION" => "?page=main",
		"ACTION_RESULT" => Array(
			"SUCCESS" => Array("REDIRECT_URL" => "?page=themes", "MESSAGE_TYPE" => "success", "MESSAGE_TEXT" => "The added."),
			"THEME_ALREADY_SUGGESTED" => Array("REDIRECT_URL" => "?page=themes", "MESSAGE_TYPE" => "warning", "MESSAGE_TEXT" => "Theme is already suggested."),
			"INVALID_THEME" => Array("REDIRECT_URL" => "?page=themes", "MESSAGE_TYPE" => "warning", "MESSAGE_TEXT" => "Theme is not valid."),
			"NOT_LOGGED_IN" => Array("REDIRECT_URL" => "?page=login", "MESSAGE_TYPE" => "warning", "MESSAGE_TEXT" => "Not logged in."),
		)
	),
	Array(
		"POST_REQUEST" => "deletetheme",
		"PHP_FILE" => "php/actions/deletetheme.php",
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
		"POST_REQUEST" => "bantheme",
		"PHP_FILE" => "php/actions/bantheme.php",
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
		"PHP_FILE" => "php/actions/unbantheme.php",
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
		"PHP_FILE" => "php/actions/downloaddb.php",
		"REDIRECT_AFTER_EXECUTION" => "?page=main",
		"ACTION_RESULT" => Array(

		)
	),
	Array(
		"POST_REQUEST" => "adminvote",
		"PHP_FILE" => "php/actions/adminvote.php",
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

//Messages and warnings
if(isset($_COOKIE["actionResult"]) && isset($_COOKIE["actionResultAction"])){
    $messageActionResult = $_COOKIE["actionResult"];
    $messageActionResultAction = $_COOKIE["actionResultAction"];

    $actionFound = false;
	foreach($actions as $i => $action){
		if($messageActionResultAction == $action["POST_REQUEST"]){
            $actionFound = true;
            if(isset($action["ACTION_RESULT"][$messageActionResult])){
                $actionResultData = $action["ACTION_RESULT"][$messageActionResult];

                $messageType = $actionResultData["MESSAGE_TYPE"];
                $messageText = $actionResultData["MESSAGE_TEXT"];

                switch($messageType){
                    case "success":
                        AddSuccess("Success", $messageText, false);
                        break;
                    case "warning":
                        AddWarning("Warning", $messageText, false);
                        break;
                    case "error":
                        AddError("Error", $messageText, false);
                        break;
                    case "none":
                        break;
                    default:
                        die("Unknown message type $messageType");
                        break;
                }
            }else{
                die("Action result $messageActionResult for $messageActionResultAction not found in actions list");
            }
        }
    }
    
    if(!$actionFound){
        die("Action $messageActionResultAction not found in actions list");
    }

    setcookie("actionResult", "", 0);
    setcookie("actionResultAction", "", 0);
}

//Actions!
if(isset($_POST["action"])){
	foreach($actions as $i => $action){
		$actionPostRequest = $action["POST_REQUEST"];
		$actionPhpFile = $action["PHP_FILE"];
		$actionRedirectAfterExecution = $action["REDIRECT_AFTER_EXECUTION"];

		if($_POST["action"] == $actionPostRequest){
			$actionResult = "PROCESSING";
			include_once($actionPhpFile);

			if(isset($action["ACTION_RESULT"][$actionResult]["REDIRECT_URL"])){
                setcookie("actionResultAction", $actionPostRequest, time() + 30);
                setcookie("actionResult", $actionResult, time() + 30);
				$redirectURL = $action["ACTION_RESULT"][$actionResult]["REDIRECT_URL"];
				header("Location: ".$redirectURL);
				die("Redirecting to <a href='$actionRedirectAfterExecution'>$actionRedirectAfterExecution</a>...");
			}

			die("Unknown action result $actionResult for action $actionPostRequest. Please report this error to administrators.  <a href='?page=mail'>back to index</a>...");
		}
	}
}

?>

			<?php
				print $mustache->render(file_get_contents($templateBasePath."header.html"), $dictionary);
			?>
			<div class="row">
				<div class="col-md-2">
					<?php
						print $mustache->render(file_get_contents($templateBasePath."menu.html"), $dictionary);
					?>
				</div>

				<?php
					print $mustache->render(file_get_contents($templateBasePath."message.html"), $dictionary);

					switch($page){
						case "main":
							print $mustache->render(file_get_contents($templateBasePath."main.html"), $dictionary);
						break;
						case "login":
							print $mustache->render(file_get_contents($templateBasePath."login.html"), $dictionary);
						break;
						case "submit":

							print $mustache->render(file_get_contents($templateBasePath."submit.html"), $dictionary);
						break;
						case "newjam":
							if(IsAdmin($loggedInUser) !== false){
								print $mustache->render(file_get_contents($templateBasePath."newjam.html"), $dictionary);
							}
						break;
						case "assets":
							print $mustache->render(file_get_contents($templateBasePath."assets.html"), $dictionary);
						break;
						case "rules":
							print $mustache->render(file_get_contents($templateBasePath."rules.html"), $dictionary);
						break;
						case "config":
							if(IsAdmin($loggedInUser) !== false){
								print $mustache->render(file_get_contents($templateBasePath."config.html"), $dictionary);
							}
						break;
						case "editasset":
							if(IsAdmin($loggedInUser) !== false){
								print $mustache->render(file_get_contents($templateBasePath."editasset.html"), $dictionary);
							}
						break;
						case "editcontent":
							if(IsAdmin($loggedInUser) !== false){
								print $mustache->render(file_get_contents($templateBasePath."editcontent.html"), $dictionary);
							}
						break;
						case "editjam":
							if(IsAdmin($loggedInUser) !== false){
								print $mustache->render(file_get_contents($templateBasePath."editjam.html"), $dictionary);
							}
						break;
						case "editentry":
							if(IsAdmin($loggedInUser) !== false){
								print $mustache->render(file_get_contents($templateBasePath."editentry.html"), $dictionary);
							}
						break;
						case "editusers":
							if(IsAdmin($loggedInUser) !== false){
								print $mustache->render(file_get_contents($templateBasePath."editusers.html"), $dictionary);
							}
						break;
						case "edituser":
							if(IsAdmin($loggedInUser) !== false){
								print $mustache->render(file_get_contents($templateBasePath."edituser.html"), $dictionary);
							}
						break;
						case "themes":
							print $mustache->render(file_get_contents($templateBasePath."themes.html"), $dictionary);
						break;
						case "usersettings":
							print $mustache->render(file_get_contents($templateBasePath."usersettings.html"), $dictionary);
						break;
						case "entries":
							print $mustache->render(file_get_contents($templateBasePath."entries.html"), $dictionary);
						break;
						case "jam":
							print $mustache->render(file_get_contents($templateBasePath."jam.html"), $dictionary);
						break;
						case "jams":
							print $mustache->render(file_get_contents($templateBasePath."jams.html"), $dictionary);
						break;
						case "author":
							print $mustache->render(file_get_contents($templateBasePath."author.html"), $dictionary);
						break;
						case "authors":
							print $mustache->render(file_get_contents($templateBasePath."authors.html"), $dictionary);
						break;
						case "privacy":
							print $mustache->render(file_get_contents($templateBasePath."privacy.html"), $dictionary);
						break;
						case "userdata":
							print $mustache->render(file_get_contents($templateBasePath."userdata.html"), $dictionary);
						break;
						case "adminlog":
							print $mustache->render(file_get_contents($templateBasePath."adminlog.html"), $dictionary);
						break;
					}
				?>
			</div>
			<?php
				print $mustache->render(file_get_contents($templateBasePath."footer.html"), $dictionary);
			?>
		</div>

		<script src="vendor/components/Bootstrap/js/bootstrap.min.js"></script>
	</body>
</html>

<?php

StopTimer("index.php");


if(IsAdmin($loggedInUser) !== false){

	print "<pre>";
	var_dump($dictionary);
	print "</pre>";

	foreach($actionLog as $actionLogKey => $actionLogValue){
		if(isset($actionTimers[$actionLogKey])){
			$actionTimers[$actionLogKey]["calls"] = $actionLogValue;
		}else{
			$actionTimers[$actionLogKey] = Array("totalTime" => "not logged", "timerRunning" => "not logged", "lastTimestamp" => "not logged", "timeInSeconds" => "not logged", "calls" => $actionLogValue);
		}
	}
	
	print ArrayToHTML($actionTimers);
}

?>