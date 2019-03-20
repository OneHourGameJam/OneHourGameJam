<?php

chdir("../../");
include_once("php/site.php");

//Number of minutes after jam to be considered active.
$jamDurationMinutes = $config->ConfigModels["JAM_DURATION"]->Value;

$sql = "
	SELECT jam_jam_number, jam_theme, jam_start_datetime, UTC_TIMESTAMP() as jam_now, UNIX_TIMESTAMP(jam_start_datetime) - UNIX_TIMESTAMP(UTC_TIMESTAMP()) AS jam_timediff
	FROM jam
	WHERE jam_deleted = 0
	ORDER BY jam_id
";
$data = mysqli_query($dbConn, $sql) ;
$sql = "";

$maxJamNumber = 0;

$return = Array();
$return["now"] = gmdate("Y-m-d H:i:s", time());
$return["upcoming_jams"] = Array();
$return["current_jams"] = Array();
$return["previous_jams"] = Array();

while($info = mysqli_fetch_array($data)){
	$row = Array();

	$row["number"] = intval($info["jam_jam_number"]);
	$row["theme"] = $info["jam_theme"];
	$row["start_datetime"] = $info["jam_start_datetime"];;
	$row["timediff"] = intval($info["jam_timediff"]);

    $maxJamNumber = max($maxJamNumber, intval($row["number"]));

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

if(count($return["upcoming_jams"]) == 0){
    //No jam scheduled yet, insert stub.

    $now = time();
    $saturday = GetSuggestedNextJamDateTime($config->ConfigModels);

	$interval = $saturday - $now;

    //$timediff = intval(date("U", $saturday)) - intval(date("U", $now));
	$timediff = $interval;
	if($timediff < 0){
		$saturday = strtotime("+7 day", $saturday);
    	//$timediff = intval(date("U", $saturday)) - intval(date("U", $now));
		$timediff = $timediff + (7*24*60*60);
	}

    $return["upcoming_jams"][] = Array("number" => $maxJamNumber + 1, "theme" => "Not announced yet", "start_datetime" => gmdate("Y-m-d H:i:s", $saturday), "timediff" => $timediff);
}

print json_encode($return);
?>