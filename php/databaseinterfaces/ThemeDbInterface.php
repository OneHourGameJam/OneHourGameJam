<?php

define("DB_TABLE_THEME", "theme");

define("DB_COLUMN_THEME_ID",                "theme_id");
define("DB_COLUMN_THEME_DATETIME",          "theme_datetime");
define("DB_COLUMN_THEME_IP",                "theme_ip");
define("DB_COLUMN_THEME_USER_AGENT",        "theme_user_agent");
define("DB_COLUMN_THEME_TEXT",              "theme_text");
define("DB_COLUMN_THEME_AUTHOR_USER_ID",    "theme_author_user_id");
define("DB_COLUMN_THEME_BANNED",            "theme_banned");
define("DB_COLUMN_THEME_DELETED",           "theme_deleted");

class ThemeDbInterface{
    private $dbConnection;
    private $publicColumns = Array(DB_COLUMN_THEME_ID, DB_COLUMN_THEME_TEXT, DB_COLUMN_THEME_AUTHOR_USER_ID, DB_COLUMN_THEME_BANNED, DB_COLUMN_THEME_DELETED);
    private $privateColumns = Array(DB_COLUMN_THEME_DATETIME, DB_COLUMN_THEME_IP, DB_COLUMN_THEME_USER_AGENT);

    function __construct(&$dbConn) {
        $this->dbConnection = $dbConn;
    }

    public function SelectAllWithResults(){
        AddActionLog("ThemeDbInterface_SelectAllWithResults");
        StartTimer("ThemeDbInterface_SelectAllWithResults");
    
        //Fill list of themes - will return same theme row multiple times (once for each valid themevote_type)
        $sql = "
            SELECT ".DB_COLUMN_THEME_ID.", ".DB_COLUMN_THEME_TEXT.", ".DB_COLUMN_THEME_AUTHOR_USER_ID.", ".DB_COLUMN_THEME_BANNED.", ".DB_COLUMN_THEMEVOTE_TYPE.", count(".DB_COLUMN_THEMEVOTE_ID.") AS themevote_count, DATEDIFF(Now(), ".DB_COLUMN_THEME_DATETIME.") as theme_daysago, ".DB_COLUMN_THEME_DELETED."
            FROM (
                ".DB_TABLE_THEME." LEFT JOIN ".DB_TABLE_THEMEVOTE." 
                ON (".DB_TABLE_THEMEVOTE.".".DB_COLUMN_THEMEVOTE_THEME_ID." = ".DB_TABLE_THEME.".".DB_COLUMN_THEME_ID.")
            )
            WHERE ".DB_COLUMN_THEME_DELETED." != 1
            GROUP BY ".DB_COLUMN_THEME_ID.", ".DB_COLUMN_THEMEVOTE_TYPE."
            ORDER BY ".DB_COLUMN_THEME_BANNED." ASC, ".DB_COLUMN_THEME_ID." ASC
        ";
        
        StopTimer("ThemeDbInterface_SelectAllWithResults");
        return mysqli_query($this->dbConnection, $sql);
    }

    public function SelectThemesByUser($userId){
        AddActionLog("ThemeDbInterface_SelectThemesByUser");
        StartTimer("ThemeDbInterface_SelectThemesByUser");
    
        $escapedUserId = mysqli_real_escape_string($this->dbConnection, $userId);
        $sql = "
            SELECT *
            FROM ".DB_TABLE_THEME."
            WHERE ".DB_COLUMN_THEME_AUTHOR_USER_ID." = $escapedUserId;
        ";
        
        StopTimer("ThemeDbInterface_SelectThemesByUser");
        return mysqli_query($this->dbConnection, $sql);
    }

    public function SoftDelete($themeId){
        AddActionLog("ThemeDbInterface_SoftDelete");
        StartTimer("ThemeDbInterface_SoftDelete");

        $escapedThemeId = mysqli_real_escape_string($this->dbConnection, $themeId);
        $sql = "
            UPDATE ".DB_TABLE_THEME."
            SET ".DB_COLUMN_THEME_DELETED." = 1
            WHERE ".DB_COLUMN_THEME_ID." = $escapedThemeId;
        ";
        
        StopTimer("ThemeDbInterface_SoftDelete");
        return mysqli_query($this->dbConnection, $sql);
    }

    public function SelectPublicData(){
        AddActionLog("ThemeDbInterface_SelectPublicData");
        StartTimer("ThemeDbInterface_SelectPublicData");

        $sql = "
            SELECT ".implode(",", $this->publicColumns)."
            FROM ".DB_TABLE_THEME.";
        ";

        StopTimer("ThemeDbInterface_SelectPublicData");
        return mysqli_query($this->dbConnection, $sql);
    }

}

?>