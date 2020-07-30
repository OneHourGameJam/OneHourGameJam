<?php

define("DB_TABLE_PLATFORMENTRY", "platform_entry");
define("DB_COLUMN_PLATFORMENTRY_ID",            "platformentry_id");
define("DB_COLUMN_PLATFORMENTRY_ENTRY_ID",      "platformentry_entry_id");
define("DB_COLUMN_PLATFORMENTRY_PLATFORM_ID",   "platformentry_platform_id");
define("DB_COLUMN_PLATFORMENTRY_URL",           "platformentry_url");

class PlatformGameModel{
	public $Id;
	public $GameId;
	public $PlatformId;
	public $Url;
}

class PlatformGameData{
    public $PlatformGameModels;
    public $GameIdToPlatformGameIds;

    private $dbConnection;
    private $publicColumns = Array(DB_COLUMN_PLATFORMENTRY_ID, DB_COLUMN_PLATFORMENTRY_ENTRY_ID, DB_COLUMN_PLATFORMENTRY_PLATFORM_ID, DB_COLUMN_PLATFORMENTRY_URL);
    private $privateColumns = Array();

    function __construct(&$dbConn) {
        $this->dbConnection = $dbConn;
        $this->PlatformGameModels = $this->LoadPlatformEntries();
        $this->GameIdToPlatformGameIds = $this->GenerateGameIdToPlatformGameIds();
    }

//////////////////////// MODEL CONSTRUCTOR

    function LoadPlatformEntries(){
        AddActionLog("LoadPlatformEntries");
        StartTimer("LoadPlatformEntries");

        $data = $this->SelectAll();

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
    
//////////////////////// FUCNTION SQL

    private function SelectAll(){
        AddActionLog("PlatformGameData_SelectAll");
        StartTimer("PlatformGameData_SelectAll");

        $sql = "
            SELECT ".DB_COLUMN_PLATFORMENTRY_ID.", ".DB_COLUMN_PLATFORMENTRY_ENTRY_ID.", ".DB_COLUMN_PLATFORMENTRY_PLATFORM_ID.", ".DB_COLUMN_PLATFORMENTRY_URL." 
            FROM ".DB_TABLE_PLATFORMENTRY.";";
        
        StopTimer("PlatformGameData_SelectAll");
        return mysqli_query($this->dbConnection, $sql);
    }

//////////////////////// END FUCNTION SQL

//////////////////////// PUBLIC DATA EXPORT

    function GetAllPublicData(){
        AddActionLog("PlatformGameData_GetAllPublicData");
        StartTimer("PlatformGameData_GetAllPublicData");
        
        $dataFromDatabase = MySQLDataToArray($this->SelectPublicData());

        foreach($dataFromDatabase as $i => $row){
            //private data overrides
        }

        StopTimer("PlatformGameData_GetAllPublicData");
        return $dataFromDatabase;
    }

    private function SelectPublicData(){
        AddActionLog("PlatformGameData_SelectPublicData");
        StartTimer("PlatformGameData_SelectPublicData");

        $sql = "
            SELECT ".implode(",", $this->publicColumns)."
            FROM ".DB_TABLE_PLATFORMENTRY.";
        ";

        StopTimer("PlatformGameData_SelectPublicData");
        return mysqli_query($this->dbConnection, $sql);
    }

//////////////////////// END PUBLIC DATA EXPORT
}

?>