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

    private $platformGameDbInterface;

    function __construct(&$platformGameDbInterface) {
        $this->platformGameDbInterface = $platformGameDbInterface;
        $this->PlatformGameModels = $this->LoadPlatformEntries();
        $this->GameIdToPlatformGameIds = $this->GenerateGameIdToPlatformGameIds();
    }

//////////////////////// MODEL CONSTRUCTOR

    function LoadPlatformEntries(){
        AddActionLog("LoadPlatformEntries");
        StartTimer("LoadPlatformEntries");

        $data = $this->platformGameDbInterface->SelectAll();

        $platformGameModels = Array();
        while($info = mysqli_fetch_array($data)){
            $platformGame = new PlatformGameModel();

            $platformGame->Id = intval($info[DB_COLUMN_PLATFORMENTRY_ID]);
            $platformGame->GameId = intval($info[DB_COLUMN_PLATFORMENTRY_ENTRY_ID]);
            $platformGame->PlatformId = intval($info[DB_COLUMN_PLATFORMENTRY_PLATFORM_ID]);
            $platformGame->Url = $info[DB_COLUMN_PLATFORMENTRY_URL];

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

//////////////////////// END MODEL CONSTRUCTOR
    
//////////////////////// DATABASE ACTIONS (select, insert, update)

//////////////////////// END DATABASE ACTIONS

//////////////////////// PUBLIC DATA EXPORT

    function GetAllPublicData(){
        AddActionLog("PlatformGameData_GetAllPublicData");
        StartTimer("PlatformGameData_GetAllPublicData");
        
        $dataFromDatabase = MySQLDataToArray($this->platformGameDbInterface->SelectPublicData());

        foreach($dataFromDatabase as $i => $row){
            //private data overrides
        }

        StopTimer("PlatformGameData_GetAllPublicData");
        return $dataFromDatabase;
    }

//////////////////////// END PUBLIC DATA EXPORT
}

?>