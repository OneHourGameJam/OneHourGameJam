<?php
//ini_set('display_errors',1);
//ini_set('display_startup_errors',1);
//error_reporting(-1);
//
//print phpversion ();

include_once("php/site.php");
StartTimer("index.php");

$pageTemplateFile = $templateBasePath.$pageSettings[$page]["template_file"];
$mustache->setPartials(Array(
	"header" => file_get_contents($templateBasePath."header.html"),
	"message" => file_get_contents($templateBasePath."message.html"),
	"menu" => file_get_contents($templateBasePath."menu.html"),
	"footer" => file_get_contents($templateBasePath."footer.html"),
	"poll" => file_get_contents($templateBasePath."poll.html"),
	"notification" => file_get_contents($templateBasePath."notification.html"),
	"cookie_notice" => file_get_contents($templateBasePath."cookienotice.html"),
	"page" => file_get_contents($pageTemplateFile),
	"csrf_token" => file_get_contents($templateBasePath."csrf-token.html")
));

print $mustache->render(file_get_contents($templateBasePath."index.html"), $dictionary);
?>

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