<?php

chdir("../../");
include_once("php/helpers.php");
$filesToParse = GetSortedJamFileList();

$minPositiveInterval = -1;
$maxNegativeInterval = 1;
$nextJam = Array();
$lastJam = Array();

foreach ($filesToParse as $fileLoc) {
	$jam = json_decode(file_get_contents($fileLoc), true);
	
	$now = time();
	$now = strtotime(gmdate("d F Y H:i:s",$now));
	$jamStartTime = strtotime($jam["date"]." ".$jam["time"]);
	
	$interval = $jamStartTime - $now;
	
	if($interval > 0 && ($interval < $minPositiveInterval || $minPositiveInterval == -1)){
		$minPositiveInterval = $interval;
		$nextJam = array("jam_number" => $jam["jam_number"], "date_gmt" => $jam["date"], "time_gmt" => $jam["time"], "seconds_until_start" => $interval);
	}
	
	if($interval < 0 && ($interval > $maxNegativeInterval || $maxNegativeInterval == 1)){
		$maxNegativeInterval = $interval;
		$lastJam = array("jam_number" => $jam["jam_number"], "theme" => $jam["theme"], "date_gmt" => $jam["date"], "time_gmt" => $jam["time"], "seconds_since_start" => $interval * -1);
	}
}

if(count($nextJam) == 0){
	print json_encode(Array("next_jam" => Array("ERROR" => "No upcoming jam"), "last_jam" => $lastJam));
}else{
	print json_encode(Array("next_jam" => $nextJam, "last_jam" => $lastJam));
}
?>