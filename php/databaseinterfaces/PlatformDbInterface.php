<?php
define("DB_TABLE_PLATFORM", "platform");

define("DB_COLUMN_PLATFORM_ID",         "platform_id");
define("DB_COLUMN_PLATFORM_NAME",       "platform_name");
define("DB_COLUMN_PLATFORM_ICON_URL",   "platform_icon_url");
define("DB_COLUMN_PLATFORM_DELETED",    "platform_deleted");

class PlatformDbInterface{
    private $database;
    private $publicColumns = Array(DB_COLUMN_PLATFORM_ID, DB_COLUMN_PLATFORM_NAME, DB_COLUMN_PLATFORM_ICON_URL, DB_COLUMN_PLATFORM_DELETED);
    private $privateColumns = Array();

    function __construct(&$database) {
        $this->database = $database;
    }

    public function SelectAll(){
        AddActionLog("PlatformDbInterface_SelectAll");
        StartTimer("PlatformDbInterface_SelectAll");

        $sql = "
            SELECT ".DB_COLUMN_PLATFORM_ID.", ".DB_COLUMN_PLATFORM_NAME.", ".DB_COLUMN_PLATFORM_ICON_URL.", ".DB_COLUMN_PLATFORM_DELETED."
            FROM ".DB_TABLE_PLATFORM.";";
        
        StopTimer("PlatformDbInterface_SelectAll");
        return $this->database->Execute($sql);;
    }

    public function Insert($platformName, $iconUrl){
        AddActionLog("PlatformDbInterface_Insert");
        StartTimer("PlatformDbInterface_Insert");

        $escapedPlatformName = $this->database->EscapeString($platformName);
        $escapedIconUrl = $this->database->EscapeString($iconUrl);
    
        $sql = "
            INSERT INTO ".DB_TABLE_PLATFORM."
            (".DB_COLUMN_PLATFORM_ID.",
            ".DB_COLUMN_PLATFORM_NAME.",
            ".DB_COLUMN_PLATFORM_ICON_URL.",
            ".DB_COLUMN_PLATFORM_DELETED.")
            VALUES
            (null,
            '$escapedPlatformName',
            '$escapedIconUrl',
            0);
        ";
        $data = $this->database->Execute($sql);;
        
        StopTimer("PlatformDbInterface_Insert");
    }

    public function Update($platformId, $platformName, $iconUrl){
        AddActionLog("PlatformDbInterface_Update");
        StartTimer("PlatformDbInterface_Update");

        $escapedPlatformId = $this->database->EscapeString($platformId);
        $escapedPlatformName = $this->database->EscapeString($platformName);
        $escapedIconUrl = $this->database->EscapeString($iconUrl);
    
        $sql = "
            UPDATE ".DB_TABLE_PLATFORM."
            SET
                ".DB_COLUMN_PLATFORM_NAME." = '$escapedPlatformName',
                ".DB_COLUMN_PLATFORM_ICON_URL." = '$escapedIconUrl'
            WHERE
                ".DB_COLUMN_PLATFORM_ID." = $escapedPlatformId;
        ";
        $data = $this->database->Execute($sql);;
        
        StopTimer("PlatformDbInterface_Update");
    }

    public function SoftDelete($platformId){
        AddActionLog("PlatformDbInterface_SoftDelete");
        StartTimer("PlatformDbInterface_SoftDelete");

        $escapedPlatformId = $this->database->EscapeString($platformId);
    
        $sql = " 
            UPDATE ".DB_TABLE_PLATFORM."
            SET
                ".DB_COLUMN_PLATFORM_DELETED." = 1
            WHERE
                ".DB_COLUMN_PLATFORM_ID." = $escapedPlatformId;
        ";
        $data = $this->database->Execute($sql);;
        
        StopTimer("PlatformDbInterface_SoftDelete");
    }

    public function RestoreSoftDeleted($platformId){
        AddActionLog("PlatformDbInterface_RestoreSoftDeleted");
        StartTimer("PlatformDbInterface_RestoreSoftDeleted");

        $escapedPlatformId = $this->database->EscapeString($platformId);
    
        $sql = "
            UPDATE ".DB_TABLE_PLATFORM." 
            SET
                ".DB_COLUMN_PLATFORM_DELETED." = 0
            WHERE
                ".DB_COLUMN_PLATFORM_ID." = $escapedPlatformId;
        ";
        $data = $this->database->Execute($sql);;
        
        StopTimer("PlatformDbInterface_RestoreSoftDeleted");
    }

    public function SelectPublicData(){
        AddActionLog("PlatformDbInterface_SelectPublicData");
        StartTimer("PlatformDbInterface_SelectPublicData");

        $sql = "
            SELECT ".implode(",", $this->publicColumns)."
            FROM ".DB_TABLE_PLATFORM.";
        ";

        StopTimer("PlatformDbInterface_SelectPublicData");
        return $this->database->Execute($sql);;
    }
}

?>