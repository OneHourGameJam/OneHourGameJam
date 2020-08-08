<?php
define("DB_TABLE_PLATFORMENTRY", "platform_entry");

define("DB_COLUMN_PLATFORMENTRY_ID",            "platformentry_id");
define("DB_COLUMN_PLATFORMENTRY_ENTRY_ID",      "platformentry_entry_id");
define("DB_COLUMN_PLATFORMENTRY_PLATFORM_ID",   "platformentry_platform_id");
define("DB_COLUMN_PLATFORMENTRY_URL",           "platformentry_url");

class PlatformGameDbInterface{
    private $database;
    private $publicColumns = Array(DB_COLUMN_PLATFORMENTRY_ID, DB_COLUMN_PLATFORMENTRY_ENTRY_ID, DB_COLUMN_PLATFORMENTRY_PLATFORM_ID, DB_COLUMN_PLATFORMENTRY_URL);
    private $privateColumns = Array();

    function __construct(&$database) {
        $this->database = $database;
    }

    public function SelectAll(){
        AddActionLog("PlatformGameDbInterface_SelectAll");
        StartTimer("PlatformGameDbInterface_SelectAll");

        $sql = "
            SELECT ".DB_COLUMN_PLATFORMENTRY_ID.", ".DB_COLUMN_PLATFORMENTRY_ENTRY_ID.", ".DB_COLUMN_PLATFORMENTRY_PLATFORM_ID.", ".DB_COLUMN_PLATFORMENTRY_URL." 
            FROM ".DB_TABLE_PLATFORMENTRY.";";
        
        StopTimer("PlatformGameDbInterface_SelectAll");
        return $this->database->Execute($sql);;
    }

    public function SelectSinglePlatformEntryId($entryId, $platformId){
        AddActionLog("PlatformGameDbInterface_SelectSinglePlatformEntryId");
        StartTimer("PlatformGameDbInterface_SelectSinglePlatformEntryId");

        $escapedEntryId = $this->database->EscapeString($entryId);
        $escapedPlatformId = $this->database->EscapeString($platformId);
    
        $sql = "
            SELECT ".DB_COLUMN_PLATFORMENTRY_ID." 
            FROM ".DB_TABLE_PLATFORMENTRY." 
            WHERE ".DB_COLUMN_PLATFORMENTRY_ENTRY_ID." = $escapedEntryId 
              AND ".DB_COLUMN_PLATFORMENTRY_PLATFORM_ID." = $escapedPlatformId;";
        
        StopTimer("PlatformGameDbInterface_SelectSinglePlatformEntryId");
        return $this->database->Execute($sql);;
    }

    public function Insert($entryId, $platformId, $url){
        AddActionLog("PlatformGameDbInterface_Insert");
        StartTimer("PlatformGameDbInterface_Insert");
    
        $escapedEntryId = $this->database->EscapeString($entryId);
        $escapedPlatformId = $this->database->EscapeString($platformId);
        $escapedUrl = $this->database->EscapeString($url);

        $sql = "
            INSERT INTO ".DB_TABLE_PLATFORMENTRY."
            (".DB_COLUMN_PLATFORMENTRY_ID.", ".DB_COLUMN_PLATFORMENTRY_ENTRY_ID.", ".DB_COLUMN_PLATFORMENTRY_PLATFORM_ID.", ".DB_COLUMN_PLATFORMENTRY_URL.")
            VALUES
            (null, $escapedEntryId, $escapedPlatformId, '$escapedUrl');
        ";
        $data = $this->database->Execute($sql);;
        
        StopTimer("PlatformGameDbInterface_Insert");
    }

    public function UpdateUrl($platformEntryId, $url){
        AddActionLog("PlatformGameDbInterface_UpdateUrl");
        StartTimer("PlatformGameDbInterface_UpdateUrl");
    
        $escapedUrl = $this->database->EscapeString($url);
        $escapedPlatformEntryId = $this->database->EscapeString($platformEntryId);

		$sql = "
            UPDATE ".DB_TABLE_PLATFORMENTRY." 
            SET ".DB_COLUMN_PLATFORMENTRY_URL." = '$escapedUrl' 
            WHERE ".DB_COLUMN_PLATFORMENTRY_ID." = $escapedPlatformEntryId;";
        $data = $this->database->Execute($sql);;
        
        StopTimer("PlatformGameDbInterface_UpdateUrl");
    }

    public function Delete($platformEntryId){
        AddActionLog("PlatformGameDbInterface_Delete");
        StartTimer("PlatformGameDbInterface_Delete");
    
        $escapedPlatformEntryId = $this->database->EscapeString($platformEntryId);

        $sql = "
            DELETE FROM ".DB_TABLE_PLATFORMENTRY." 
            WHERE ".DB_COLUMN_PLATFORMENTRY_ID." = $escapedPlatformEntryId;";
        $data = $this->database->Execute($sql);;
        
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
        return $this->database->Execute($sql);;
    }
}

?>