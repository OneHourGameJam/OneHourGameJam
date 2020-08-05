<?php

define("DB_TABLE_THEMEVOTE", "themevote");

define("DB_COLUMN_THEMEVOTE_ID",            "themevote_id");
define("DB_COLUMN_THEMEVOTE_DATETIME",      "themevote_datetime");
define("DB_COLUMN_THEMEVOTE_IP",            "themevote_ip");
define("DB_COLUMN_THEMEVOTE_USER_AGENT",    "themevote_user_agent");
define("DB_COLUMN_THEMEVOTE_THEME_ID",      "themevote_theme_id");
define("DB_COLUMN_THEMEVOTE_USER_ID",       "themevote_user_id");
define("DB_COLUMN_THEMEVOTE_TYPE",          "themevote_type");

class ThemeVoteDbInterface{
    private $dbConnection;
    private $publicColumns = Array(DB_COLUMN_THEMEVOTE_ID, DB_COLUMN_THEMEVOTE_THEME_ID, DB_COLUMN_THEMEVOTE_USER_ID);
    private $privateColumns = Array(DB_COLUMN_THEMEVOTE_DATETIME, DB_COLUMN_THEMEVOTE_IP, DB_COLUMN_THEMEVOTE_USER_AGENT, DB_COLUMN_THEMEVOTE_TYPE);

    function __construct(&$dbConn) {
        $this->dbConnection = $dbConn;
    }

    public function SelectThemeVotesByUser($userId){
        AddActionLog("ThemeVoteDbInterface_SelectThemeVotesByUser");
        StartTimer("ThemeVoteDbInterface_SelectThemeVotesByUser");
    
        $escapedUserId = mysqli_real_escape_string($this->dbConnection, $userId);
        $sql = "
            SELECT ".DB_COLUMN_THEMEVOTE_THEME_ID.", ".DB_COLUMN_THEMEVOTE_TYPE."
            FROM ".DB_TABLE_THEMEVOTE."
            WHERE ".DB_COLUMN_THEMEVOTE_USER_ID." = $escapedUserId;
        ";
        
        StopTimer("ThemeVoteDbInterface_SelectThemeVotesByUser");
        return mysqli_query($this->dbConnection, $sql);
    }

    public function SelectThemeVotesWithThemeByUser($userId){
        AddActionLog("ThemeVoteDbInterface_SelectThemeVotesWithThemeByUser");
        StartTimer("ThemeVoteDbInterface_SelectThemeVotesWithThemeByUser");
        
        $escapedUserId = mysqli_real_escape_string($this->dbConnection, $userId);
        $sql = "
            SELECT ".DB_TABLE_THEME.".".DB_COLUMN_THEME_TEXT.", ".DB_TABLE_THEMEVOTE.".*, IF(".DB_TABLE_THEMEVOTE.".".DB_COLUMN_THEMEVOTE_TYPE." = 1, '-1', IF(".DB_TABLE_THEMEVOTE.".".DB_COLUMN_THEMEVOTE_TYPE." = 2, '0', '+1'))
            FROM ".DB_TABLE_THEMEVOTE.", ".DB_TABLE_THEME."
            WHERE ".DB_TABLE_THEME.".".DB_COLUMN_THEME_ID." = ".DB_TABLE_THEMEVOTE.".".DB_COLUMN_THEMEVOTE_THEME_ID."
              AND ".DB_COLUMN_THEMEVOTE_USER_ID." = $escapedUserId;
        ";
        
        StopTimer("ThemeVoteDbInterface_SelectThemeVotesWithThemeByUser");
        return mysqli_query($this->dbConnection, $sql);
    }

    public function SelectSingle($themeId, $userId){
        AddActionLog("ThemeVoteDbInterface_SelectSingle");
        StartTimer("ThemeVoteDbInterface_SelectSingle");
        
        $escapedThemeId = mysqli_real_escape_string($this->dbConnection, $themeId);
        $escapedUserId = mysqli_real_escape_string($this->dbConnection, $userId);
        $sql = "
            SELECT ".DB_COLUMN_THEMEVOTE_ID." 
            FROM ".DB_TABLE_THEMEVOTE." 
            WHERE ".DB_COLUMN_THEMEVOTE_THEME_ID." = $escapedThemeId 
              AND ".DB_COLUMN_THEMEVOTE_USER_ID." = $escapedUserId";
        
        StopTimer("ThemeVoteDbInterface_SelectSingle");
        return mysqli_query($this->dbConnection, $sql);
    }

    public function Insert($ip, $userAgent, $themeId, $userId, $vote){
        AddActionLog("ThemeVoteDbInterface_Insert");
        StartTimer("ThemeVoteDbInterface_Insert");
        
        $escapedIp = mysqli_real_escape_string($this->dbConnection, $ip);
        $escapedUserAgent = mysqli_real_escape_string($this->dbConnection, $userAgent);
        $escapedThemeId = mysqli_real_escape_string($this->dbConnection, $themeId);
        $escapedUserId = mysqli_real_escape_string($this->dbConnection, $userId);
        $escapedVote = mysqli_real_escape_string($this->dbConnection, $vote);
        $sql = "
            INSERT INTO ".DB_TABLE_THEMEVOTE."
            (".DB_COLUMN_THEMEVOTE_DATETIME.", ".DB_COLUMN_THEMEVOTE_IP.", ".DB_COLUMN_THEMEVOTE_USER_AGENT.", ".DB_COLUMN_THEMEVOTE_THEME_ID.", ".DB_COLUMN_THEMEVOTE_USER_ID.", ".DB_COLUMN_THEMEVOTE_TYPE.")
            VALUES
            (Now(), '$escapedIp', '$escapedUserAgent', $escapedThemeId, $escapedUserId, $escapedVote);";
        mysqli_query($this->dbConnection, $sql);
        
        StopTimer("ThemeVoteDbInterface_Insert");
    }

    public function Update($themeVoteId, $vote){
        AddActionLog("ThemeVoteDbInterface_Update");
        StartTimer("ThemeVoteDbInterface_Update");
        
        $escapedThemeVoteId = mysqli_real_escape_string($this->dbConnection, $themeVoteId);
        $escapedVote = mysqli_real_escape_string($this->dbConnection, $vote);
        $sql = "
            UPDATE ".DB_TABLE_THEMEVOTE." 
            SET ".DB_COLUMN_THEMEVOTE_TYPE." = $escapedVote 
            WHERE ".DB_COLUMN_THEMEVOTE_ID." = $escapedThemeVoteId";
        mysqli_query($this->dbConnection, $sql);
        
        StopTimer("ThemeVoteDbInterface_Update");
    }

    public function SelectPublicData(){
        AddActionLog("ThemeVoteDbInterface_SelectPublicData");
        StartTimer("ThemeVoteDbInterface_SelectPublicData");

        $sql = "
            SELECT ".implode(",", $this->publicColumns)."
            FROM ".DB_TABLE_THEMEVOTE.";
        ";

        StopTimer("ThemeVoteDbInterface_SelectPublicData");
        return mysqli_query($this->dbConnection, $sql);
    }

}

?>