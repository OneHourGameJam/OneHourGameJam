<?php

$actionTimers = Array();
$actionLog = Array();

function AddActionLog($logEntry){
    global $actionLog;

	if(!isset($actionLog[$logEntry])){
		$actionLog[$logEntry] = 0;
	}
	$actionLog[$logEntry] += 1;
}

function StartTimer($timerName){
	global $actionTimers;

	if(!isset($actionTimers[$timerName])){
		$actionTimers[$timerName] = Array("totalTime" => 0, "timerRunning" => false, "lastTimestamp" => 0, "timeInSeconds" => "", "calls" => "-");
	}

	if($actionTimers[$timerName]["timerRunning"]){
		die("timer $timerName already running");
	}

	$actionTimers[$timerName]["timerRunning"] = true;
	$actionTimers[$timerName]["lastTimestamp"] = microtime(true);
}

function StopTimer($timerName){
	global $actionTimers;

	if(!isset($actionTimers[$timerName])){
		die("timer $timerName does not exist");
	}

	if($actionTimers[$timerName]["timerRunning"] == false){
		die("timer $timerName not running");
	}

	$actionTimers[$timerName]["timerRunning"] = false;
    $actionTimers[$timerName]["totalTime"] += microtime(true) - $actionTimers[$timerName]["lastTimestamp"];
    $actionTimers[$timerName]["timeInSeconds"] = number_format($actionTimers[$timerName]["totalTime"], 8);
}

function StartsWith($haystack, $needle) {
	AddActionLog("StartsWith");

    // search backwards starting from haystack length characters from the end
    return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== FALSE;
}

function EndsWith($haystack, $needle) {
	AddActionLog("EndsWith");

    // search forward starting from end minus needle length characters
    return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== FALSE);
}

//Returns ordinal version of provided number, so 1 -> 1st; 3 -> 3rd, etc.
function ordinal($number) {
	AddActionLog("ordinal");
	StartTimer("ordinal");

	$number = intval($number);
    $ends = array('th','st','nd','rd','th','th','th','th','th','th');
    if ((($number % 100) >= 11) && (($number%100) <= 13)){
		StopTimer("ordinal");
		return $number. 'th';
	}
    else{
		StopTimer("ordinal");
		return $number. $ends[$number % 10];
	}
	StopTimer("ordinal");
}
// Converts bytes to a string with a more accurate unit. Returns "5 kilobytes" instead of 5120 bytes, for example.
// Used in the mustache helper for printing bytes
function bytesToString($bytes) {
	AddActionLog("bytesToString");
	
	$byteConversions = Array( 
		"Terabyte" => 1099511627776,
		"Gigabyte" => 1073741824,
		"Megabyte" => 1048576,
		"Kilobyte" => 1024,
		"Byte" => 1
    );
	foreach ($byteConversions as $name => $ratio) {
		if ($bytes >= $ratio){
      		$amount = $bytes / $ratio;
		  	$plural = $amount > 1 ? "s" : "";
			return number_format($amount, 2) . " " . $name . $plural;
		}
	}
	return "less than 1 Byte. This is a bug.";
}

function GetSuggestedNextJamDateTime(&$configData){
	AddActionLog("GetSuggestedNextJamDateTime");
	StartTimer("GetSuggestedNextJamDateTime");

    $jamDay = "monday";
    switch($configData->ConfigModels["JAM_DAY"]->Value){
        case 0:
            $jamDay = "sunday";
        break;
        case 1:
            $jamDay = "monday";
        break;
        case 2:
            $jamDay = "tuesday";
        break;
        case 3:
            $jamDay = "wednesday";
        break;
        case 4:
            $jamDay = "thursday";
        break;
        case 5:
            $jamDay = "friday";
        break;
        case 6:
            $jamDay = "saturday";
        break;
    }

	$nextSuggestedJamTime = strtotime("$jamDay +" . intval($configData->ConfigModels["JAM_TIME"]->Value) . " hours UTC");

	if($nextSuggestedJamTime > strtotime("+7 DAYS")){
		$nextSuggestedJamTime -= 7 * 24 * 60 * 60;
	}

	StopTimer("GetSuggestedNextJamDateTime");
	return $nextSuggestedJamTime;
}

