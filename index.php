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

$page = "main";
if(isset($_GET["page"])){
	$page = strtolower(trim($_GET["page"]));
}

//List allowed page identifiers here.
if(!(in_array($page, Array("main", "login", "logout", "submit", "newjam", "assets", "editasset", "rules", "config", "editcontent", "editjam", "editentry", "editusers", "edituser", "themes", "usersettings", "entries", "jam", "jams", "author", "authors", "privacy", "userdata")))){
	$page = "main";
}

//Actions!
$action = "";
if(isset($_POST["action"])){
	$action = $_POST["action"];
	switch($action){
		case "login":
			$username = (isset($_POST["un"])) ? $_POST["un"] : "";
			$password = (isset($_POST["pw"])) ? $_POST["pw"] : "";
			$loginChecked = false;
			
			$username = strtolower(trim($username));
			$password = trim($password);
			LogInOrRegister($username, $password);
		break;
		case "submit":
			$gameName = (isset($_POST["gamename"])) ? $_POST["gamename"] : "";
			$gameURL = (isset($_POST["gameurl"])) ? $_POST["gameurl"] : "";
			$gameURLWeb = (isset($_POST["gameurlweb"])) ? $_POST["gameurlweb"] : "";
			$gameURLWin = (isset($_POST["gameurlwin"])) ? $_POST["gameurlwin"] : "";
			$gameURLMac = (isset($_POST["gameurlmac"])) ? $_POST["gameurlmac"] : "";
			$gameURLLinux = (isset($_POST["gameurllinux"])) ? $_POST["gameurllinux"] : "";
			$gameURLiOS = (isset($_POST["gameurlios"])) ? $_POST["gameurlios"] : "";
			$gameURLAndroid = (isset($_POST["gameurlandroid"])) ? $_POST["gameurlandroid"] : "";
			$gameURLSource = (isset($_POST["gameurlsource"])) ? $_POST["gameurlsource"] : "";
			$screenshotURL = (isset($_POST["screenshoturl"])) ? $_POST["screenshoturl"] : "";
			$description = (isset($_POST["description"])) ? $_POST["description"] : "";
			$jamNumber = (isset($_POST["jam_number"])) ? intval($_POST["jam_number"]) : -1;
			$jamColorNumber = (isset($_POST["colorNumber"])) ? intval($_POST["colorNumber"]) : 0;
			
			SubmitEntry($jamNumber, $gameName, $gameURL, $gameURLWeb, $gameURLWin, $gameURLMac, $gameURLLinux, $gameURLiOS, $gameURLAndroid, $gameURLSource, $screenshotURL, $description, $jamColorNumber);
			
			$satisfaction = (isset($_POST["satisfaction"])) ? intval($_POST["satisfaction"]) : 0;
			if($satisfaction != 0){
				SubmitSatisfaction("JAM_$jamNumber", $satisfaction);
			}
		break;
		case "newjam":
			$theme = (isset($_POST["theme"])) ? $_POST["theme"] : "";
			$date = (isset($_POST["date"])) ? $_POST["date"] : "";
			$time = (isset($_POST["time"])) ? $_POST["time"] : "";
			$jamColors = Array();
			for($colorIndex = 0; $colorIndex < 16; $colorIndex++){
				if(isset($_POST["jamcolor".$colorIndex])){
					$jamColors[] = $_POST["jamcolor".$colorIndex];
				}
			}
			if(count($jamColors) == 0){
				$jamColors = Array("FFFFFF");
			}
			
			CreateJam($theme, $date, $time, $jamColors);
		break;
		case "deletejam":
			$jamID = (isset($_POST["jamID"])) ? $_POST["jamID"] : "";
			if($jamID != ""){
				DeleteJam(intval($jamID));
				$page = "editcontent";
			}
		break;
		case "deleteentry":
			$entryID = (isset($_POST["entryID"])) ? $_POST["entryID"] : "";
			if($entryID != ""){
				DeleteEntry(intval($entryID));
				$page = "editcontent";
			}
		break;
		case "saveconfig":
			if(IsAdmin()){
				foreach($_POST as $key => $value){
					SaveConfig($key, $value);
				}
				LoadConfig(); //reload config
			}
		break;
		case "editentry":
			if(IsAdmin()){
				$jamNumber = intval($_POST["jamnumber"]);
				$author = strtolower(trim($_POST["entryauthor"]));
				$dictionary["editingentry"] = Array();
				foreach($jams as $i => $jam){
					if(intval($jam["jam_number"]) == $jamNumber){
						foreach($jam["entries"] as $j => $entry){
							if($entry["author"] == $author){
								$dictionary["editingentry"] = $entry;
								$dictionary["editingentry"]["jam_number"] = $jamNumber;
								break;
							}
						}
						break;
					}
				}
				if(count($dictionary["editingentry"]) == 0){
					die("no entry selected");
				}
			}
		break;
		case "editjam":
			if(IsAdmin()){
				$jamNumber = intval($_POST["jamnumber"]);
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
				$assetID = intval($_POST["asset_id"]);
				$dictionary["editingasset"] = ((isset($assets[$assetID])) ? $assets[$assetID] : Array());
				print_r($dictionary["editingasset"]);
			}
		break;
		case "saveassetedits":
			if(IsAdmin()){
				$assetID = $_POST["asset_id"];
				$author = $_POST["author"];
				$title = $_POST["title"];
				$description = $_POST["description"];
				$type = $_POST["type"];
				
				AddAsset($assetID, $author, $title, $description, $type);
			}
			$page = "assets";
		break;
		case "deleteasset":
			if(IsAdmin()){
				$assetID = $_POST["asset_id"];
				DeleteAsset($assetID);
			}
			$page = "assets";
		break;
		case "savejamedits":
			if(IsAdmin()){
				$jamNumber = intval($_POST["jam_number"]);
				$theme = $_POST["theme"];
				$date = $_POST["date"];
				$time = $_POST["time"];
				$jamcolors = $_POST["jamcolors"];
				
				EditJam($jamNumber, $theme, $date, $time, $jamcolors);
			}
			$page = "main";
		break;
		case "saveentryedits":
			if(IsAdmin()){
				$jamNumber = intval($_POST["jam_number"]);
				$author = $_POST["author"];
				$title = $_POST["title"];
				$url = $_POST["url"];
				$screenshot_url = $_POST["screenshot_url"];
				
				EditEntry($jamNumber, $author, $title, $url, $screenshot_url);
			}
			$page = "main";
		break;
		case "saveuseredits":
			if(IsAdmin()){
				$username = $_POST["username"];
				$isAdmin = (isset($_POST["isadmin"])) ? intval($_POST["isadmin"]) : 0;
				if($isAdmin != 0 && $isAdmin != 1){
					die("invalid isadmin value");
				}
				
				EditUser($username, $isAdmin);
			}
			$page = "editusers";
		break;
		case "savenewuserpassword":
			if(IsAdmin()){
				$username = $_POST["username"];
				$password1 = $_POST["password1"];
				$password2 = $_POST["password2"];
				
				EditUserPassword($username, $password1, $password2);
			}
			$page = "editusers";
		break;
		case "changepassword":
			if(IsLoggedIn()){
				$passwordold = $_POST["passwordold"];
				$password1 = $_POST["password1"];
				$password2 = $_POST["password2"];
				
				ChangePassword($passwordold, $password1, $password2);
			}
			$page = "usersettings";
		break;
		case "saveuserchanges":
			if(IsLoggedIn()){
				$displayName = $_POST["displayname"];
				$twitterHandle = $_POST["twitterhandle"];
				$emailAddress = $_POST["emailaddress"];
				$bio = $_POST["bio"];
				
				ChangeUserData($displayName, $twitterHandle, $emailAddress, $bio);
			}
			$page = "usersettings";
		break;
		case "savenewtheme":
			if(IsLoggedIn()){
				$newTheme = $_POST["theme"];
				AddTheme($newTheme, false);
			}
		break;
		case "deletetheme":
			if(IsAdmin()){
				$deletedTheme = $_POST["theme"];
				RemoveTheme($deletedTheme);
			}
		break;
		case "bantheme":
			if(IsAdmin()){
				$bannedTheme = $_POST["theme"];
				BanTheme($bannedTheme);
			}
		break;
		case "unbantheme":
			if(IsAdmin()){
				$unbannedTheme = $_POST["theme"];
				UnbanTheme($unbannedTheme);
			}
		break;
		case "downloaddb":
			if(IsAdmin()){
				print GetJSONDataForAllTables();
				die();
			}
		break;
	}
}

//Page actions!
switch($page){
	case "login":
		if(IsLoggedIn()){
			$page = "main";
		}
	break;
	case "logout":
		$loginChecked = false;
		LogOut();
		$page = "main";
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
	break;
}

$dictionary["CURRENT_TIME"] = gmdate("d M Y H:i", time());

$dictionary["ANALYTICS"] = GetAnalyticsCode();


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