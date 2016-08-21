<?php

/*

This file is meant to help transition your installation from the JSON file based storage to databases. Put the files you want to transition into the /migratedata folder (files named jam_xy.json, where xy is a number). Then open the page in your browser. It will generate the SQL statements needed to move the content from the json files into the database. Copy-paste these statements into your database (using mysql workbench of phpmyadmin).

*/

?>


<html lang="en">
	<head>
		<meta charset='utf-8'>
		<script src="js/jquery.js"></script>
		<title>One hour game jam</title>
		<meta name="viewport" content="width=device-width, initial-scale=1">

		<link href="bs/css/bootstrap.min.css" rel="stylesheet">
		<link href="css/site.css" rel="stylesheet">
		<link rel="shortcut icon" href="/favicon.ico" type="image/x-icon">
		<link rel="icon" href="/favicon.ico" type="image/x-icon">
		<script src='js/1hgj.js' type='text/javascript'></script>
		
	</head>
	<body>
	
	<?php


$jamID = 1;
$entryID = 1;

for($i = 1; $i <= 100; $i++){		

	if(!file_exists("migratedata/jam_$i.json")){
		continue;
	}

	$data = json_decode(file_get_contents("migratedata/jam_$i.json"), true);
	
	//print_r($data);
	
	$datetime = date("Y-m-d H:i", strtotime($data["date"]." ".$data["time"]));
	
	$theme = $data["theme"];
	$theme = str_replace("'", "\\'", $theme);
	$theme = str_replace("\"", "\\\"", $theme);
	
	print "<br>INSERT INTO jam
		(jam_id,
		jam_datetime,
		jam_ip,
		jam_user_agent,
		jam_jam_number,
		jam_theme,
		jam_start_datetime,
		jam_deleted)
		VALUES(
		$jamID,
		Now(),
		'LEGACY',
		'LEGACY',
		".$data["jam_number"].",
		'$theme',
		'$datetime',
		0);
	";
	
	foreach($data["entries"] as $j => $entry){
		$title = $entry["title"];
		$title = str_replace("'", "\\'", $title);
		$title = str_replace("\"", "\\\"", $title);
		$description = $entry["description"];
		$description = str_replace("'", "\\'", $description);
		$description = str_replace("\"", "\\\"", $description);
		$author = $entry["author"];
		$author = str_replace("'", "\\'", $author);
		$author = str_replace("\"", "\\\"", $author);
		$url = $entry["url"];
		$url = str_replace("'", "\\'", $url);
		$url = str_replace("\"", "\\\"", $url);
		$ss = $entry["screenshot_url"];
		$ss = str_replace("'", "\\'", $ss);
		$ss = str_replace("\"", "\\\"", $ss);
		
		print "<br>INSERT INTO entry
			(entry_id,
			entry_datetime,
			entry_ip,
			entry_user_agent,
			entry_jam_id,
			entry_jam_number,
			entry_title,
			entry_description,
			entry_author,
			entry_url,
			entry_screenshot_url,
			entry_deleted)
			VALUES(
			$entryID,
			Now(),
			'LEGACY',
			'LEGACY',
			$jamID,
			".$data["jam_number"].",
			'$title',
			'$description',
			'$author',
			'$url',
			'$ss',
			0);
";
		$entryID++;
	}
	
	$jamID++;
}


?>

</body>
</html>