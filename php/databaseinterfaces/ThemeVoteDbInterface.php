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
    private $database;
    private $publicColumns = Array(DB_COLUMN_THEMEVOTE_ID, DB_COLUMN_THEMEVOTE_THEME_ID, DB_COLUMN_THEMEVOTE_USER_ID);
    private $privateColumns = Array(DB_COLUMN_THEMEVOTE_DATETIME, DB_COLUMN_THEMEVOTE_IP, DB_COLUMN_THEMEVOTE_USER_AGENT, DB_COLUMN_THEMEVOTE_TYPE);

    function __construct(&$database) {
        $this->database = $database;
    }

    public function SelectThemeVotesByUser($userId){
        AddActionLog("ThemeVoteDbInterface_SelectThemeVotesByUser");
        StartTimer("ThemeVoteDbInterface_SelectThemeVotesByUser");
    
        $escapedUserId = $this->database->EscapeString($userId);
        $sql = "
            SELECT ".DB_COLUMN_THEMEVOTE_THEME_ID.", ".DB_COLUMN_THEMEVOTE_TYPE."
            FROM ".DB_TABLE_THEMEVOTE."
            WHERE ".DB_COLUMN_THEMEVOTE_USER_ID." = $escapedUserId;
        ";
        
        StopTimer("ThemeVoteDbInterface_SelectThemeVotesByUser");
        return $this->database->Execute($sql);
    }

    public function SelectThemeVotesWithThemeByUser($userId){
        AddActionLog("ThemeVoteDbInterface_SelectThemeVotesWithThemeByUser");
        StartTimer("ThemeVoteDbInterface_SelectThemeVotesWithThemeByUser");
        
        $escapedUserId = $this->database->EscapeString($userId);
        $sql = "
            SELECT ".DB_TABLE_THEME.".".DB_COLUMN_THEME_TEXT.", ".DB_TABLE_THEMEVOTE.".*, IF(".DB_TABLE_THEMEVOTE.".".DB_COLUMN_THEMEVOTE_TYPE." = 1, '-1', IF(".DB_TABLE_THEMEVOTE.".".DB_COLUMN_THEMEVOTE_TYPE." = 2, '0', '+1'))
            FROM ".DB_TABLE_THEMEVOTE.", ".DB_TABLE_THEME."
            WHERE ".DB_TABLE_THEME.".".DB_COLUMN_THEME_ID." = ".DB_TABLE_THEMEVOTE.".".DB_COLUMN_THEMEVOTE_THEME_ID."
              AND ".DB_COLUMN_THEMEVOTE_USER_ID." = $escapedUserId;
        ";
        
        StopTimer("ThemeVoteDbInterface_SelectThemeVotesWithThemeByUser");
        return $this->database->Execute($sql);
    }

    public function SelectSingle($themeId, $userId){
        AddActionLog("ThemeVoteDbInterface_SelectSingle");
        StartTimer("ThemeVoteDbInterface_SelectSingle");
        
        $escapedThemeId = $this->database->EscapeString($themeId);
        $escapedUserId = $this->database->EscapeString($userId);
        $sql = "
            SELECT ".DB_COLUMN_THEMEVOTE_ID." 
            FROM ".DB_TABLE_THEMEVOTE." 
            WHERE ".DB_COLUMN_THEMEVOTE_THEME_ID." = $escapedThemeId 
              AND ".DB_COLUMN_THEMEVOTE_USER_ID." = $escapedUserId";
        
        StopTimer("ThemeVoteDbInterface_SelectSingle");
        return $this->database->Execute($sql);
    }

    public function Insert($ip, $userAgent, $themeId, $userId, $vote){
        AddActionLog("ThemeVoteDbInterface_Insert");
        StartTimer("ThemeVoteDbInterface_Insert");
        
        $escapedIp = $this->database->EscapeString($ip);
        $escapedUserAgent = $this->database->EscapeString($userAgent);
        $escapedThemeId = $this->database->EscapeString($themeId);
        $escapedUserId = $this->database->EscapeString($userId);
        $escapedVote = $this->database->EscapeString($vote);
        $sql = "
            INSERT INTO ".DB_TABLE_THEMEVOTE."
            (".DB_COLUMN_THEMEVOTE_DATETIME.", ".DB_COLUMN_THEMEVOTE_IP.", ".DB_COLUMN_THEMEVOTE_USER_AGENT.", ".DB_COLUMN_THEMEVOTE_THEME_ID.", ".DB_COLUMN_THEMEVOTE_USER_ID.", ".DB_COLUMN_THEMEVOTE_TYPE.")
            VALUES
            (Now(), '$escapedIp', '$escapedUserAgent', $escapedThemeId, $escapedUserId, $escapedVote);";
        $this->database->Execute($sql);
        
        StopTimer("ThemeVoteDbInterface_Insert");
    }

    public function Update($themeVoteId, $vote){
        AddActionLog("ThemeVoteDbInterface_Update");
        StartTimer("ThemeVoteDbInterface_Update");
        
        $escapedThemeVoteId = $this->database->EscapeString($themeVoteId);
        $escapedVote = $this->database->EscapeString($vote);
        $sql = "
            UPDATE ".DB_TABLE_THEMEVOTE." 
            SET ".DB_COLUMN_THEMEVOTE_TYPE." = $escapedVote 
            WHERE ".DB_COLUMN_THEMEVOTE_ID." = $escapedThemeVoteId";
        $this->database->Execute($sql);
        
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
        return $this->database->Execute($sql);
    }

}

?>