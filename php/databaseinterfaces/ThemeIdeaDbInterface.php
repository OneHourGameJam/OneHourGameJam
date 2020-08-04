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