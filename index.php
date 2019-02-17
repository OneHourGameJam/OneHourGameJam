<?php
//ini_set('display_errors',1);
//ini_set('display_startup_errors',1);
//error_reporting(-1);
//
//print phpversion ();

include_once("php/site.php");
StartTimer("index.php");

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

	//print "<pre>";
	//var_dump($dictionary);
	//print "</pre>";

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