<?php

$username = ((isset($_GET["username"])) ? $_GET["username"] : "");
if($username == ""){
	print json_encode(Array("ERROR" => "No username provided"));
	die();
}

$data = Array();

$data["username"] = $username;
$data["jam"] = "1 hour game jam";
$data["jam_url"] = "http://onehourgamejam.com/";

for($i = 1; $i < 1000; $i++){
	if(file_exists("../../data/jams/jam_$i.json")){
		$content = json_decode(file_get_contents("../../data/jams/jam_$i.json"), true);

		$theme = $content["theme"];
		$jam_date = $content["date"];
		$jam_time = $content["time"];
		$start_time = $content["start_time"];

		foreach($content["entries"] as $j => $d){
			if($d["author"] != $username){
				continue;
			}

			$title = $d["title"];
			$author = $d["author"];
			$url = $d["url"];
			$screenshot_url = $d["screenshot_url"];

			$data["games"][] = Array("title" => $title, "author" => $author, "game_url" => $url, "screenshot_url" => $screenshot_url, "jam_number" => $i, "jam_theme" => $theme, "jam_date" => $jam_date, "jam_time_utc" => $jam_time, "jam_time" => $start_time);
		}
	}else{
		break;
	}
}

print json_encode($data);



?>