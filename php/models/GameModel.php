<?php

class GameModel{
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

class GameData{
    public $GameModels;

    function __construct() {
        $this->GameModels = $this->LoadGames();
    }

    function LoadGames(){
        global $dbConn;
        AddActionLog("LoadGames");
        StartTimer("LoadGames");

        $gameModels = Array();

        $sql = "SELECT entry_id, entry_jam_id, entry_jam_number, entry_title, entry_description, entry_author, entry_url, entry_url_web, entry_url_windows, entry_url_linux, entry_url_mac, entry_url, entry_url_android, entry_url_ios, entry_url_source, entry_screenshot_url, entry_color, entry_deleted
        FROM entry ORDER BY entry_id DESC";
        $data = mysqli_query($dbConn, $sql);
        $sql = "";

        while($info = mysqli_fetch_array($data)){
            $game = new GameModel();

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

            $gameModels[] = $game;
        }

        StopTimer("LoadGames");
        return $gameModels;
    }
}

?>