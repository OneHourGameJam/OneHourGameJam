<?php
//ini_set('display_errors',1);
//ini_set('display_startup_errors',1);
//error_reporting(-1);

include_once("php/site.php");

$page = "main";
if(isset($_GET["page"])){
	$page = strtolower(trim($_GET["page"]));
}

//List allowed page identifiers here.
if(!(in_array($page, Array("main", "login", "logout", "submit", "newjam", "assets", "rules")))){
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
			
			$username = strtolower(trim($username));
			$password = trim($password);
			LogInOrRegister($username, $password);
		break;
		case "submit":
			$gameName = (isset($_POST["gamename"])) ? $_POST["gamename"] : "";
			$gameURL = (isset($_POST["gameurl"])) ? $_POST["gameurl"] : "";
			$screenshotURL = (isset($_POST["screenshoturl"])) ? $_POST["screenshoturl"] : "";
			
			SubmitEntry($gameName, $gameURL, $screenshotURL);
		break;
		case "newjam":
			$theme = (isset($_POST["theme"])) ? $_POST["theme"] : "";
			$date = (isset($_POST["date"])) ? $_POST["date"] : "";
			$time = (isset($_POST["time"])) ? $_POST["time"] : "";
			
			CreateJam($theme, $date, $time);
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
		LogOut();
		$page = "main";
	break;
}


//Create lists of jams and jam entries
$filesToParse = GetSortedJamFileList();
$authors = Array();
$firstJam = true;
$jamFromStart = 1;
foreach ($filesToParse as $fileLoc) {
	//Read data about the jam
	$data = json_decode(file_get_contents($fileLoc), true);
	$newData = Array();
	$newData["jam_number"] = htmlspecialchars($data["jam_number"], ENT_QUOTES);
	$newData["jam_number_ordinal"] = htmlspecialchars(ordinal($data["jam_number"]), ENT_QUOTES);
	$newData["theme"] = htmlspecialchars($data["theme"], ENT_QUOTES);
	$newData["date"] = htmlspecialchars($data["date"], ENT_QUOTES);
	$newData["time"] = htmlspecialchars($data["time"], ENT_QUOTES);
	$newData["entries"] = Array();
	$newData["first_jam"] = $firstJam;
	$newData["entries_visible"] = $jamFromStart <= 2;
	if($firstJam){
		$firstJam = false;
	}
	
	foreach ($data["entries"] as $i => $entry){
		$newData["entries"][$i]["title"] = htmlspecialchars($entry["title"], ENT_QUOTES);
		$author = htmlspecialchars($entry["author"], ENT_QUOTES);
		
		if(isset($authors[$author])){
			$entryCount = $authors[$author]["entry_count"];
			$entryCount = $entryCount + 1;
			$authors[$author]["entry_count"] = $entryCount;
		}else{
			$authors[$author] = Array("entry_count" => 1, "username" => $author);
		}
		
		$newData["entries"][$i]["author"] = $author;
		$newData["entries"][$i]["url"] = str_replace("'", "\\'", $entry["url"]);
		$newData["entries"][$i]["screenshot_url"] = str_replace("'", "\\'", $entry["screenshot_url"]);
	}
	
	//Hide theme of not-yet-started jams
	
	$now = new DateTime();
	$datetime = new DateTime($data["start_time"]);
	$timeUntilJam = date_diff($datetime, $now);
	
	if($datetime > $now){
		$newData["theme"] = "Not yet announced";
		$newData["jam_started"] = false;
		if($timeUntilJam->days > 0){
			$newData["time_left"] = $timeUntilJam->format("%a days %H:%I:%S");
		}else if($timeUntilJam->h > 0){
			$newData["time_left"] = $timeUntilJam->format("%H:%I:%S");
		}else  if($timeUntilJam->i > 0){
			$newData["time_left"] = $timeUntilJam->format("%I:%S");
		}else if($timeUntilJam->s > 0){
			$newData["time_left"] = $timeUntilJam->format("%S seconds");
		}else{
			$newData["time_left"] = "Now!";
		}
	}else{
		$newData["jam_started"] = true;
	}
	
	//Insert into dictionary array
	$dictionary["jams"][] = $newData;
	$jamFromStart++;
}

//Insert authors into dictionary
foreach($authors as $k => $authorData){
	$dictionary["authors"][] = $authorData;
}

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
		<script src='js/1hgj.js' type='text/javascript'></script>
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
							print $mustache->render(file_get_contents("template/submit.html"), $dictionary);
						break;
						case "newjam":
							print $mustache->render(file_get_contents("template/newjam.html"), $dictionary);
						break;
						case "assets":
							print $mustache->render(file_get_contents("template/assets.html"), $dictionary);
						break;
						case "rules":
							print $mustache->render(file_get_contents("template/rules.html"), $dictionary);
						break;
					}
				?>
			</div>
		</div>
	
		<script src="bs/js/bootstrap.min.js"></script>
	</body>
</html>