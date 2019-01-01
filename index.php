<?php
//ini_set('display_errors',1);
//ini_set('display_startup_errors',1);
//error_reporting(-1);
//
//print phpversion ();

include_once("php/site.php");

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
if($nightmode == 1){
	$dictionary["NIGHT_MODE"] = 1;
}

$actions = Array(
	Array(
		"POST_REQUEST" => "login",
		"PHP_FILE" => "php/actions/login.php",
		"REDIRECT_AFTER_EXECUTION" => "?page=main"
	),
	Array(
		"POST_REQUEST" => "logout",
		"PHP_FILE" => "php/actions/logout.php",
		"REDIRECT_AFTER_EXECUTION" => "?page=main"
	),
	Array(
		"POST_REQUEST" => "submit",
		"PHP_FILE" => "php/actions/submit.php",
		"REDIRECT_AFTER_EXECUTION" => "?page=main"
	),
	Array(
		"POST_REQUEST" => "newjam",
		"PHP_FILE" => "php/actions/newjam.php",
		"REDIRECT_AFTER_EXECUTION" => "?page=main"
	),
	Array(
		"POST_REQUEST" => "deletejam",
		"PHP_FILE" => "php/actions/deletejam.php",
		"REDIRECT_AFTER_EXECUTION" => "?page=main"
	),
	Array(
		"POST_REQUEST" => "deleteentry",
		"PHP_FILE" => "php/actions/deleteentry.php",
		"REDIRECT_AFTER_EXECUTION" => "?page=main"
	),
	Array(
		"POST_REQUEST" => "saveconfig",
		"PHP_FILE" => "php/actions/saveconfig.php",
		"REDIRECT_AFTER_EXECUTION" => "?page=main"
	),
	Array(
		"POST_REQUEST" => "editjam",
		"PHP_FILE" => "php/actions/editjam.php",
		"REDIRECT_AFTER_EXECUTION" => "?page=main"
	),
	Array(
		"POST_REQUEST" => "saveassetedits",
		"PHP_FILE" => "php/actions/saveassetedits.php",
		"REDIRECT_AFTER_EXECUTION" => "?page=main"
	),
	Array(
		"POST_REQUEST" => "deleteasset",
		"PHP_FILE" => "php/actions/deleteasset.php",
		"REDIRECT_AFTER_EXECUTION" => "?page=main"
	),
	Array(
		"POST_REQUEST" => "savejamedits",
		"PHP_FILE" => "php/actions/savejamedits.php",
		"REDIRECT_AFTER_EXECUTION" => "?page=main"
	),
	Array(
		"POST_REQUEST" => "saveuseredits",
		"PHP_FILE" => "php/actions/saveuseredits.php",
		"REDIRECT_AFTER_EXECUTION" => "?page=main"
	),
	Array(
		"POST_REQUEST" => "savenewuserpassword",
		"PHP_FILE" => "php/actions/savenewuserpassword.php",
		"REDIRECT_AFTER_EXECUTION" => "?page=main"
	),
	Array(
		"POST_REQUEST" => "changepassword",
		"PHP_FILE" => "php/actions/changepassword.php",
		"REDIRECT_AFTER_EXECUTION" => "?page=main"
	),
	Array(
		"POST_REQUEST" => "saveuserchanges",
		"PHP_FILE" => "php/actions/saveuserchanges.php",
		"REDIRECT_AFTER_EXECUTION" => "?page=main"
	),
	Array(
		"POST_REQUEST" => "savenewtheme",
		"PHP_FILE" => "php/actions/savenewtheme.php",
		"REDIRECT_AFTER_EXECUTION" => "?page=main"
	),
	Array(
		"POST_REQUEST" => "deletetheme",
		"PHP_FILE" => "php/actions/deletetheme.php",
		"REDIRECT_AFTER_EXECUTION" => "?page=main"
	),
	Array(
		"POST_REQUEST" => "bantheme",
		"PHP_FILE" => "php/actions/bantheme.php",
		"REDIRECT_AFTER_EXECUTION" => "?page=main"
	),
	Array(
		"POST_REQUEST" => "unbantheme",
		"PHP_FILE" => "php/actions/unbantheme.php",
		"REDIRECT_AFTER_EXECUTION" => "?page=main"
	),
	Array(
		"POST_REQUEST" => "downloaddb",
		"PHP_FILE" => "php/actions/downloaddb.php",
		"REDIRECT_AFTER_EXECUTION" => "?page=main"
	),
	Array(
		"POST_REQUEST" => "adminvote",
		"PHP_FILE" => "php/actions/adminvote.php",
		"REDIRECT_AFTER_EXECUTION" => "?page=main"
	)
);

//Actions!
if(isset($_POST["action"])){
	foreach($actions as $i => $action){
		$actionPostRequest = $action["POST_REQUEST"];
		$actionPhpFile = $action["PHP_FILE"];
		$actionRedirectAfterExecution = $action["REDIRECT_AFTER_EXECUTION"];

		if($_POST["action"] == $actionPostRequest){
			include_once($actionPhpFile);

			header("Location: $actionRedirectAfterExecution");
			die("Redirecting to <a href='$actionRedirectAfterExecution'>$actionRedirectAfterExecution</a>...");
		}
	}
}

