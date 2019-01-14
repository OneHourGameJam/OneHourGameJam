<?php
//ini_set('display_errors',1);
//ini_set('display_startup_errors',1);
//error_reporting(-1);
//
//print phpversion ();
$nightmode = $_COOKIE["nightmode"];
if(isset($_GET["nightmode"])){
	$nightmode = $_GET["nightmode"];
	if($nightmode == 1){
		setcookie("nightmode", 1, time() + (60 * 60 * 24 * 365));
		$nightmode = 1;
	}else{
		setcookie("nightmode", null, -1);
		$nightmode = 0;
	}
}

include_once("php/site.php");
StartTimer("index.php");

//Determine whether the person is in streaming mode
if(isset($_GET["streaming"])){
	if($_GET["streaming"] == 1){
		setcookie("streaming", 1, time() + (60 * 60 * 3));	//Streamer mode lasts for 3 hours
		$dictionary["is_streamer"] = 1;
	}else{
		setcookie("streaming", null, -1);
	}
}else{
	if(isset($_COOKIE["streaming"]) && $_COOKIE["streaming"] == 1){
		$dictionary["is_streamer"] = 1;
	}
}

$templateBasePath = "template/";
$dictionary["template_path"] = $templateBasePath;

//List allowed page identifiers here.
if(!(in_array($page, Array("main", "login", "logout", "submit", "newjam", "assets", "editasset", "rules", "config", "editcontent", "editjam", "editentry", "editusers", "edituser", "themes", "usersettings", "entries", "jam", "jams", "author", "authors", "privacy", "userdata", "adminlog")))){
	$page = "main";
}

$pageTitles = Array(
	"main" => "Main Page",
	"login" => "Login",
	"logout" => "Logout",
	"submit" => "Submit Game",
	"newjam" => "Schedule New Jam",
	"assets" => "Assets",
	"editasset" => "Edit Asset",
	"rules" => "Rules",
	"config" => "Configuration",
	"editcontent" => "Manage Content",
	"editjam" => "Edit Jam",
	"editentry" => "Edit Entry",
	"editusers" => "Manage Users",
	"edituser" => "Edit User",
	"themes" => "Theme Voting",
	"usersettings" => "User Settings",
	"entries" => "Entries",
	"jam" => "Jam",
	"jams" => "Jams",
	"author" => "Author",
	"authors" => "Authors",
	"privacy" => "Privacy",
    "userdata" => "User Data",
    "adminlog" => "Admin Log"
);

//List of pages which require user to be logged in
if(in_array($page, Array("submit", "newjam", "editasset", "config", "editcontent", "editjam", "editentry", "editusers", "edituser", "themes", "usersettings", "userdata"))){
	if(!IsLoggedIn()){
		$page = "main";
	}
}

//List of pages which require administrator access
if(in_array($page, Array("newjam", "editasset", "config", "editcontent", "editjam", "editentry", "editusers", "edituser", "adminlog"))){
	if(!IsAdmin()){
		$page = "main";
	}
}

if($nightmode == 1){
	$dictionary["NIGHT_MODE"] = 1;
}

