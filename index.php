<?php
//ini_set('display_errors',1);
//ini_set('display_startup_errors',1);
//error_reporting(-1);

include_once("php/site.php");

$templateBasePath = "template/";
$dictionary["template_path"] = $templateBasePath;

$page = "main";
if(isset($_GET["page"])){
	$page = strtolower(trim($_GET["page"]));
}

//List allowed page identifiers here.
if(!(in_array($page, Array("main", "login", "logout", "submit", "newjam", "assets", "rules", "config", "editcontent", "editjam", "editentry", "editusers", "edituser", "themes", "usersettings")))){
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
			$screenshotURL = (isset($_POST["screenshoturl"])) ? $_POST["screenshoturl"] : "";
			$description = (isset($_POST["description"])) ? $_POST["description"] : "";
			
			SubmitEntry($gameName, $gameURL, $screenshotURL, $description);
		break;
		case "newjam":
			$theme = (isset($_POST["theme"])) ? $_POST["theme"] : "";
			$date = (isset($_POST["date"])) ? $_POST["date"] : "";
			$time = (isset($_POST["time"])) ? $_POST["time"] : "";
			
			CreateJam($theme, $date, $time, $description);
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
			if(IsAdmin()){
				$passwordold = $_POST["passwordold"];
				$password1 = $_POST["password1"];
				$password2 = $_POST["password2"];
				
				ChangePassword($passwordold, $password1, $password2);
			}
			$page = "usersettings";
		break;
		case "saveuserchanges":
			if(IsAdmin()){
				$displayName = $_POST["displayname"];
				$twitterHandle = $_POST["twitterhandle"];
				
				ChangeUserData($displayName, $twitterHandle);
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
}

$dictionary["CURRENT_TIME"] = gmdate("d M Y H:i", time());

?>

<!doctype html>
<html lang="en">
	<head>
		<meta charset='utf-8'>
		<script src="js/jquery.js"></script>
		<title>One hour game jam</title>
		<meta name="viewport" content="width=device-width, initial-scale=1">

		<link href="bs/css/bootstrap.min.css" rel="stylesheet">
		<link href="css/site.css" rel="stylesheet">
		<link rel="shortcut icon" href="/favicon.ico" type="image/x-icon">
		<link rel="icon" href="/favicon.ico" type="image/x-icon">
		<script src='js/1hgj.js' type='text/javascript'></script>
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
							foreach($dictionary["current_jam"]["entries"] as $current_jam_entry){
								if($current_jam_entry["author"] == $loggedInUser["username"]){
									$dictionary["user_entry_name"] = $current_jam_entry["title"];
									if($current_jam_entry["screenshot_url"] != "logo.png"){
										$dictionary["user_entry_screenshot"] = $current_jam_entry["screenshot_url"];
									}
									$dictionary["user_entry_url"] = $current_jam_entry["url"];
									$dictionary["user_entry_desc"] = $current_jam_entry["description"];
									break;
								}
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
							print $mustache->render(file_get_contents("template/usersettings.html"), $dictionary);
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