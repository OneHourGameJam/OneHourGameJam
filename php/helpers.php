<?php

function StartsWith($haystack, $needle) {
    // search backwards starting from haystack length characters from the end
    return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== FALSE;
}

function EndsWith($haystack, $needle) {
    // search forward starting from end minus needle length characters
    return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== FALSE);
}

//Numeric comparator for arrays based on property
function CmpArrayByPropertyPopularityNum($a, $b)
{
	if(isset($a["banned"]) && $a["banned"] == 1){
		return 1;
	}
	return CmpArrayByProperty($a, $b, "popularity_num");
}

//Numeric comparator for arrays based on property
function CmpArrayByProperty($a, $b, $property)
{
	return $a[$property] < $b[$property];
}

//Gets the list of json file locations, sorted in ascending order
//Capped at 1000 entries.
function GetSortedJamFileList(){
	$filesToParse = Array();
	for($i = 0; $i < 1000; $i++){
		if(file_exists("data/jams/jam_$i.json")){
			$filesToParse[] = "data/jams/jam_$i.json";
		}
	}
	krsort($filesToParse);
	return $filesToParse;
}

//Returns ordinal version of provided number, so 1 -> 1st; 3 -> 3rd, etc.
function ordinal($number) {
	$number = intval($number);
    $ends = array('th','st','nd','rd','th','th','th','th','th','th');
    if ((($number % 100) >= 11) && (($number%100) <= 13))
        return $number. 'th';
    else
        return $number. $ends[$number % 10];
}

function GetNextJamDateAndTime(){
	global $dictionary, $config;
	
    $jamDay = "monday";
    switch($config["LANG_JAM_DAY"]){
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

	$suggestedDay = strtotime("$jamDay +" . intval($config["LANG_JAM_TIME"]) . " hours UTC");
	$dictionary["next_jam_suggested_date"] = gmdate("Y-m-d", $suggestedDay);
	$dictionary["next_jam_suggested_time"] = gmdate("H:i", $suggestedDay);
	$now = time();
	$interval = $suggestedDay - $now;
	$dictionary["seconds_until_jam_suggested_time"] = $interval;
	return $suggestedDay;
}

$currentJamNumberArchive = FALSE;
function GetCurrentJamNumberAndID(){
	global $currentJamNumberArchive, $dbConn;
	
	if($currentJamNumberArchive !== FALSE){
		return $currentJamNumberArchive;
	}
	
	$sql = "
		SELECT jam_id, jam_jam_number FROM jam WHERE jam_jam_number = (SELECT MAX(jam_jam_number) FROM jam WHERE jam_deleted = 0) AND jam_deleted = 0
	";
	$data = mysqli_query($dbConn, $sql);
	$sql = "";
	
	if($info = mysqli_fetch_array($data)){
		$currentJamNumberArchive = Array("NUMBER" => intval($info["jam_jam_number"]), "ID" => intval($info["jam_id"]));
	}else{
		$currentJamNumberArchive = Array("NUMBER" => 0, "ID" => 0);
	}
	return $currentJamNumberArchive;
}

// Uses a whitelist of tags and attributes, plus parses the HTML to ensure
// the markup is well-formed and limited to non-harming code.
function CleanHtml($html) {
	// Remove tags
	$halfCleanedHtml = strip_tags($html, '<p><strong><em><strike><sup><sub><a><ul><li>');

	// Parse non-empty HTML only
	if (!empty(trim($html))) {
		$dom = new DOMDocument();
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
		$cleanedHtml = '';
		foreach ($dom->getElementsByTagName('body')->item(0)->childNodes as $node) {
		    $cleanedHtml .= $dom->saveXML($node);
		}
		return $cleanedHtml;
	} else {
		return NULL;
	}
}

?>