$actions = Array(
	Array(
		"POST_REQUEST" => "login",
		"PHP_FILE" => "php/actions/login.php",
		"REDIRECT_AFTER_EXECUTION" => "?page=main",
		"ACTION_RESULT" => Array(
			"SUCCESS" => Array("REDIRECT_URL" => "?page=main"),
			"REGISTRATION_SUCCESS" => Array("REDIRECT_URL" => "?page=login"),
			"INVALID_PASSWORD_LENGTH" => Array("REDIRECT_URL" => "?page=login"),
			"INVALID_USERNAME_LENGTH" => Array("REDIRECT_URL" => "?page=login"),
			"USERNAME_ALREADY_REGISTERED" => Array("REDIRECT_URL" => "?page=login"),
			"USER_DOES_NOT_EXIST" => Array("REDIRECT_URL" => "?page=login"),
			"INCORRECT_PASSWORD" => Array("REDIRECT_URL" => "?page=login"),
		)
	),
	Array(
		"POST_REQUEST" => "logout",
		"PHP_FILE" => "php/actions/logout.php",
		"REDIRECT_AFTER_EXECUTION" => "?page=main",
		"ACTION_RESULT" => Array(
			"SUCCESS" => Array("REDIRECT_URL" => "?page=main")
		)
	),
	Array(
		"POST_REQUEST" => "submit",
		"PHP_FILE" => "php/actions/submit.php",
		"REDIRECT_AFTER_EXECUTION" => "?page=main",
		"ACTION_RESULT" => Array(
			"SUCCESS_ENTRY_ADDED" => Array("REDIRECT_URL" => "?page=main"),
			"SUCCESS_ENTRY_UPDATED" => Array("REDIRECT_URL" => "?page=main"),
			"NOT_LOGGED_IN" => Array("REDIRECT_URL" => "?page=login"),
			"MISSING_GAME_NAME" => Array("REDIRECT_URL" => "?page=submit"),
			"INVALID_GAME_URL" => Array("REDIRECT_URL" => "?page=submit"),
			"INVALID_DESCRIPTION" => Array("REDIRECT_URL" => "?page=submit"),
			"INVALID_JAM_NUMBER" => Array("REDIRECT_URL" => "?page=submit"),
			"NO_JAM_TO_SUBMIT_TO" => Array("REDIRECT_URL" => "?page=submit"),
			"INVALID_COLOR" => Array("REDIRECT_URL" => "?page=submit"),
			"SCREENSHOT_NOT_AN_IMAGE" => Array("REDIRECT_URL" => "?page=submit"),
			"SCREENSHOT_TOO_BIT" => Array("REDIRECT_URL" => "?page=submit"),
			"SCREENSHOT_WRONG_FILE_TYPE" => Array("REDIRECT_URL" => "?page=submit"),
			"CANNOT_SUBMIT_TO_PAST_JAM" => Array("REDIRECT_URL" => "?page=submit"),
		)
	),
	Array(
		"POST_REQUEST" => "newjam",
		"PHP_FILE" => "php/actions/newjam.php",
		"REDIRECT_AFTER_EXECUTION" => "?page=main",
		"ACTION_RESULT" => Array(
			"SUCCESS" => Array("REDIRECT_URL" => "?page=editcontent"),
			"INVALID_TIME" => Array("REDIRECT_URL" => "?page=newjam"),
			"INVALID_DATE" => Array("REDIRECT_URL" => "?page=newjam"),
			"INVALID_THEME" => Array("REDIRECT_URL" => "?page=newjam"),
			"INVALID_JAM_NUMBER" => Array("REDIRECT_URL" => "?page=newjam"),
			"NOT_AUTHORIZED" => Array("REDIRECT_URL" => "?page=main"),
			"NOT_LOGGED_IN" => Array("REDIRECT_URL" => "?page=login"),
		)
	),
	Array(
		"POST_REQUEST" => "deletejam",
		"PHP_FILE" => "php/actions/deletejam.php",
		"REDIRECT_AFTER_EXECUTION" => "?page=main",
		"ACTION_RESULT" => Array(
			"SUCCESS" => Array("REDIRECT_URL" => "?page=editcontent"),
			"NO_JAMS_EXIST" => Array("REDIRECT_URL" => "?page=editcontent"),
			"INVALID_JAM_ID" => Array("REDIRECT_URL" => "?page=editcontent"),
			"CANNOT_DELETE_JAM" => Array("REDIRECT_URL" => "?page=editcontent"),
			"NOT_AUTHORIZED" => Array("REDIRECT_URL" => "?page=editcontent"),
		)
	),
	Array(
		"POST_REQUEST" => "deleteentry",
		"PHP_FILE" => "php/actions/deleteentry.php",
		"REDIRECT_AFTER_EXECUTION" => "?page=main",
		"ACTION_RESULT" => Array(
			"SUCCESS" => Array("REDIRECT_URL" => "?page=editcontent"),
			"NO_JAMS_EXIST" => Array("REDIRECT_URL" => "?page=editcontent"),
			"INVALID_JAM_ID" => Array("REDIRECT_URL" => "?page=editcontent"),
			"CANNOT_DELETE_ENTRY" => Array("REDIRECT_URL" => "?page=editcontent"),
			"NOT_AUTHORIZED" => Array("REDIRECT_URL" => "?page=editcontent"),
		)
	),
	Array(
		"POST_REQUEST" => "saveconfig",
		"PHP_FILE" => "php/actions/saveconfig.php",
		"REDIRECT_AFTER_EXECUTION" => "?page=main",
		"ACTION_RESULT" => Array(
			"SUCCESS" => Array("REDIRECT_URL" => "?page=config"),
			"NOT_AUTHORIZED" => Array("REDIRECT_URL" => "?page=main"),
			"NO_CHANGE" => Array("REDIRECT_URL" => "?page=config"),
		)
	),
	Array(
		"POST_REQUEST" => "saveassetedits",
		"PHP_FILE" => "php/actions/saveassetedits.php",
		"REDIRECT_AFTER_EXECUTION" => "?page=main",
		"ACTION_RESULT" => Array(
			"SUCCESS_INSERTED" => Array("REDIRECT_URL" => "?page=assets"),
			"SUCCESS_UPDATED" => Array("REDIRECT_URL" => "?page=assets"),
			"COULD_NOT_DETERMINE_URL" => Array("REDIRECT_URL" => "?page=assets"),
			"UNLOADED_ASSET_TOO_BIG" => Array("REDIRECT_URL" => "?page=assets"),
			"COULD_NOT_FIND_VALID_FILE_NAME" => Array("REDIRECT_URL" => "?page=assets"),
			"INVALID_ASSET_TYPE" => Array("REDIRECT_URL" => "?page=assets"),
			"ASSET_TYPE_EMPTY" => Array("REDIRECT_URL" => "?page=assets"),
			"INVALID_DESCRIPTION" => Array("REDIRECT_URL" => "?page=assets"),
			"INVALID_TITLE" => Array("REDIRECT_URL" => "?page=assets"),
			"INVALID_AUTHOR" => Array("REDIRECT_URL" => "?page=assets"),
			"AUTHOR_EMPTY" => Array("REDIRECT_URL" => "?page=assets"),
			"NOT_AUTHORIZED" => Array("REDIRECT_URL" => "?page=main"),
		)
	),
	Array(
		"POST_REQUEST" => "deleteasset",
		"PHP_FILE" => "php/actions/deleteasset.php",
		"REDIRECT_AFTER_EXECUTION" => "?page=main",
		"ACTION_RESULT" => Array(
			"SUCCESS" => Array("REDIRECT_URL" => "?page=assets"),
			"ASSET_DOES_NOT_EXIST" => Array("REDIRECT_URL" => "?page=assets"),
			"NOT_AUTHORIZED" => Array("REDIRECT_URL" => "?page=assets"),
		)
	),
	Array(
		"POST_REQUEST" => "savejamedits",
		"PHP_FILE" => "php/actions/savejamedits.php",
		"REDIRECT_AFTER_EXECUTION" => "?page=main",
		"ACTION_RESULT" => Array(
			"SUCCESS" => Array("REDIRECT_URL" => "?page=editcontent"),
			"INVALID_TIME" => Array("REDIRECT_URL" => "?page=editcontent"),
			"INVALID_DATE" => Array("REDIRECT_URL" => "?page=editcontent"),
			"INVALID_THEME" => Array("REDIRECT_URL" => "?page=editcontent"),
			"INVALID_JAM_NUMBER" => Array("REDIRECT_URL" => "?page=editcontent"),
			"NOT_AUTHORIZED" => Array("REDIRECT_URL" => "?page=main"),
			"NO_JAMS_EXIST" => Array("REDIRECT_URL" => "?page=editcontent"),
			"INVALID_JAM_ID" => Array("REDIRECT_URL" => "?page=editcontent"),
			"INVALID_COLOR" => Array("REDIRECT_URL" => "?page=editcontent"),
		)
	),
	Array(
		"POST_REQUEST" => "saveuseredits",
		"PHP_FILE" => "php/actions/saveuseredits.php",
		"REDIRECT_AFTER_EXECUTION" => "?page=main",
		"ACTION_RESULT" => Array(
			"SUCCESS" => Array("REDIRECT_URL" => "?page=editusers"),
			"USER_DOES_NOT_EXIST" => Array("REDIRECT_URL" => "?page=editusers"),
			"INVALID_ISADMIN" => Array("REDIRECT_URL" => "?page=editusers"),
			"NOT_AUTHORIZED" => Array("REDIRECT_URL" => "?page=main"),
		)
	),
	Array(
		"POST_REQUEST" => "savenewuserpassword",
		"PHP_FILE" => "php/actions/savenewuserpassword.php",
		"REDIRECT_AFTER_EXECUTION" => "?page=main",
		"ACTION_RESULT" => Array(
			"SUCCESS" => Array("REDIRECT_URL" => "?page=editusers"),
			"USER_DOES_NOT_EXIST" => Array("REDIRECT_URL" => "?page=editusers"),
			"INVALID_PASSWORD_LENGTH" => Array("REDIRECT_URL" => "?page=editusers"),
			"PASSWORDS_DONT_MATCH" => Array("REDIRECT_URL" => "?page=editusers"),
			"NOT_AUTHORIZED" => Array("REDIRECT_URL" => "?page=main"),
		)
	),
	Array(
		"POST_REQUEST" => "changepassword",
		"PHP_FILE" => "php/actions/changepassword.php",
		"REDIRECT_AFTER_EXECUTION" => "?page=main",
		"ACTION_RESULT" => Array(
			"SUCCESS" => Array("REDIRECT_URL" => "?page=usersettings"),
			"USER_DOES_NOT_EXIST" => Array("REDIRECT_URL" => "?page=usersettings"),
			"INVALID_PASSWORD_LENGTH" => Array("REDIRECT_URL" => "?page=usersettings"),
			"PASSWORDS_DONT_MATCH" => Array("REDIRECT_URL" => "?page=usersettings"),
			"INCORRECT_PASSWORD" => Array("REDIRECT_URL" => "?page=usersettings"),
			"NOT_LOGGED_IN" => Array("REDIRECT_URL" => "?page=login"),
		)
	),
	Array(
		"POST_REQUEST" => "saveuserchanges",
		"PHP_FILE" => "php/actions/saveuserchanges.php",
		"REDIRECT_AFTER_EXECUTION" => "?page=main",
		"ACTION_RESULT" => Array(
			"SUCCESS" => Array("REDIRECT_URL" => "?page=usersettings"),
			"INVALID_EMAIL" => Array("REDIRECT_URL" => "?page=usersettings"),
			"INVALID_DISPLAY_NAME" => Array("REDIRECT_URL" => "?page=usersettings"),
			"NOT_LOGGED_IN" => Array("REDIRECT_URL" => "?page=login"),
		)
	),
	Array(
		"POST_REQUEST" => "savenewtheme",
		"PHP_FILE" => "php/actions/savenewtheme.php",
		"REDIRECT_AFTER_EXECUTION" => "?page=main",
		"ACTION_RESULT" => Array(
			"SUCCESS" => Array("REDIRECT_URL" => "?page=themes"),
			"THEME_ALREADY_SUGGESTED" => Array("REDIRECT_URL" => "?page=themes"),
			"INVALID_THEME" => Array("REDIRECT_URL" => "?page=themes"),
			"NOT_LOGGED_IN" => Array("REDIRECT_URL" => "?page=login"),
		)
	),
	Array(
		"POST_REQUEST" => "deletetheme",
		"PHP_FILE" => "php/actions/deletetheme.php",
		"REDIRECT_AFTER_EXECUTION" => "?page=main",
		"ACTION_RESULT" => Array(
			"SUCCESS" => Array("REDIRECT_URL" => "?page=themes"),
			"INVALID_THEME" => Array("REDIRECT_URL" => "?page=themes"),
			"THEME_NOT_BANNED" => Array("REDIRECT_URL" => "?page=themes"),
			"NOT_AHTORIZED" => Array("REDIRECT_URL" => "?page=main"),
			"NOT_LOGGED_IN" => Array("REDIRECT_URL" => "?page=login"),
		)
	),
	Array(
		"POST_REQUEST" => "bantheme",
		"PHP_FILE" => "php/actions/bantheme.php",
		"REDIRECT_AFTER_EXECUTION" => "?page=main",
		"ACTION_RESULT" => Array(
			"SUCCESS" => Array("REDIRECT_URL" => "?page=themes"),
			"INVALID_THEME" => Array("REDIRECT_URL" => "?page=themes"),
			"THEME_NOT_BANNED" => Array("REDIRECT_URL" => "?page=themes"),
			"NOT_AHTORIZED" => Array("REDIRECT_URL" => "?page=main"),
			"NOT_LOGGED_IN" => Array("REDIRECT_URL" => "?page=login"),
		)
	),
	Array(
		"POST_REQUEST" => "unbantheme",
		"PHP_FILE" => "php/actions/unbantheme.php",
		"REDIRECT_AFTER_EXECUTION" => "?page=main",
		"ACTION_RESULT" => Array(
			"SUCCESS" => Array("REDIRECT_URL" => "?page=themes"),
			"INVALID_THEME" => Array("REDIRECT_URL" => "?page=themes"),
			"THEME_NOT_BANNED" => Array("REDIRECT_URL" => "?page=themes"),
			"NOT_AHTORIZED" => Array("REDIRECT_URL" => "?page=main"),
			"NOT_LOGGED_IN" => Array("REDIRECT_URL" => "?page=login"),
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
			"SUCESS_UPDATE" => Array("REDIRECT_URL" => "?page=editusers"),
			"SUCESS_INSERT" => Array("REDIRECT_URL" => "?page=editusers"),
			"INVALID_VOTE_TYPE" => Array("REDIRECT_URL" => "?page=editusers"),
			"NOT_AHTORIZED" => Array("REDIRECT_URL" => "?page=main"),
			"NOT_LOGGED_IN" => Array("REDIRECT_URL" => "?page=login"),
		)
	)
);

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
				$redirectURL = $action["ACTION_RESULT"][$actionResult]["REDIRECT_URL"];
				header("Location: ".$redirectURL);
				die("Redirecting to <a href='$actionRedirectAfterExecution'>$actionRedirectAfterExecution</a>...");
			}

			die("Unknown action result $actionResult for action $actionPostRequest. Please report this error to administrators.  <a href='?page=mail'>back to index</a>...");
		}
	}
}

