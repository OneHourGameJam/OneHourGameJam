<?php
//ini_set('display_errors',1);
//ini_set('display_startup_errors',1);
//error_reporting(-1);

include("php/site.php");

$page = "main";
if(isset($_GET["page"])){
	$page = strtolower(trim($_GET["page"]));
}

//List allowed page identifiers here.
if(!(in_array($page, Array("main", "login", "logout", "submit", "newjam")))){
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


?>

<!doctype html>
<html lang="en">
	<head>
		<meta charset='utf-8'>
		<script src="js/jquery.js"></script>
			<?php
				$filesToParse = GetSortedJamFileList();
				$jams = Array();
				$firstJam = true;
				$jamFromStart = 1;
				foreach ($filesToParse as $fileLoc) {
					//Read data about the jam
					$data = json_decode(file_get_contents($fileLoc), true);
					$newData = Array();
					$newData["jam_number"] = htmlspecialchars($data["jam_number"], ENT_QUOTES);
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
						$newData["entries"][$i]["author"] = htmlspecialchars($entry["author"], ENT_QUOTES);
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
					
					//Insert into jams array
					$jams[$newData["jam_number"]] = $newData;
					$jamFromStart++;
				}
			?>

		<script type='text/javascript'>
			var jams = [];
			
			<?php
				//Transfer jam data to JavaScript
				foreach($jams as $i => $jam){
					$newDataJSON = json_encode($jam);
					print "jams.push($newDataJSON);\n";
				}
			?>
			
			$(document).ready(function(){
				jams.sort(function(j1, j2){
					return j1.jam_number - j2.jam_number;
				});
				
				//TODO: Do not load all jams at once - load when scrolling beyond end of page
				for(var i = jams.length - 1; i >= 0; i--){
					makeJam(jams[i]);
					for(var j = 0; j < jams[i].entries.length; j++){
						makeEntry(jams[i], jams[i].entries[j]);
					}
				}

			});
			
		</script>
		<title>One hour game jam</title>
		<meta name="viewport" content="width=device-width, initial-scale=1">

		<link href="bs/css/bootstrap.min.css" rel="stylesheet">
		<link href="css/site.css" rel="stylesheet">
		<script src='js/1hgj.js' type='text/javascript'></script>
	</head>
	<body>
		<div class="container">
			<?php
				include("template/header.html");
			?>
			<div class="row">
				<div class="col-md-2">
					<?php
						if(IsLoggedIn() === false){
							include("template/menu_guest.html");
						}else if(IsAdmin()){
							include("template/menu_admin.html");
						}else{
							include("template/menu_user.html");
						}
						
						include("template/menu_shared.html");
					?>
				</div>
						
					<?php
						switch($page){
							case "main":
								include("template/main.html");
							break;
							case "login":
								include("template/login.html");
							break;
							case "submit":
								include("template/submit.html");
							break;
							case "newjam":
								include("template/newjam.html");
							break;
						}
					?>
			</div>
		</div>
	
		<script src="bs/js/bootstrap.min.js"></script>
	</body>
</html>
