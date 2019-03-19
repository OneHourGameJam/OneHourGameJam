<?php

class Game{
	public $Id;
	public $JamId;
	public $JamNumber;
	public $Title;
	public $Description;
	public $Author;
	public $Url;
	public $UrlWeb;
	public $UrlWindows;
	public $UrlMac;
	public $UrlLinux;
	public $UrliOs;
	public $UrlAndroid;
	public $UrlSource;
	public $UrlScreenshot;
	public $Color;
	public $Deleted;
}

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
		$game = new Game();

		$game->Id = $info["entry_id"];
		$game->JamId = intval($info["entry_jam_id"]);
		$game->JamNumber = intval($info["entry_jam_number"]);
		$game->Title = $info["entry_title"];
		$game->Description = $info["entry_description"];
		$game->Author = $info["entry_author"];
		$game->Url = $info["entry_url"];
		$game->UrlWeb = $info["entry_url_web"];
		$game->UrlWindows = $info["entry_url_windows"];
		$game->UrlMac = $info["entry_url_mac"];
		$game->UrlLinux = $info["entry_url_linux"];
		$game->UrliOs = $info["entry_url_ios"];
		$game->UrlAndroid = $info["entry_url_android"];
		$game->UrlSource = $info["entry_url_source"];
		$game->UrlScreenshot = $info["entry_screenshot_url"];
		$game->Color = $info["entry_color"];
		$game->Deleted = $info["entry_deleted"];

		$games[] = $game;
	}

	StopTimer("LoadGames");
	return $games;
}



function RenderGames(&$users, &$games, &$jams, $renderDepth){
	AddActionLog("RenderGames");
	StartTimer("RenderGames");

	$render = Array("LIST" => Array());
    $nonDeletedGamesCounter = 0;
	foreach($games as $i => $game){
		if(($renderDepth & RENDER_DEPTH_GAMES) > 0){
			$render["LIST"][] = RenderGame($users, $game, $jams, $renderDepth);
		}
        if($game->Deleted != 1){
            $nonDeletedGamesCounter += 1;
        }
    }
    $render["all_entries_count"] = $nonDeletedGamesCounter;

	StopTimer("RenderGames");
	return $render;
}

function RenderGame(&$users, &$game, &$jams, $renderDepth){
	AddActionLog("RenderGame");
	StartTimer("RenderGame");
	
	$jamId = intval($game->JamId);
	$title = $game->Title;

	$render = Array();
	$render["id"] = $game->Id;
	$render["jam_id"] = $jamId;
	$render["jam_number"] = intval($game->JamNumber);
	$render["title"] = $title;
	$render["description"] = $game->Description;
	$render["author"] = $game->Author;
	$render["url"] = str_replace("'", "\\'", $game->Url);
	$render["url_web"] = str_replace("'", "\\'", $game->UrlWeb);
	$render["url_windows"] = str_replace("'", "\\'", $game->UrlWindows);
	$render["url_mac"] = str_replace("'", "\\'", $game->UrlMac);
	$render["url_linux"] = str_replace("'", "\\'", $game->UrlLinux);
	$render["url_ios"] = str_replace("'", "\\'", $game->UrliOs);
	$render["url_android"] = str_replace("'", "\\'", $game->UrlAndroid);
	$render["url_source"] = str_replace("'", "\\'", $game->UrlSource);
	$render["screenshot_url"] = str_replace("'", "\\'", $game->UrlScreenshot);
	$render["entry_deleted"] = $game->Deleted;
	$render["title_url_encoded"] = urlencode($title);

	//Entry color
	$render["color"] = "#".$game->Color;
	$render["color256_red"] = hexdec(substr($game->Color, 0, 2));
	$render["color256_green"] = hexdec(substr($game->Color, 2, 2));
	$render["color256_blue"] = hexdec(substr($game->Color, 4, 2));
	$render["color_lighter"] = "#".str_pad(dechex( ($render["color256_red"] + 255) / 2 ), 2, "0", STR_PAD_LEFT).str_pad(dechex( ($render["color256_green"] + 255) / 2 ), 2, "0", STR_PAD_LEFT).str_pad(dechex( ($render["color256_blue"] + 255) / 2 ), 2, "0", STR_PAD_LEFT);
	$render["color_non_white"] = "#".str_pad(dechex(min($render["color256_red"], 0xDD)), 2, "0", STR_PAD_LEFT).str_pad(dechex(min($render["color256_green"], 0xDD)), 2, "0", STR_PAD_LEFT).str_pad(dechex(min($render["color256_blue"], 0xDD)), 2, "0", STR_PAD_LEFT);

	//Mini RenderJam()
	$jamData = $jams[$jamId];
	$render["jam_number"] = $jamData->JamNumber;
	$render["jam_theme"] = $jamData->Theme;

	//Mini RenderUser()
	$author = $render["author"];
	$author_display = $author;
	if(isset($users[$author]->DisplayName)){
		$author_display = $users[$author]->DisplayName;
	}
	$render["author_display"] = $author_display;
	$render["author"] = $author;
	$render["author_url_encoded"] = urlencode($author);

	$render["has_url"] = ($game->Url != "") ? 1 : 0;
	$render["has_url_web"] = ($game->UrlWeb != "") ? 1 : 0;
	$render["has_url_windows"] = ($game->UrlWindows != "") ? 1 : 0;
	$render["has_url_mac"] = ($game->UrlMac != "") ? 1 : 0;
	$render["has_url_linux"] = ($game->UrlLinux != "") ? 1 : 0;
	$render["has_url_ios"] = ($game->UrliOs != "") ? 1 : 0;
	$render["has_url_android"] = ($game->UrlAndroid != "") ? 1 : 0;
	$render["has_url_source"] = ($game->UrlSource != "") ? 1 : 0;

	if($render["screenshot_url"] != "logo.png" &&
	   $render["screenshot_url"] != ""){
		$render["has_screenshot"] = 1;
	}

	if(trim($render["title"]) != ""){
		$render["has_title"] = 1;
	}

	if(trim($render["description"]) != ""){
		$render["has_description"] = 1;
	}

	StopTimer("RenderGame");
	return $render;
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