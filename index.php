<?php

$softwareVersion = "1.1.1";

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
$partials["header"] = file_get_contents($templateBasePath."header.html");
$partials["message"] = file_get_contents($templateBasePath."message.html");
$partials["menu"] = file_get_contents($templateBasePath."menu.html");
$partials["footer"] = file_get_contents($templateBasePath."footer.html");
$partials["poll"] = file_get_contents($templateBasePath."poll.html");
$partials["cookie_notice"] = file_get_contents($templateBasePath."cookienotice.html");
$partials["page"] = file_get_contents($pageTemplateFile);
$partials["csrf_token"] = file_get_contents($templateBasePath."csrf-token.html");
$partials["jam_header"] = file_get_contents($templateBasePath."jam_header.html");
$partials["entry_by_user"] = file_get_contents($templateBasePath."entry_by_user.html");
$partials["entry_for_jam"] = file_get_contents($templateBasePath."entry_for_jam.html");
$partials["user_header"] = file_get_contents($templateBasePath."user_header.html");
$partials["satisfaction"] = file_get_contents($templateBasePath."satisfaction.html");
$partials["css"] = file_get_contents($templateBasePath."css/site.css");

$mustache->setPartials($partials);

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