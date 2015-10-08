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
			
			CreateJam($theme, $date);
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
		<script type='text/javascript'>
			//SETUP!
			var NEXT_EVENT = "10-10-2015 20:00 UTC";

			$(document).ready(function(){
				<?php
					$filesToParse = GetSortedJamFileList();
					
					foreach ($filesToParse as $fileLoc) {
						$data = json_decode(file_get_contents($fileLoc), true);
						//print_r($data);
						$jamNumber = htmlspecialchars($data["jam_number"], ENT_QUOTES);
						$theme = htmlspecialchars($data["theme"], ENT_QUOTES);
						$date = htmlspecialchars($data["date"], ENT_QUOTES);
						$entries = $data["entries"];
						
						//Create the jam listing
						print "makeJam($jamNumber, '$date', '$theme');";
						
						foreach($entries as $i => $entry){
							$title = htmlspecialchars($entry["title"], ENT_QUOTES);
							$author = htmlspecialchars($entry["author"], ENT_QUOTES);
							$url = $entry["url"];
							$screenshot = $entry["screenshot_url"];
							
							//Create the entry listing
							print "makeEntry($jamNumber, '$author', '$title', '$screenshot', '$url');";
						}
					}
				?>
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