//Special processing for specific pages!
switch($page){
	case "logout":
	break;
	case "edituser":
		if(IsAdmin()){
			$editingUsername = $_GET["username"];
			$editingUsername = trim(strtolower($editingUsername));
			if(!isset($users[$editingUsername])){
				die("no user selected");
			}
			$dictionary["editinguser"] = $users[$editingUsername];
			if($users[$editingUsername]["admin"] != 0){
				$dictionary["editinguser"]["isadmin"] = 1;
			}
		}
	break;
	case "editjam":
		if(IsAdmin()){
			$jamID = intval($_GET["jam_id"]);
			$jamFound = false;
			foreach($jams as $i => $jam){
				if(intval($jam["jam_id"]) == $jamID){
					$dictionary["editingjam"] = RenderJam($jam, 0, $config, $games, $users, $loggedInUser);
					$jamFound = true;
					break;
				}
			}
			if(!$jamFound){
				die("no jam selected");
			}
			$editingJamDate = date("Y-m-d", strtotime($dictionary["editingjam"]["date"]));
			$dictionary["editingjam"]["html_startdate"] = $editingJamDate;
		}
	break;
	case "editasset":
		if(IsAdmin()){
			$assetID = intval($_GET["asset_id"]);
			$dictionary["editingasset"] = ((isset($assets[$assetID])) ? $assets[$assetID] : Array());
		}
	break;
	case "editentry":
		if(IsAdmin()){
			$entryID = intval($_GET["entry_id"]);
			$dictionary["editingentry"] = Array();
			foreach($games as $i => $game){
				if($game["id"] == $entryID){
					$dictionary["editingentry"] = RenderGame($game, $jams, $users);
					break;
				}
			}
			if(count($dictionary["editingentry"]) == 0){
				die("no entry selected");
			}
		}
	break;
	case "jam":
		$viewingJamNumber = ((isset($_GET["jam"])) ? intval($_GET["jam"]) : 0);
		if($viewingJamNumber == 0){
			die("invalid jam number");
		}

		$pass = FALSE;
		foreach($jams as $i => $jam){
			if($jam["jam_number"] != $viewingJamNumber){
				continue;
			}

			if($jam["jam_deleted"] == 1){
				continue;
			}

			$dictionary["viewing_jam"] = RenderJam($jam, $nonDeletedJamCounter, $config, $games, $users, $loggedInUser);
			$pass = TRUE;
			break;
		}

		if($pass == FALSE){
			die("jam does not exist");
		}
	break;
	case "author":
		$viewingAuthor = ((isset($_GET["author"])) ? ("".$_GET["author"]) : "");
		if($viewingAuthor == ""){
			die("invalid author name");
		}

		$dictionary["viewing_author"] = RenderUser($users[$viewingAuthor], $users, $games, $jams, $config);
	break;
	case "submit":
		$jamNumber = (isset($_GET["jam_number"])) ? intval($_GET["jam_number"]) : $dictionary["jams"]["current_jam"]["jam_number"];
	break;
	case "userdata":
		$dictionary["userdata_assets"] = GetAssetsOfUserFormatted($loggedInUser["username"]);
		$dictionary["userdata_entries"] = GetEntriesOfUserFormatted($loggedInUser["username"]);
		$dictionary["userdata_poll_votes"] = GetPollVotesOfUserFormatted($loggedInUser["username"]);
		$dictionary["userdata_themes"] = GetThemesOfUserFormatted($loggedInUser["username"]);
		$dictionary["userdata_theme_votes"] = GetThemeVotesOfUserFormatted($loggedInUser["username"]);
		$dictionary["userdata_users"] = GetUsersOfUserFormatted($loggedInUser["username"]);
		$dictionary["userdata_jams"] = GetJamsOfUserFormatted($loggedInUser["username"]);
        $dictionary["userdata_satisfaction"] = GetSatisfactionVotesOfUserFormatted($loggedInUser["username"]);
        $dictionary["userdata_sessions"] = GetSessionsOfUserFormatted($loggedInUser["id"]);
        $dictionary["userdata_adminlog_admin"] = GetAdminLogForAdminFormatted($loggedInUser["username"]);
        $dictionary["userdata_adminlog_subject"] = GetAdminLogForSubjectFormatted($loggedInUser["username"]);
        $dictionary["userdata_admin_vote_voter"] = GetAdminVotesCastByUserFormatted($loggedInUser["username"]);
        $dictionary["userdata_admin_vote_subject"] = GetAdminVotesForSubjectUserFormatted($loggedInUser["username"]);
	break;
}

