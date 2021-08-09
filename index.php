<?php

$softwareVersion = "1.2.0-SNAPSHOT-4";

//ini_set('display_errors',1);
//ini_set('display_startup_errors',1);
//error_reporting(-1);
//
//print phpversion ();

//die("Website update in progress. Come back in a minute or two. Thanks <3");

$templateBasePath = "template/";
include_once("php/site.php");
StartTimer("index.php");

$dictionary["SOFTWARE_VERSION"] = $softwareVersion;

$pageTemplateFile = $pageSettings[$page]["template_file"];
$partials["header"] = file_get_contents($templateBasePath."header.mustache");
$partials["jam_stats"] = file_get_contents($templateBasePath."jam_stats.mustache");
$partials["message"] = file_get_contents($templateBasePath."message.mustache");
$partials["menu"] = file_get_contents($templateBasePath."menu.mustache");
$partials["footer"] = file_get_contents($templateBasePath."footer.mustache");
$partials["poll"] = file_get_contents($templateBasePath."poll.mustache");
$partials["cookie_notice"] = file_get_contents($templateBasePath."cookienotice.mustache");
$partials["page"] = file_get_contents($pageTemplateFile);
$partials["csrf_token"] = file_get_contents($templateBasePath."csrf-token.mustache");
$partials["jam_header"] = file_get_contents($templateBasePath."jam_header.mustache");
$partials["entry_by_user"] = file_get_contents($templateBasePath."entry_by_user.mustache");
$partials["entry_for_jam"] = file_get_contents($templateBasePath."entry_for_jam.mustache");
$partials["user_header"] = file_get_contents($templateBasePath."user_header.mustache");
$partials["satisfaction"] = file_get_contents($templateBasePath."satisfaction.mustache");
$partials["css"] = file_get_contents($templateBasePath."css/site.css");

$mustache->setPartials($partials);

print $mustache->render(file_get_contents($templateBasePath."index.mustache"), $dictionary);
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