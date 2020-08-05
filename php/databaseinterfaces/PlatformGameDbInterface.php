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

    public function SelectSinglePlatformEntryId($entryId, $platformId){
        AddActionLog("PlatformGameDbInterface_SelectSinglePlatformEntryId");
        StartTimer("PlatformGameDbInterface_SelectSinglePlatformEntryId");

        $escapedEntryId = mysqli_real_escape_string($this->dbConnection, $entryId);
        $escapedPlatformId = mysqli_real_escape_string($this->dbConnection, $platformId);
    
        $sql = "
            SELECT ".DB_COLUMN_PLATFORMENTRY_ID." 
            FROM ".DB_TABLE_PLATFORMENTRY." 
            WHERE ".DB_COLUMN_PLATFORMENTRY_ENTRY_ID." = $escapedEntryId 
              AND ".DB_COLUMN_PLATFORMENTRY_PLATFORM_ID." = $escapedPlatformId;";
        
        StopTimer("PlatformGameDbInterface_SelectSinglePlatformEntryId");
        return mysqli_query($this->dbConnection, $sql);
    }

    public function Insert($entryId, $platformId, $url){
        AddActionLog("PlatformGameDbInterface_Insert");
        StartTimer("PlatformGameDbInterface_Insert");
    
        $escapedEntryId = mysqli_real_escape_string($this->dbConnection, $entryId);
        $escapedPlatformId = mysqli_real_escape_string($this->dbConnection, $platformId);
        $escapedUrl = mysqli_real_escape_string($this->dbConnection, $url);

        $sql = "
            INSERT INTO ".DB_TABLE_PLATFORMENTRY."
            (".DB_COLUMN_PLATFORMENTRY_ID.", ".DB_COLUMN_PLATFORMENTRY_ENTRY_ID.", ".DB_COLUMN_PLATFORMENTRY_PLATFORM_ID.", ".DB_COLUMN_PLATFORMENTRY_URL.")
            VALUES
            (null, $escapedEntryId, $escapedPlatformId, '$escapedUrl');
        ";
        $data = mysqli_query($this->dbConnection, $sql);
        
        StopTimer("PlatformGameDbInterface_Insert");
    }

    public function UpdateUrl($platformEntryId, $url){
        AddActionLog("PlatformGameDbInterface_UpdateUrl");
        StartTimer("PlatformGameDbInterface_UpdateUrl");
    
        $escapedUrl = mysqli_real_escape_string($this->dbConnection, $url);
        $escapedPlatformEntryId = mysqli_real_escape_string($this->dbConnection, $platformEntryId);

		$sql = "
            UPDATE ".DB_TABLE_PLATFORMENTRY." 
            SET ".DB_COLUMN_PLATFORMENTRY_URL." = '$escapedUrl' 
            WHERE ".DB_COLUMN_PLATFORMENTRY_ID." = $escapedPlatformEntryId;";
        $data = mysqli_query($this->dbConnection, $sql);
        
        StopTimer("PlatformGameDbInterface_UpdateUrl");
    }

    public function Delete($platformEntryId){
        AddActionLog("PlatformGameDbInterface_Delete");
        StartTimer("PlatformGameDbInterface_Delete");
    
        $escapedPlatformEntryId = mysqli_real_escape_string($this->dbConnection, $platformEntryId);

        $sql = "
            DELETE FROM ".DB_TABLE_PLATFORMENTRY." 
            WHERE ".DB_COLUMN_PLATFORMENTRY_ID." = $escapedPlatformEntryId;";
        $data = mysqli_query($this->dbConnection, $sql);
        
        StopTimer("PlatformGameDbInterface_Delete");
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