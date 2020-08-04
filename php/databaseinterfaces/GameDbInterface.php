<?php
define("DB_TABLE_ENTRY", "entry");

define("DB_COLUMN_ENTRY_ID",                "entry_id");
define("DB_COLUMN_ENTRY_DATETIME",          "entry_datetime");
define("DB_COLUMN_ENTRY_IP",                "entry_ip");
define("DB_COLUMN_ENTRY_USER_AGENT",        "entry_user_agent");
define("DB_COLUMN_ENTRY_JAM_ID",            "entry_jam_id");
define("DB_COLUMN_ENTRY_JAM_NUMBER",        "entry_jam_number");
define("DB_COLUMN_ENTRY_TITLE",             "entry_title");
define("DB_COLUMN_ENTRY_DESCRIPTION",       "entry_description");
define("DB_COLUMN_ENTRY_AUTHOR_USER_ID",    "entry_author_user_id");
define("DB_COLUMN_ENTRY_SCREENSHOT_URL",    "entry_screenshot_url");
define("DB_COLUMN_ENTRY_BACKGROUND_COLOR",  "entry_background_color");
define("DB_COLUMN_ENTRY_TEXT_COLOR",        "entry_text_color");
define("DB_COLUMN_ENTRY_DELETED",           "entry_deleted");

class GameDbInterface{
    private $dbConnection;
    private $publicColumns = Array(DB_COLUMN_ENTRY_ID, DB_COLUMN_ENTRY_JAM_ID, DB_COLUMN_ENTRY_JAM_NUMBER, DB_COLUMN_ENTRY_TITLE, DB_COLUMN_ENTRY_DESCRIPTION, DB_COLUMN_ENTRY_AUTHOR_USER_ID, DB_COLUMN_ENTRY_SCREENSHOT_URL, DB_COLUMN_ENTRY_BACKGROUND_COLOR, DB_COLUMN_ENTRY_TEXT_COLOR, DB_COLUMN_ENTRY_DELETED);
    private $privateColumns = Array(DB_COLUMN_ENTRY_DATETIME, DB_COLUMN_ENTRY_IP, DB_COLUMN_ENTRY_USER_AGENT);

    function __construct(&$dbConn) {
        $this->dbConnection = $dbConn;
    }

    public function SelectAll(){
        AddActionLog("GameDbInterface_SelectAll");
        StartTimer("GameDbInterface_SelectAll");

        $sql = "
            SELECT ".DB_COLUMN_ENTRY_ID.", ".DB_COLUMN_ENTRY_JAM_ID.", ".DB_COLUMN_ENTRY_JAM_NUMBER.", ".DB_COLUMN_ENTRY_TITLE.", ".DB_COLUMN_ENTRY_DESCRIPTION.", ".DB_COLUMN_ENTRY_AUTHOR_USER_ID.", ".DB_COLUMN_ENTRY_SCREENSHOT_URL.", ".DB_COLUMN_ENTRY_BACKGROUND_COLOR.", ".DB_COLUMN_ENTRY_TEXT_COLOR.", ".DB_COLUMN_ENTRY_DELETED."
            FROM ".DB_TABLE_ENTRY."
            ORDER BY ".DB_COLUMN_ENTRY_ID." DESC";
        
        StopTimer("GameDbInterface_SelectAll");
        return mysqli_query($this->dbConnection, $sql);
    }

    public function SelectIfExists($entryId){
        AddActionLog("GameDbInterface_SelectIfExists");
        StartTimer("GameDbInterface_SelectIfExists");

        $escapedEntryID = mysqli_real_escape_string($this->dbConnection, intval($entryId));
        $sql = "
            SELECT 1
            FROM ".DB_TABLE_ENTRY."
            WHERE ".DB_COLUMN_ENTRY_ID." = $escapedEntryID
              AND ".DB_COLUMN_ENTRY_DELETED." = 0;";
        
        StopTimer("GameDbInterface_SelectIfExists");
        return mysqli_query($this->dbConnection, $sql);
    }

    public function SelectEntriesForAuthor($authorUserId){
        AddActionLog("GameDbInterface_SelectEntriesForAuthor");
        StartTimer("GameDbInterface_SelectEntriesForAuthor");
    
        $escapedAuthorUserId = mysqli_real_escape_string($this->dbConnection, $authorUserId);
        $sql = "
            SELECT *
            FROM ".DB_TABLE_ENTRY."
            WHERE ".DB_COLUMN_ENTRY_AUTHOR_USER_ID." = '$escapedAuthorUserId';
        ";
        
        StopTimer("GameDbInterface_SelectEntriesForAuthor");
        return mysqli_query($this->dbConnection, $sql);
    }

    public function SelectPublicData(){
        AddActionLog("GameDbInterface_SelectPublicData");
        StartTimer("GameDbInterface_SelectPublicData");

        $sql = "
            SELECT ".implode(",", $this->publicColumns)."
            FROM ".DB_TABLE_ENTRY.";
        ";

        StopTimer("GameDbInterface_SelectPublicData");
        return mysqli_query($this->dbConnection, $sql);
    }
}

?>