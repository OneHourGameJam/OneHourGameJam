<?php

/*

This file is meant to help transition your installation from the JSON file based session storage to databases. Put the sessions.json into the /migratedata. Then open the page in your browser. It will generate the SQL statements needed to move the content from the json files into the database. Copy-paste these statements into your database (using mysql workbench of phpmyadmin).

*/

?>


<html lang="en">
	<head>
		<meta charset='utf-8'>
		<script src="../vendor/components/jquery/jquery.js"></script>
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

// Pretend we are at the project root
chdir("../");

require_once("php/site.php");

if(file_exists("plugins/migratedata/sessions.json")){
	$data = json_decode(file_get_contents("plugins/migratedata/sessions.json"), true);

	$skippedSessions = 0;

	foreach($data as $id => $session){
		$userID = $users[$session["username"]]["id"];
		$timestamp = date("Y-m-d H:i:s", $session['datetime']);

		if ($session['datetime']-60*60*24*30 <= time()) {
			$skippedSessions += 1;
		}

		print "<br>INSERT INTO session
			(session_id,
			session_user_id,
			session_datetime_started,
			session_datetime_last_used)
			VALUES
			('$id',
			'$userID',
			'$timestamp',
			'$timestamp');	#".$session["username"].";
		";
	}

	print("<br><b># Skipped: $skippedSessions</b>");
}

?>

</body>
</html>
