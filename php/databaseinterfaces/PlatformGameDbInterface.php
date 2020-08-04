<?php
define("DB_TABLE_PLATFORMENTRY", "platform_entry");

define("DB_COLUMN_PLATFORMENTRY_ID",            "platformentry_id");
define("DB_COLUMN_PLATFORMENTRY_ENTRY_ID",      "platformentry_entry_id");
define("DB_COLUMN_PLATFORMENTRY_PLATFORM_ID",   "platformentry_platform_id");
define("DB_COLUMN_PLATFORMENTRY_URL",           "platformentry_url");

class PlatformGameDbInterface{
    private $dbConnection;
    private $publicColumns = Array(DB_COLUMN_PLATFORMENTRY_ID, DB_COLUMN_PLATFORMENTRY_ENTRY_ID, DB_COLUMN_PLATFORMENTRY_PLATFORM_ID, DB_COLUMN_PLATFORMENTRY_URL);
    private $privateColumns = Array();

    function __construct(&$dbConn) {
        $this->dbConnection = $dbConn;
    }

    public function SelectAll(){
        AddActionLog("PlatformGameDbInterface_SelectAll");
        StartTimer("PlatformGameDbInterface_SelectAll");

        $sql = "
            SELECT ".DB_COLUMN_PLATFORMENTRY_ID.", ".DB_COLUMN_PLATFORMENTRY_ENTRY_ID.", ".DB_COLUMN_PLATFORMENTRY_PLATFORM_ID.", ".DB_COLUMN_PLATFORMENTRY_URL." 
            FROM ".DB_TABLE_PLATFORMENTRY.";";
        
        StopTimer("PlatformGameDbInterface_SelectAll");
        return mysqli_query($this->dbConnection, $sql);
    }

    public function SelectPublicData(){
        AddActionLog("PlatformGameDbInterface_SelectPublicData");
        StartTimer("PlatformGameDbInterface_SelectPublicData");

        $sql = "
            SELECT ".implode(",", $this->publicColumns)."
            FROM ".DB_TABLE_PLATFORMENTRY.";
        ";

        StopTimer("PlatformGameDbInterface_SelectPublicData");
        return mysqli_query($this->dbConnection, $sql);
    }
}

?>