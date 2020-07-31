<?php

define("DB_TABLE_PLATFORM", "platform");
define("DB_COLUMN_PLATFORM_ID",         "platform_id");
define("DB_COLUMN_PLATFORM_NAME",       "platform_name");
define("DB_COLUMN_PLATFORM_ICON_URL",   "platform_icon_url");
define("DB_COLUMN_PLATFORM_DELETED",    "platform_deleted");

class PlatformModel{
	public $Id;
	public $Name;
	public $IconUrl;
	public $Deleted;
}

class PlatformData{
    public $PlatformModels;

    private $dbConnection;
    private $publicColumns = Array(DB_COLUMN_PLATFORM_ID, DB_COLUMN_PLATFORM_NAME, DB_COLUMN_PLATFORM_ICON_URL, DB_COLUMN_PLATFORM_DELETED);
    private $privateColumns = Array();

    function __construct(&$dbConn) {
        $this->dbConnection = $dbConn;
        $this->PlatformModels = $this->LoadPlatforms();
    }

//////////////////////// MODEL CONSTRUCTOR

    function LoadPlatforms(){
        AddActionLog("LoadPlatforms");
        StartTimer("LoadPlatforms");

        $data = $this->SelectAll();

        $platformModels = Array();
        while($info = mysqli_fetch_array($data)){
            $platform = new PlatformModel();

            $platform->Id = intval($info[DB_COLUMN_PLATFORM_ID]);
            $platform->Name = $info[DB_COLUMN_PLATFORM_NAME];
            $platform->IconUrl = $info[DB_COLUMN_PLATFORM_ICON_URL];
            $platform->Deleted = intval($info[DB_COLUMN_PLATFORM_DELETED]);

            $platformModels[$platform->Id] = $platform;
        }

        StopTimer("LoadPlatforms");
        return $platformModels;
    }

//////////////////////// END MODEL CONSTRUCTOR
    
//////////////////////// DATABASE ACTIONS (select, insert, update)

//////////////////////// END DATABASE ACTIONS
    
//////////////////////// FUCNTION SQL

    private function SelectAll(){
        AddActionLog("PlatformData_SelectAll");
        StartTimer("PlatformData_SelectAll");

        $sql = "
            SELECT ".DB_COLUMN_PLATFORM_ID.", ".DB_COLUMN_PLATFORM_NAME.", ".DB_COLUMN_PLATFORM_ICON_URL.", ".DB_COLUMN_PLATFORM_DELETED."
            FROM ".DB_TABLE_PLATFORM.";";
        
        StopTimer("PlatformData_SelectAll");
        return mysqli_query($this->dbConnection, $sql);
    }


//////////////////////// END FUCNTION SQL

//////////////////////// PUBLIC DATA EXPORT

    function GetAllPublicData(){
        AddActionLog("PlatformData_GetAllPublicData");
        StartTimer("PlatformData_GetAllPublicData");
        
        $dataFromDatabase = MySQLDataToArray($this->SelectPublicData());

        foreach($dataFromDatabase as $i => $row){
            //private data overrides
        }

        StopTimer("PlatformData_GetAllPublicData");
        return $dataFromDatabase;
    }

    private function SelectPublicData(){
        AddActionLog("PlatformData_SelectPublicData");
        StartTimer("PlatformData_SelectPublicData");

        $sql = "
            SELECT ".implode(",", $this->publicColumns)."
            FROM ".DB_TABLE_PLATFORM.";
        ";

        StopTimer("PlatformData_SelectPublicData");
        return mysqli_query($this->dbConnection, $sql);
    }

//////////////////////// END PUBLIC DATA EXPORT
}

?>