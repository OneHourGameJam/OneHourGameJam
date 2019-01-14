<?php

function LoadGames(){
	global $dbConn;
	AddActionLog("LoadGames");
	StartTimer("LoadGames");

	$games = Array();

	$sql = "SELECT entry_id, entry_jam_id, entry_jam_number, entry_title, entry_description, entry_author, entry_url, entry_url_web, entry_url_windows, entry_url_linux, entry_url_mac, entry_url, entry_url_android, entry_url_ios, entry_url_source, entry_screenshot_url, entry_color, entry_deleted
	 FROM entry ORDER BY entry_id DESC";
	$data = mysqli_query($dbConn, $sql);
	$sql = "";

	while($info = mysqli_fetch_array($data)){
		$gameData = Array();

		$gameData["id"] = $info["entry_id"];
		$gameData["jam_id"] = intval($info["entry_jam_id"]);
		$gameData["jam_number"] = intval($info["entry_jam_number"]);
		$gameData["title"] = $info["entry_title"];
		$gameData["description"] = $info["entry_description"];
		$gameData["author"] = $info["entry_author"];
		$gameData["url"] = $info["entry_url"];
		$gameData["url_web"] = $info["entry_url_web"];
		$gameData["url_windows"] = $info["entry_url_windows"];
		$gameData["url_mac"] = $info["entry_url_mac"];
		$gameData["url_linux"] = $info["entry_url_linux"];
		$gameData["url_ios"] = $info["entry_url_ios"];
		$gameData["url_android"] = $info["entry_url_android"];
		$gameData["url_source"] = $info["entry_url_source"];
		$gameData["screenshot_url"] = $info["entry_screenshot_url"];
		$gameData["color"] = $info["entry_color"];
		$gameData["entry_deleted"] = $info["entry_deleted"];

		$games[] = $gameData;
	}

	StopTimer("LoadGames");
	return $games;
}



function RenderGames(&$games, &$jams, &$users){
	AddActionLog("RenderGames");
	StartTimer("RenderGames");
	$render = Array("LIST" => Array());
    $nonDeletedGamesCounter = 0;
	foreach($games as $i => $game){
        $render["LIST"][] = RenderGame($game, $jams, $users);
        if($game["entry_deleted"] != 1){
            $nonDeletedGamesCounter += 1;
        }
    }
    $render["all_entries_count"] = $nonDeletedGamesCounter;

	StopTimer("RenderGames");
	return $render;
}

