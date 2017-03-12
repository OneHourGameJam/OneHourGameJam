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
		setcookie("streaming", 1, time() + (60 * 3));	//Streamer mode lasts for 3 hours
		$dictionary["is_streamer"] = 1;
	}else{
		setcookie("streaming", 1, 0);
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
if(!(in_array($page, Array("main", "login", "logout", "submit", "newjam", "assets", "rules", "config", "editcontent", "editjam", "editentry", "editusers", "edituser", "themes", "usersettings", "entries", "jam", "jams", "author", "authors")))){
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
			
			SubmitEntry($jamNumber, $gameName, $gameURL, $gameURLWeb, $gameURLWin, $gameURLMac, $gameURLLinux, $gameURLiOS, $gameURLAndroid, $gameURLSource, $screenshotURL, $description);
		break;
		case "newjam":
			$theme = (isset($_POST["theme"])) ? $_POST["theme"] : "";
			$date = (isset($_POST["date"])) ? $_POST["date"] : "";
			$time = (isset($_POST["time"])) ? $_POST["time"] : "";
			
			CreateJam($theme, $date, $time);
		break;
		case "deletejam":
			$jamID = (isset($_POST["jamID"])) ? $_POST["jamID"] : "";
			if($jamID != ""){
				DeleteJam(intval($jamID));
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
		case "savejamedits":
			if(IsAdmin()){
				$jamNumber = intval($_POST["jam_number"]);
				$theme = $_POST["theme"];
				$date = $_POST["date"];
				$time = $_POST["time"];
				
				EditJam($jamNumber, $theme, $date, $time);
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
}

$dictionary["CURRENT_TIME"] = gmdate("d M Y H:i", time());

//print_r($authors[0]);

?>

<!doctype html>
<html lang="en">
	<head>
		<meta charset='utf-8'>
		<script src="js/jquery.js"></script>
		<title>One hour game jam</title>
		<meta name="viewport" content="width=device-width, initial-scale=1">

		<link href="bs/css/bootstrap.min.css" rel="stylesheet">
		<link rel="stylesheet" href="/css/trumbowyg.css">
		<link href="css/site.css" rel="stylesheet">
		<link rel="shortcut icon" href="/favicon.ico" type="image/x-icon">
		<link rel="icon" href="/favicon.ico" type="image/x-icon">
		<script src='js/1hgj.js' type='text/javascript'></script>
		<script src='js/sorttable.js' type='text/javascript'></script>
		<?php PrintAnalyticsCode(); ?>
	</head>
	<body>
		<div class="container">
			<?php
				print $mustache->render(file_get_contents("template/header.html"), $dictionary);
			?>
			<div class="row">
				<div class="col-md-2">
					<?php
						if(IsLoggedIn() === false){
							print $mustache->render(file_get_contents("template/menu_guest.html"), $dictionary);
						}else if(IsAdmin()){
							print $mustache->render(file_get_contents("template/menu_admin.html"), $dictionary);
						}else{
							print $mustache->render(file_get_contents("template/menu_user.html"), $dictionary);
						}
						
						print $mustache->render(file_get_contents("template/menu_shared.html"), $dictionary);
					?>
				</div>
						
				<?php
					switch($page){
						case "main":
							print $mustache->render(file_get_contents("template/main.html"), $dictionary);
						break;
						case "login":
							print $mustache->render(file_get_contents("template/login.html"), $dictionary);
						break;
						case "submit":
							$jam = GetJamByNumber($jamNumber);
							if (!$jam) {
								die('jam not found');
							}

							$dictionary["submit_jam"] = $jam;

							foreach($jam["entries"] as $jam_entry){
								if($jam_entry["author"] == $loggedInUser["username"]){
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

							print $mustache->render(file_get_contents("template/submit.html"), $dictionary);
						break;
						case "newjam":
							if(IsAdmin()){
								print $mustache->render(file_get_contents("template/newjam.html"), $dictionary);
							}
						break;
						case "assets":
							print $mustache->render(file_get_contents("template/assets.html"), $dictionary);
						break;
						case "rules":
							print $mustache->render(file_get_contents("template/rules.html"), $dictionary);
						break;
						case "config":
							if(IsAdmin()){
								print $mustache->render(file_get_contents("template/config.html"), $dictionary);
							}
						break;
						case "editcontent":
							if(IsAdmin()){
								print $mustache->render(file_get_contents("template/editcontent.html"), $dictionary);
							}
						break;
						case "editjam":
							if(IsAdmin()){
								print $mustache->render(file_get_contents("template/editjam.html"), $dictionary);
							}
						break;
						case "editentry":
							if(IsAdmin()){
								print $mustache->render(file_get_contents("template/editentry.html"), $dictionary);
							}
						break;
						case "editusers":
							if(IsAdmin()){
								print $mustache->render(file_get_contents("template/editusers.html"), $dictionary);
							}
						break;
						case "edituser":
							if(IsAdmin()){
								print $mustache->render(file_get_contents("template/edituser.html"), $dictionary);
							}
						break;
						case "themes":
							print $mustache->render(file_get_contents("template/themes.html"), $dictionary);
						break;
						case "usersettings":
							$dictionary["user"] = LoadBio($dictionary["user"]);
							print $mustache->render(file_get_contents("template/usersettings.html"), $dictionary);
						break;
						case "entries":
							print $mustache->render(file_get_contents("template/entries.html"), $dictionary);
						break;
						case "jam":
							print $mustache->render(file_get_contents("template/jam.html"), $dictionary);
						break;
						case "jams":
							print $mustache->render(file_get_contents("template/jams.html"), $dictionary);
						break;
						case "author":
							$dictionary['show_edit_link'] = $dictionary["viewing_author"]["id"] == $loggedInUser["id"];
							$dictionary["viewing_author"] = LoadBio($dictionary["viewing_author"]);
							print $mustache->render(file_get_contents("template/author.html"), $dictionary);
						break;
						case "authors":
							print $mustache->render(file_get_contents("template/authors.html"), $dictionary);
						break;
					}
				?>
			</div>
			<?php
				print $mustache->render(file_get_contents("template/footer.html"), $dictionary);
			?>
		</div>
	
		<script src="bs/js/bootstrap.min.js"></script>
	</body>
</html>