$dictionary["CURRENT_TIME"] = gmdate("d M Y H:i", time());

$dictionary["ANALYTICS"] = GetAnalyticsCode();

$dictionary["page_title"] = $pageTitles[$page];

if($page == "author")
{
	$dictionary["page_title"] = $viewingAuthor;
}
if($page == "jam")
{
	$dictionary["page_title"] = "Jam #" . $viewingJamNumber . ": ".$dictionary["viewing_jam"]["theme"];
}

//print_r($authors[0]);

?>

			<?php
				print $mustache->render(file_get_contents($templateBasePath."header.html"), $dictionary);
			?>
			<div class="row">
				<div class="col-md-2">
					<?php
						if(IsLoggedIn() === false){
							print $mustache->render(file_get_contents($templateBasePath."menu_guest.html"), $dictionary);
						}else if(IsAdmin()){
							print $mustache->render(file_get_contents($templateBasePath."menu_admin.html"), $dictionary);
						}else{
							print $mustache->render(file_get_contents($templateBasePath."menu_user.html"), $dictionary);
						}

						print $mustache->render(file_get_contents($templateBasePath."menu_shared.html"), $dictionary);
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
							$jam = GetJamByNumber($jams, $jamNumber);
							if (!$jam) {
								die('jam not found');
							}

							$dictionary["submit_jam"] = RenderSubmitJam($jam, $config, $games, $users, $loggedInUser);
							$colorNumber = rand(0, count($jam["colors"]) - 1);
							$dictionary["user_entry_color"] = $jam["colors"][$colorNumber];

							foreach($games as $i => $game){
								if($game["author"] != $loggedInUser["username"]){
									continue;
								}

								if($game["jam_number"] != $jamNumber){
									continue;
								}

								if($game["entry_deleted"] == 1){
									continue;
								}

								//Determine entry color number
								foreach($jam["colors"] as $colorIndex => $color){
									if($color == $game["color"]){
										$colorNumber = $colorIndex;
										break;
									}
								}

								$dictionary["user_entry_color_number"] = $colorNumber;
								$dictionary["user_entry_color"] = $jam["colors"][$colorNumber];

								$dictionary["user_submitted_to_this_jam"] = true;
								$dictionary["user_entry_name"] = $game["title"];
								if($game["screenshot_url"] != "logo.png"){
									$dictionary["user_entry_screenshot"] = $game["screenshot_url"];
								}
								$dictionary["user_entry_url"] = $game["url"];
								$dictionary["user_entry_url_web"] = $game["url_web"];
								$dictionary["user_entry_url_windows"] = $game["url_windows"];
								$dictionary["user_entry_url_mac"] = $game["url_mac"];
								$dictionary["user_entry_url_linux"] = $game["url_linux"];
								$dictionary["user_entry_url_ios"] = $game["url_ios"];
								$dictionary["user_entry_url_android"] = $game["url_android"];
								$dictionary["user_entry_url_source"] = $game["url_source"];
								$dictionary["user_entry_desc"] = $game["description"];
								//$dictionary["user_entry_color"] = $game["color"];
								//$dictionary["user_entry_color_number"] = $game["color_number"];

								if($game["url_web"] != ""){
									$dictionary["user_entry_share_url"] = $game["url_web"];
								}else if($game["url_windows"] != ""){
									$dictionary["user_entry_share_url"] = $game["url_windows"];
								}else if($game["url_mac"] != ""){
									$dictionary["user_entry_share_url"] = $game["url_mac"];
								}else if($game["url_linux"] != ""){
									$dictionary["user_entry_share_url"] = $game["url_linux"];
								}else if($game["url_ios"] != ""){
									$dictionary["user_entry_share_url"] = $game["url_ios"];
								}else if($game["url_android"] != ""){
									$dictionary["user_entry_share_url"] = $game["url_android"];
								}else if($game["url"] != ""){
									$dictionary["user_entry_share_url"] = $game["url"];
								}else if($game["url_source"] != ""){
									$dictionary["user_entry_share_url"] = $game["url_source"];
								}

								if(isset($game["has_url"])){$dictionary["user_has_url"] = 1;}
								if(isset($game["has_url_web"])){$dictionary["user_has_url_web"] = 1;}
								if(isset($game["has_url_windows"])){$dictionary["user_has_url_windows"] = 1;}
								if(isset($game["has_url_mac"])){$dictionary["user_has_url_mac"] = 1;}
								if(isset($game["has_url_linux"])){$dictionary["user_has_url_linux"] = 1;}
								if(isset($game["has_url_ios"])){$dictionary["user_has_url_ios"] = 1;}
								if(isset($game["has_url_android"])){$dictionary["user_has_url_android"] = 1;}
								if(isset($game["has_url_source"])){$dictionary["user_has_url_source"] = 1;}
								break;
							}

							if (!isset($dictionary["user_entry_name"]) && $jamNumber != $dictionary["jams"]["current_jam"]["jam_number"]) {
								die('Cannot make a new submission to a past jam');
							}

							print $mustache->render(file_get_contents($templateBasePath."submit.html"), $dictionary);
						break;
						case "newjam":
							if(IsAdmin()){
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
							if(IsAdmin()){
								print $mustache->render(file_get_contents($templateBasePath."config.html"), $dictionary);
							}
						break;
						case "editasset":
							if(IsAdmin()){
								print $mustache->render(file_get_contents($templateBasePath."editasset.html"), $dictionary);
							}
						break;
						case "editcontent":
							if(IsAdmin()){
								print $mustache->render(file_get_contents($templateBasePath."editcontent.html"), $dictionary);
							}
						break;
						case "editjam":
							if(IsAdmin()){
								print $mustache->render(file_get_contents($templateBasePath."editjam.html"), $dictionary);
							}
						break;
						case "editentry":
							if(IsAdmin()){
								print $mustache->render(file_get_contents($templateBasePath."editentry.html"), $dictionary);
							}
						break;
						case "editusers":
							if(IsAdmin()){
								print $mustache->render(file_get_contents($templateBasePath."editusers.html"), $dictionary);
							}
						break;
						case "edituser":
							if(IsAdmin()){
								print $mustache->render(file_get_contents($templateBasePath."edituser.html"), $dictionary);
							}
						break;
						case "themes":
							print $mustache->render(file_get_contents($templateBasePath."themes.html"), $dictionary);
						break;
						case "usersettings":
							$dictionary["user"] = LoadBio($dictionary["user"]);
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
							$dictionary['show_edit_link'] = $dictionary["viewing_author"]["id"] == $loggedInUser["id"];
							$dictionary["viewing_author"] = LoadBio($dictionary["viewing_author"]);
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

		<script src="bs/js/bootstrap.min.js"></script>
	</body>
</html>

<?php

StopTimer("index.php");


if(IsAdmin()){
	print_r($actionLog);
	print "<br><br>";
	print_r($actionTimers);
	print "<br><br>";
	print ArrayToHTML($actionTimers);
}

?>