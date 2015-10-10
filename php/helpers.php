<?php

function StartsWith($haystack, $needle) {
    // search backwards starting from haystack length characters from the end
    return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== FALSE;
}

function EndsWith($haystack, $needle) {
    // search forward starting from end minus needle length characters
    return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== FALSE);
}

//Returns the number for the next jam. 
//If a jam json file is deleted, it does not return the jam number of the deleted jam,
//unless the deleted jam was the last one. Essentially it returns Max(jam_number) +1,
//determined by file names. Capped at 1000 jam entries.
function GetNextJamNumber(){
	$NextJamNumber = 1;
	
	for($i = 1; $i < 1000; $i++){
		if(file_exists("data/jams/jam_$i.json")){
			$NextJamNumber = max($NextJamNumber, $i + 1);
		}
	}
	
	return $NextJamNumber;
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

?>