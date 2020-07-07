<?php

class PlatformGameModel{
	public $Id;
	public $GameId;
	public $PlatformId;
	public $Url;
}

class PlatformGameData{
    public $PlatformGameModels;
    public $GameIdToPlatformGameIds;

    function __construct() {
        $this->PlatformGameModels = $this->LoadPlatformEntries();
        $this->GameIdToPlatformGameIds = $this->GenerateGameIdToPlatformGameIds();
    }

    function LoadPlatformEntries(){
        global $dbConn;
        AddActionLog("LoadPlatformEntries");
        StartTimer("LoadPlatformEntries");

        $platformGameModels = Array();

        $sql = "SELECT platformentry_id, platformentry_entry_id, platformentry_platform_id, platformentry_url FROM platform_entry";
        $data = mysqli_query($dbConn, $sql);
        $sql = "";

        while($info = mysqli_fetch_array($data)){
            $platformGame = new PlatformGameModel();

            $platformGame->Id = intval($info["platformentry_id"]);
            $platformGame->GameId = intval($info["platformentry_entry_id"]);
            $platformGame->PlatformId = intval($info["platformentry_platform_id"]);
            $platformGame->Url = $info["platformentry_url"];

            $platformGameModels[$platformGame->Id] = $platformGame;
        }

        StopTimer("LoadPlatformEntries");
        return $platformGameModels;
    }

    function GenerateGameIdToPlatformGameIds(){
        $gameIdToPlatformGameIds = Array();

        foreach($this->PlatformGameModels as $i => $platformGameModel){
            $gameId = $platformGameModel->GameId;
            $platformGameId = $platformGameModel->Id;
            
            if(!isset($gameIdToPlatformGameIds[$gameId])){
                $gameIdToPlatformGameIds[$gameId] = Array();
            }

            $gameIdToPlatformGameIds[$gameId][] = $platformGameId;
        }

        return $gameIdToPlatformGameIds;
    }
}

?>