$currentJamNumberArchive = FALSE;
function GetCurrentJamNumberAndId(){
	global $currentJamNumberArchive, $jamDbInterface;
	AddActionLog("GetCurrentJamNumberAndId");
	StartTimer("GetCurrentJamNumberAndId");

	if($currentJamNumberArchive !== FALSE){
		StopTimer("GetCurrentJamNumberAndId");
		return $currentJamNumberArchive;
	}

	$data = $jamDbInterface->SelectCurrentJamNumberAndId();

	if($info = mysqli_fetch_array($data)){
		$currentJamNumberArchive = Array("NUMBER" => intval($info["jam_jam_number"]), "ID" => intval($info["jam_id"]));
	}else{
		$currentJamNumberArchive = Array("NUMBER" => 0, "ID" => 0);
	}

	StopTimer("GetCurrentJamNumberAndId");
	return $currentJamNumberArchive;
}

// Uses a whitelist of tags and attributes, plus parses the HTML to ensure
// the markup is well-formed and limited to non-harming code.
function CleanHtml($html) {
	// Remove tags
	AddActionLog("CleanHtml");
	StartTimer("CleanHtml");

	$halfCleanedHtml = strip_tags($html, '<p><strong><em><strike><sup><sub><a><ul><li>');

	// Parse non-empty HTML only
	if (!empty(trim($html))) {
		$dom = new DOMDocument('1.0', 'utf-8');
		$dom->loadHTML($halfCleanedHtml);

		// Only keep whitelisted HTML attributes
		$allowed_attributes = array('href');
		foreach ($dom->getElementsByTagName('*') as $node) {
		    for ($i = $node->attributes->length - 1; $i >= 0; $i--) {
		        $attribute = $node->attributes->item($i);
		        if (!in_array($attribute->name, $allowed_attributes)) {
		        	$node->removeAttributeNode($attribute);
		        }
		    }
		}

		// Stringify the DOMDocument <body> contents
		$cleanedHtml = utf8_decode($dom->saveXML($dom->getElementsByTagName('body')->item(0)));
		StopTimer("CleanHtml");
		return $cleanedHtml;
	} else {
		StopTimer("CleanHtml");
		return NULL;
	}
	
	StopTimer("CleanHtml");
}

// Converts a MySQL data object into an Array (Skips numeric keys aka $data[0], $data[1], etc.)
function MySQLDataToArray($data){
	AddActionLog("MySQLDataToArray");
	StartTimer("MySQLDataToArray");

	$result = Array();
	while($asset = mysqli_fetch_array($data)){
		$row = Array();
		foreach($asset as $key => $value){
			if(!is_numeric($key)){
				$row[$key] = $value;
			}
		}
		$result[] = $row;
	}

	StopTimer("MySQLDataToArray");
	return $result;
}

// Converts a two dimensional array into a html-formatted table (string output)
function ArrayToHTML($array){
	AddActionLog("ArrayToHTML");
	StartTimer("ArrayToHTML");

	if(count($array) == 0){
		StopTimer("ArrayToHTML");
		return "No data in table";
	}

	$str = "<table style='border: solid 1px'>";

	$columnNames = array();

	foreach($array as $id => $row){
		$str .= "<tr style='border: solid 2px'><td>#</td>";
		foreach($row as $key => $value){
			$str .= "<th style='border: solid 2px'>";
			$str .= "$key";
			$str .= "</th>";
			array_push($columnNames, $key);
		}
		$str .= "</tr>";
		break;
	}

	foreach($array as $id => $row){
		$str .= "<tr style='border: solid 1px'><td>$id</td>";
		foreach($columnNames as $columnName) {
			$value = "";
			
			if (isset($row[$columnName])) {
				$value = $row[$columnName];
			}

			$str .= "<td style='border: solid 1px'>";
			$str .= "$value";
			$str .= "</td>";
		}
		$str .= "</tr>";
	}

	$str .= "</table>";

	StopTimer("ArrayToHTML");
	return $str;
}

?>
