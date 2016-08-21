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
	if($a["banned"] == 1){
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

?>