function RenderGame(&$game, &$jams, &$users){
	AddActionLog("RenderGame");
	StartTimer("RenderGame");
	$gameData = Array();
	$gameData["id"] = $game["id"];
	$gameData["jam_id"] = intval($game["jam_id"]);
	$gameData["jam_number"] = intval($game["jam_number"]);
	$gameData["title"] = $game["title"];
	$gameData["description"] = $game["description"];
	$gameData["author"] = $game["author"];
	$gameData["url"] = str_replace("'", "\\'", $game["url"]);
	$gameData["url_web"] = str_replace("'", "\\'", $game["url_web"]);
	$gameData["url_windows"] = str_replace("'", "\\'", $game["url_windows"]);
	$gameData["url_mac"] = str_replace("'", "\\'", $game["url_mac"]);
	$gameData["url_linux"] = str_replace("'", "\\'", $game["url_linux"]);
	$gameData["url_ios"] = str_replace("'", "\\'", $game["url_ios"]);
	$gameData["url_android"] = str_replace("'", "\\'", $game["url_android"]);
	$gameData["url_source"] = str_replace("'", "\\'", $game["url_source"]);
	$gameData["screenshot_url"] = str_replace("'", "\\'", $game["screenshot_url"]);

	$jamID = $gameData["jam_id"];
	$jamData = $jams[$jamID];

	$gameData["title_url_encoded"] = urlencode($game["title"]);

	if($game["entry_deleted"] == 1){
		$gameData["entry_deleted"] = $game["entry_deleted"];
	}

	//Entry color
	$gameData["color"] = "#".$game["color"];
	$gameData["color256_red"] = hexdec(substr($game["color"], 0, 2));
	$gameData["color256_green"] = hexdec(substr($game["color"], 2, 2));
	$gameData["color256_blue"] = hexdec(substr($game["color"], 4, 2));
	$gameData["color_lighter"] = "#".str_pad(dechex( ($gameData["color256_red"] + 255) / 2 ), 2, "0", STR_PAD_LEFT).str_pad(dechex( ($gameData["color256_green"] + 255) / 2 ), 2, "0", STR_PAD_LEFT).str_pad(dechex( ($gameData["color256_blue"] + 255) / 2 ), 2, "0", STR_PAD_LEFT);
	$gameData["color_non_white"] = "#".str_pad(dechex(min($gameData["color256_red"], 0xDD)), 2, "0", STR_PAD_LEFT).str_pad(dechex(min($gameData["color256_green"], 0xDD)), 2, "0", STR_PAD_LEFT).str_pad(dechex(min($gameData["color256_blue"], 0xDD)), 2, "0", STR_PAD_LEFT);

	$gameData["jam_number"] = $jamData["jam_number"];
	$gameData["jam_theme"] = $jamData["theme"];

	//Entry author
	$author = $gameData["author"];
	$author_display = $author;
	if(isset($users[$author]["display_name"])){
		$author_display = $users[$author]["display_name"];
	}
	$gameData["author_display"] = $author_display;
	$gameData["author"] = $author;
	$gameData["author_url_encoded"] = urlencode($author);

	if($gameData["url"] != ""){$gameData["has_url"] = 1;}
	if($gameData["url_web"] != ""){$gameData["has_url_web"] = 1;}
	if($gameData["url_windows"] != ""){$gameData["has_url_windows"] = 1;}
	if($gameData["url_mac"] != ""){$gameData["has_url_mac"] = 1;}
	if($gameData["url_linux"] != ""){$gameData["has_url_linux"] = 1;}
	if($gameData["url_ios"] != ""){$gameData["has_url_ios"] = 1;}
	if($gameData["url_android"] != ""){$gameData["has_url_android"] = 1;}
	if($gameData["url_source"] != ""){$gameData["has_url_source"] = 1;}

	if($gameData["screenshot_url"] != "logo.png" &&
	   $gameData["screenshot_url"] != ""){
		$gameData["has_screenshot"] = 1;
	}

	if(trim($gameData["title"]) != ""){
		$gameData["has_title"] = 1;
	}

	if(trim($gameData["description"]) != ""){
		$gameData["has_description"] = 1;
	}

	StopTimer("RenderGame");
	return $gameData;
}

//Returns true / false based on whether or not the specified entry exists (and has not been deleted)
function EntryExists($gameID){
	global $dbConn;
	AddActionLog("EntryExists");
	StartTimer("EntryExists");

	//Validate values
	$gameID = intval($gameID);
	if($gameID <= 0){
		StopTimer("EntryExists");
		return FALSE;
	}

	$escapedEntryID = mysqli_real_escape_string($dbConn, "$gameID");

	$sql = "
		SELECT 1
		FROM entry
		WHERE entry_id = $escapedEntryID
		AND entry_deleted = 0;
		";
	$data = mysqli_query($dbConn, $sql);
	$sql = "";

	if(mysqli_fetch_array($data)){
		StopTimer("EntryExists");
		return true;
	}else{
		StopTimer("EntryExists");
		return false;
	}
	StopTimer("EntryExists");
}

function GetEntriesOfUserFormatted($author){
	global $dbConn;
	AddActionLog("GetEntriesOfUserFormatted");
	StartTimer("GetEntriesOfUserFormatted");

	$escapedAuthor = mysqli_real_escape_string($dbConn, $author);
	$sql = "
		SELECT *
		FROM entry
		WHERE entry_author = '$escapedAuthor';
	";
	$data = mysqli_query($dbConn, $sql);
	$sql = "";

	StopTimer("GetEntriesOfUserFormatted");
	return ArrayToHTML(MySQLDataToArray($data));
}

?>