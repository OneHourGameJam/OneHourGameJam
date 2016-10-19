<?php

chdir("../../");
include_once("php/db.php");

//Number of minutes after jam to be considered active.
$jamDurationMinutes = 60;

$sql = "	
	SELECT jam_jam_number, jam_theme, jam_start_datetime, UTC_TIMESTAMP() as jam_now, UNIX_TIMESTAMP(jam_start_datetime) - UNIX_TIMESTAMP(UTC_TIMESTAMP()) AS jam_timediff
	FROM jam
	WHERE jam_deleted = 0
";
$data = mysqli_query($dbConn, $sql) ;
$sql = "";

$return = Array();
$return["upcoming_jams"] = Array();
$return["current_jams"] = Array();
$return["previous_jams"] = Array();

while($info = mysqli_fetch_array($data)){
	$row = Array();
	foreach(array_keys($info) as $key){
		if(is_numeric($key)){
			continue;
		}
		$row[str_replace("jam_", "", $key)] = $info[$key];
	}
	
	if(intval($info["jam_timediff"]) > 0){
		//Future jam
		$row["theme"] = "Not announced yet";
		$return["upcoming_jams"][] = $row;
	}else if(intval($info["jam_timediff"]) >= -60 * $jamDurationMinutes){
		$return["current_jams"][] = $row;
	}else{
		$return["previous_jams"][] = $row;
	}
}

print json_encode($return);

die();

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