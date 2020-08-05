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
    private $dbConnection;
    private $publicColumns = Array(DB_COLUMN_THEMEIDEA_ID, DB_COLUMN_THEMEIDEA_THEME_ID, DB_COLUMN_THEMEIDEA_USER_ID);
    private $privateColumns = Array(DB_COLUMN_THEMEIDEA_DATETIME, DB_COLUMN_THEMEIDEA_IP, DB_COLUMN_THEMEIDEA_USER_AGENT, DB_COLUMN_THEMEIDEA_IDEAS);

    function __construct(&$dbConn) {
        $this->dbConnection = $dbConn;
    }

    public function SelectThemeIdeasByUser($userId){
        AddActionLog("ThemeIdeaDbInterface_SelectThemeIdeasByUser");
        StartTimer("ThemeIdeaDbInterface_SelectThemeIdeasByUser");
        
        $escapedUserId = mysqli_real_escape_string($this->dbConnection, $userId);
        $sql = "
            SELECT ".DB_COLUMN_THEMEIDEA_ID.", ".DB_COLUMN_THEMEIDEA_THEME_ID.", ".DB_COLUMN_THEMEIDEA_USER_ID.", ".DB_COLUMN_THEMEIDEA_IDEAS."
            FROM ".DB_TABLE_THEMEIDEA."
            WHERE ".DB_COLUMN_THEMEIDEA_USER_ID." = $escapedUserId
        ";
        
        StopTimer("ThemeIdeaDbInterface_SelectThemeIdeasByUser");
        return mysqli_query($this->dbConnection, $sql);
    }

    public function SelectAllThemeIdeasByUserWithTheme($userId){
        AddActionLog("ThemeIdeaDbInterface_SelectAllThemeIdeasByUserWithTheme");
        StartTimer("ThemeIdeaDbInterface_SelectAllThemeIdeasByUserWithTheme");
        
        $escapedUserId = mysqli_real_escape_string($this->dbConnection, $userId);
        $sql = "
            SELECT i.".DB_COLUMN_THEMEIDEA_ID.", i.".DB_COLUMN_THEMEIDEA_DATETIME.", i.".DB_COLUMN_THEMEIDEA_IP.", i.".DB_COLUMN_THEMEIDEA_USER_AGENT.", i.".DB_COLUMN_THEMEIDEA_THEME_ID.", t.".DB_COLUMN_THEME_TEXT.", i.".DB_COLUMN_THEMEIDEA_USER_ID.", i.".DB_COLUMN_THEMEIDEA_IDEAS."
            FROM ".DB_TABLE_THEMEIDEA." i, ".DB_TABLE_THEME." t
            WHERE i.".DB_COLUMN_THEMEIDEA_USER_ID." = $escapedUserId
              AND i.".DB_COLUMN_THEMEIDEA_THEME_ID." = t.".DB_COLUMN_THEME_ID."
        ";
        
        StopTimer("ThemeIdeaDbInterface_SelectAllThemeIdeasByUserWithTheme");
        return mysqli_query($this->dbConnection, $sql);
    }

    public function SelectSingle($themeId, $userId){
        AddActionLog("ThemeIdeaDbInterface_SelectSingle");
        StartTimer("ThemeIdeaDbInterface_SelectSingle");
        
        $escapedThemeId = mysqli_real_escape_string($this->dbConnection, $themeId);
        $escapedUserId = mysqli_real_escape_string($this->dbConnection, $userId);
        $sql = "
            SELECT idea_id 
            FROM theme_ideas 
            WHERE idea_theme_id = $escapedThemeId 
              AND idea_user_id = $escapedUserId";
        
        StopTimer("ThemeIdeaDbInterface_SelectSingle");
        return mysqli_query($this->dbConnection, $sql);
    }

    public function Insert($ip, $userAgent, $themeId, $userId, $ideas){
        AddActionLog("ThemeIdeaDbInterface_Insert");
        StartTimer("ThemeIdeaDbInterface_Insert");
        
        $escapedIp = mysqli_real_escape_string($this->dbConnection, $ip);
        $escapedUserAgent = mysqli_real_escape_string($this->dbConnection, $userAgent);
        $escapedThemeId = mysqli_real_escape_string($this->dbConnection, $themeId);
        $escapedUserId = mysqli_real_escape_string($this->dbConnection, $userId);
        $escapedIdeas = mysqli_real_escape_string($this->dbConnection, $ideas);

        $sql = "
            INSERT INTO theme_ideas
            (idea_datetime, idea_ip, idea_user_agent, idea_theme_id, idea_user_id, idea_ideas)
            VALUES
            (Now(), '$escapedIp', '$escapedUserAgent', $escapedThemeId, $escapedUserId, '$escapedIdeas');";
        mysqli_query($this->dbConnection, $sql);

        StopTimer("ThemeIdeaDbInterface_Insert");
    }

    public function Update($ideaId, $ideas){
        AddActionLog("ThemeIdeaDbInterface_Update");
        StartTimer("ThemeIdeaDbInterface_Update");
        
        $escapedIdeaId = mysqli_real_escape_string($this->dbConnection, $ideaId);
        $escapedIdeas = mysqli_real_escape_string($this->dbConnection, $ideas);

        $sql = "
            UPDATE theme_ideas 
            SET idea_ideas = '$escapedIdeas' 
            WHERE idea_id = $escapedIdeaId";
        mysqli_query($this->dbConnection, $sql);

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
        return mysqli_query($this->dbConnection, $sql);
    }

}

?>