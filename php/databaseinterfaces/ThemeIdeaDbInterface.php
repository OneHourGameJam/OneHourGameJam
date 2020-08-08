<?php

define("DB_TABLE_THEMEIDEA", "theme_ideas");

define("DB_COLUMN_THEMEIDEA_ID",           "idea_id");
define("DB_COLUMN_THEMEIDEA_DATETIME",     "idea_datetime");
define("DB_COLUMN_THEMEIDEA_IP",           "idea_ip");
define("DB_COLUMN_THEMEIDEA_USER_AGENT",   "idea_user_agent");
define("DB_COLUMN_THEMEIDEA_THEME_ID",     "idea_theme_id");
define("DB_COLUMN_THEMEIDEA_USER_ID",      "idea_user_id");
define("DB_COLUMN_THEMEIDEA_IDEAS",        "idea_ideas");

class ThemeIdeaDbInterface{
    private $database;
    private $publicColumns = Array(DB_COLUMN_THEMEIDEA_ID, DB_COLUMN_THEMEIDEA_THEME_ID, DB_COLUMN_THEMEIDEA_USER_ID);
    private $privateColumns = Array(DB_COLUMN_THEMEIDEA_DATETIME, DB_COLUMN_THEMEIDEA_IP, DB_COLUMN_THEMEIDEA_USER_AGENT, DB_COLUMN_THEMEIDEA_IDEAS);

    function __construct(&$database) {
        $this->database = $database;
    }

    public function SelectThemeIdeasByUser($userId){
        AddActionLog("ThemeIdeaDbInterface_SelectThemeIdeasByUser");
        StartTimer("ThemeIdeaDbInterface_SelectThemeIdeasByUser");
        
        $escapedUserId = $this->database->EscapeString($userId);
        $sql = "
            SELECT ".DB_COLUMN_THEMEIDEA_ID.", ".DB_COLUMN_THEMEIDEA_THEME_ID.", ".DB_COLUMN_THEMEIDEA_USER_ID.", ".DB_COLUMN_THEMEIDEA_IDEAS."
            FROM ".DB_TABLE_THEMEIDEA."
            WHERE ".DB_COLUMN_THEMEIDEA_USER_ID." = $escapedUserId
        ";
        
        StopTimer("ThemeIdeaDbInterface_SelectThemeIdeasByUser");
        return $this->database->Execute($sql);;
    }

    public function SelectAllThemeIdeasByUserWithTheme($userId){
        AddActionLog("ThemeIdeaDbInterface_SelectAllThemeIdeasByUserWithTheme");
        StartTimer("ThemeIdeaDbInterface_SelectAllThemeIdeasByUserWithTheme");
        
        $escapedUserId = $this->database->EscapeString($userId);
        $sql = "
            SELECT i.".DB_COLUMN_THEMEIDEA_ID.", i.".DB_COLUMN_THEMEIDEA_DATETIME.", i.".DB_COLUMN_THEMEIDEA_IP.", i.".DB_COLUMN_THEMEIDEA_USER_AGENT.", i.".DB_COLUMN_THEMEIDEA_THEME_ID.", t.".DB_COLUMN_THEME_TEXT.", i.".DB_COLUMN_THEMEIDEA_USER_ID.", i.".DB_COLUMN_THEMEIDEA_IDEAS."
            FROM ".DB_TABLE_THEMEIDEA." i, ".DB_TABLE_THEME." t
            WHERE i.".DB_COLUMN_THEMEIDEA_USER_ID." = $escapedUserId
              AND i.".DB_COLUMN_THEMEIDEA_THEME_ID." = t.".DB_COLUMN_THEME_ID."
        ";
        
        StopTimer("ThemeIdeaDbInterface_SelectAllThemeIdeasByUserWithTheme");
        return $this->database->Execute($sql);;
    }

    public function SelectSingle($themeId, $userId){
        AddActionLog("ThemeIdeaDbInterface_SelectSingle");
        StartTimer("ThemeIdeaDbInterface_SelectSingle");
        
        $escapedThemeId = $this->database->EscapeString($themeId);
        $escapedUserId = $this->database->EscapeString($userId);
        $sql = "
            SELECT ".DB_COLUMN_THEMEIDEA_ID." 
            FROM ".DB_TABLE_THEMEIDEA." 
            WHERE ".DB_COLUMN_THEMEIDEA_THEME_ID." = $escapedThemeId 
              AND ".DB_COLUMN_THEMEIDEA_USER_ID." = $escapedUserId";
        
        StopTimer("ThemeIdeaDbInterface_SelectSingle");
        return $this->database->Execute($sql);;
    }

    public function Insert($ip, $userAgent, $themeId, $userId, $ideas){
        AddActionLog("ThemeIdeaDbInterface_Insert");
        StartTimer("ThemeIdeaDbInterface_Insert");
        
        $escapedIp = $this->database->EscapeString($ip);
        $escapedUserAgent = $this->database->EscapeString($userAgent);
        $escapedThemeId = $this->database->EscapeString($themeId);
        $escapedUserId = $this->database->EscapeString($userId);
        $escapedIdeas = $this->database->EscapeString($ideas);

        $sql = "
            INSERT INTO ".DB_TABLE_THEMEIDEA."
            (".DB_COLUMN_THEMEIDEA_DATETIME.", ".DB_COLUMN_THEMEIDEA_IP.", ".DB_COLUMN_THEMEIDEA_USER_AGENT.", ".DB_COLUMN_THEMEIDEA_THEME_ID.", ".DB_COLUMN_THEMEIDEA_USER_ID.", ".DB_COLUMN_THEMEIDEA_IDEAS.")
            VALUES
            (Now(), '$escapedIp', '$escapedUserAgent', $escapedThemeId, $escapedUserId, '$escapedIdeas');";
        $this->database->Execute($sql);;

        StopTimer("ThemeIdeaDbInterface_Insert");
    }

    public function Update($ideaId, $ideas){
        AddActionLog("ThemeIdeaDbInterface_Update");
        StartTimer("ThemeIdeaDbInterface_Update");
        
        $escapedIdeaId = $this->database->EscapeString($ideaId);
        $escapedIdeas = $this->database->EscapeString($ideas);

        $sql = "
            UPDATE ".DB_TABLE_THEMEIDEA." 
            SET ".DB_COLUMN_THEMEIDEA_IDEAS." = '$escapedIdeas' 
            WHERE ".DB_COLUMN_THEMEIDEA_ID." = $escapedIdeaId";
        $this->database->Execute($sql);;

        StopTimer("ThemeIdeaDbInterface_Update");
    }

    public function SelectPublicData(){
        AddActionLog("ThemeIdeaDbInterface_SelectPublicData");
        StartTimer("ThemeIdeaDbInterface_SelectPublicData");

        $sql = "
            SELECT ".implode(",", $this->publicColumns)."
            FROM ".DB_TABLE_THEMEIDEA.";
        ";

        StopTimer("ThemeIdeaDbInterface_SelectPublicData");
        return $this->database->Execute($sql);;
    }

}

?>