//Page actions!
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
			$jamNumber = intval($_GET["jamnumber"]);
			$dictionary["editingjam"] = Array();
			foreach($jams as $i => $jam){
				if(intval($jam["jam_number"]) == $jamNumber){
					$dictionary["editingjam"] = $jam;
					break;
				}
			}
			if(count($dictionary["editingjam"]) == 0){
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
			foreach($jams as $i => $jam){
				foreach($jam["entries_with_deleted"] as $j => $entry){
					if($entry["entry_id"] == $entryID){
						$dictionary["editingentry"] = $entry;
						$dictionary["editingentry"]["jam_number"] = $jamNumber;
						break;
					}
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
		foreach($dictionary["jams"] as $i => $jam){
			if($jam["jam_number"] == $viewingJamNumber){
				$dictionary["viewing_jam"] = $jam;
				$pass = TRUE;
				break;
			}
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
		
		if (isset($authors[$viewingAuthor])) {
			$dictionary["viewing_author"] = $authors[$viewingAuthor];
		} else if (isset($users[$viewingAuthor])) {
			// User without entries
			$dictionary["viewing_author"] = $users[$viewingAuthor];
		} else {
			die("Author does not exist");
		}
	break;
	case "submit":
		$jamNumber = (isset($_GET["jam_number"])) ? intval($_GET["jam_number"]) : $dictionary["current_jam"]["jam_number"];
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
							$jam = GetJamByNumber($jamNumber);
							if (!$jam) {
								die('jam not found');
							}

							$dictionary["submit_jam"] = $jam;
							$dictionary["user_entry_color_number"] = rand(0, count($jam["colors"]) - 1);
							$dictionary["user_entry_color"] = $jam["colors"][$dictionary["user_entry_color_number"]]["color"];

							foreach($jam["entries"] as $jam_entry){
								if($jam_entry["author"] == $loggedInUser["username"]){
									$dictionary["user_submitted_to_this_jam"] = true;
									$dictionary["user_entry_name"] = $jam_entry["title"];
									if($jam_entry["screenshot_url"] != "logo.png"){
										$dictionary["user_entry_screenshot"] = $jam_entry["screenshot_url"];
									}
									$dictionary["user_entry_url"] = $jam_entry["url"];
									$dictionary["user_entry_url_web"] = $jam_entry["url_web"];
									$dictionary["user_entry_url_windows"] = $jam_entry["url_windows"];
									$dictionary["user_entry_url_mac"] = $jam_entry["url_mac"];
									$dictionary["user_entry_url_linux"] = $jam_entry["url_linux"];
									$dictionary["user_entry_url_ios"] = $jam_entry["url_ios"];
									$dictionary["user_entry_url_android"] = $jam_entry["url_android"];
									$dictionary["user_entry_url_source"] = $jam_entry["url_source"];
									$dictionary["user_entry_desc"] = $jam_entry["description"];
									$dictionary["user_entry_color"] = $jam_entry["color"];
									$dictionary["user_entry_color_number"] = $jam_entry["color_number"];
									
									if($jam_entry["url_web"] != ""){
										$dictionary["user_entry_share_url"] = $jam_entry["url_web"];
									}else if($jam_entry["url_windows"] != ""){
										$dictionary["user_entry_share_url"] = $jam_entry["url_windows"];
									}else if($jam_entry["url_mac"] != ""){
										$dictionary["user_entry_share_url"] = $jam_entry["url_mac"];
									}else if($jam_entry["url_linux"] != ""){
										$dictionary["user_entry_share_url"] = $jam_entry["url_linux"];
									}else if($jam_entry["url_ios"] != ""){
										$dictionary["user_entry_share_url"] = $jam_entry["url_ios"];
									}else if($jam_entry["url_android"] != ""){
										$dictionary["user_entry_share_url"] = $jam_entry["url_android"];
									}else if($jam_entry["url"] != ""){
										$dictionary["user_entry_share_url"] = $jam_entry["url"];
									}else if($jam_entry["url_source"] != ""){
										$dictionary["user_entry_share_url"] = $jam_entry["url_source"];
									}
									
									if(isset($jam_entry["has_url"])){$dictionary["user_has_url"] = 1;}
									if(isset($jam_entry["has_url_web"])){$dictionary["user_has_url_web"] = 1;}
									if(isset($jam_entry["has_url_windows"])){$dictionary["user_has_url_windows"] = 1;}
									if(isset($jam_entry["has_url_mac"])){$dictionary["user_has_url_mac"] = 1;}
									if(isset($jam_entry["has_url_linux"])){$dictionary["user_has_url_linux"] = 1;}
									if(isset($jam_entry["has_url_ios"])){$dictionary["user_has_url_ios"] = 1;}
									if(isset($jam_entry["has_url_android"])){$dictionary["user_has_url_android"] = 1;}
									if(isset($jam_entry["has_url_source"])){$dictionary["user_has_url_source"] = 1;}
									break;
								}
							}

							if (!isset($dictionary["user_entry_name"]) && $jamNumber != $dictionary["current_jam"]["jam_number"]) {
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