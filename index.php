<?php
//ini_set('display_errors',1);
//ini_set('display_startup_errors',1);
//error_reporting(-1);
//
//print phpversion ();

//die("Website update in progress. Come back in a minute or two. Thanks <3");

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
	"csrf_token" => file_get_contents($templateBasePath."csrf-token.html"),
	"jam_header" => file_get_contents($templateBasePath."jam_header.html"),
	"entry_by_user" => file_get_contents($templateBasePath."entry_by_user.html"),
	"entry_for_jam" => file_get_contents($templateBasePath."entry_for_jam.html"),
	"user_header" => file_get_contents($templateBasePath."user_header.html"),
	"satisfaction" => file_get_contents($templateBasePath."satisfaction.html"),
	"css" => file_get_contents($templateBasePath."css/site.css")
));

$dictionary["plugin_display"]= 1;

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
	
	//print ArrayToHTML($actionTimers);
}

?>