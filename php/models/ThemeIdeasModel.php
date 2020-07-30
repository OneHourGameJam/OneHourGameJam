<?php
define("DB_TABLE_THEMEIDEA", "theme_ideas");

define("DB_COLUMN_THEMEIDEA_ID",           "idea_id");
define("DB_COLUMN_THEMEIDEA_DATETIME",     "idea_datetime");
define("DB_COLUMN_THEMEIDEA_IP",           "idea_ip");
define("DB_COLUMN_THEMEIDEA_USER_AGENT",   "idea_user_agent");
define("DB_COLUMN_THEMEIDEA_THEME_ID",     "idea_theme_id");
define("DB_COLUMN_THEMEIDEA_USER_ID",      "idea_user_id");
define("DB_COLUMN_THEMEIDEA_IDEAS",        "idea_ideas");

class ThemeIdeasModel
{
    public $Id;
    public $ThemeId;
    public $UserId;
    public $Ideas;
}

class ThemeIdeasData{
    public $ThemeIdeas;

    private $dbConnection;
    private $publicColumns = Array(DB_COLUMN_THEMEIDEA_ID, DB_COLUMN_THEMEIDEA_THEME_ID, DB_COLUMN_THEMEIDEA_USER_ID);
    private $privateColumns = Array(DB_COLUMN_THEMEIDEA_DATETIME, DB_COLUMN_THEMEIDEA_IP, DB_COLUMN_THEMEIDEA_USER_AGENT, DB_COLUMN_THEMEIDEA_IDEAS);

    function __construct(&$dbConn, &$loggedInUser) {
        $this->dbConnection = $dbConn;
        $this->ThemeIdeas = $this->LoadThemeIdeasForUser($loggedInUser);
    }

//////////////////////// MODEL CONSTRUCTOR

    function LoadThemeIdeasForUser(&$loggedInUser){
        AddActionLog("LoadThemeIdeasForUser");
        StartTimer("LoadThemeIdeasForUser");
    
        $themeIdeas = Array();

        if($loggedInUser !== false){
            $data = $this->SelectThemeIdeasByUser($loggedInUser->Id);

            while($info = mysqli_fetch_array($data)){
                $themeIdea = new ThemeIdeasModel();
                $themeIdea->Id = $info[DB_COLUMN_THEMEIDEA_ID];
                $themeIdea->ThemeId = $info[DB_COLUMN_THEMEIDEA_THEME_ID];
                $themeIdea->UserId = $info[DB_COLUMN_THEMEIDEA_USER_ID];
                $themeIdea->Ideas = $info[DB_COLUMN_THEMEIDEA_IDEAS];

                $themeIdeas[] = $themeIdea;
            }
        }
    
        StopTimer("LoadThemeIdeasForUser");
        return $themeIdeas;
    }

//////////////////////// END MODEL CONSTRUCTOR
    
//////////////////////// DATABASE ACTIONS (select, insert, update)

    function GetThemeIdeasOfUserFormatted($userId){
        AddActionLog("GetThemeIdeasOfUserFormatted");
        StartTimer("GetThemeIdeasOfUserFormatted");

        $data = $this->SelectAllThemeIdeasByUserWithTheme($userId);
    
        StopTimer("GetThemeIdeasOfUserFormatted");
        return ArrayToHTML(MySQLDataToArray($data));
    }

//////////////////////// END DATABASE ACTIONS
    
//////////////////////// FUCNTION SQL

    private function SelectThemeIdeasByUser($userId){
        AddActionLog("ThemeIdeasData_SelectThemeIdeasByUser");
        StartTimer("ThemeIdeasData_SelectThemeIdeasByUser");
        
        $escapedUserId = mysqli_real_escape_string($this->dbConnection, $userId);
        $sql = "
            SELECT ".DB_COLUMN_THEMEIDEA_ID.", ".DB_COLUMN_THEMEIDEA_THEME_ID.", ".DB_COLUMN_THEMEIDEA_USER_ID.", ".DB_COLUMN_THEMEIDEA_IDEAS."
            FROM ".DB_TABLE_THEMEIDEA."
            WHERE ".DB_COLUMN_THEMEIDEA_USER_ID." = $escapedUserId
        ";
        
        StopTimer("ThemeIdeasData_SelectThemeIdeasByUser");
        return mysqli_query($this->dbConnection, $sql);
    }

    private function SelectAllThemeIdeasByUserWithTheme($userId){
        AddActionLog("ThemeIdeasData_SelectAllThemeIdeasByUserWithTheme");
        StartTimer("ThemeIdeasData_SelectAllThemeIdeasByUserWithTheme");
        
        $escapedUserId = mysqli_real_escape_string($this->dbConnection, $userId);
        $sql = "
            SELECT i.".DB_COLUMN_THEMEIDEA_ID.", i.".DB_COLUMN_THEMEIDEA_DATETIME.", i.".DB_COLUMN_THEMEIDEA_IP.", i.".DB_COLUMN_THEMEIDEA_USER_AGENT.", i.".DB_COLUMN_THEMEIDEA_THEME_ID.", t.".DB_COLUMN_THEME_TEXT.", i.".DB_COLUMN_THEMEIDEA_USER_ID.", i.".DB_COLUMN_THEMEIDEA_IDEAS."
            FROM ".DB_TABLE_THEMEIDEA." i, ".DB_TABLE_THEME." t
            WHERE i.".DB_COLUMN_THEMEIDEA_USER_ID." = $escapedUserId
              AND i.".DB_COLUMN_THEMEIDEA_THEME_ID." = t.".DB_COLUMN_THEME_ID."
        ";
        
        StopTimer("ThemeIdeasData_SelectAllThemeIdeasByUserWithTheme");
        return mysqli_query($this->dbConnection, $sql);
    }

//////////////////////// END FUCNTION SQL

//////////////////////// PUBLIC DATA EXPORT

    function GetAllPublicData(){
        AddActionLog("ThemeIdeasData_GetAllPublicData");
        StartTimer("ThemeIdeasData_GetAllPublicData");
        
        $dataFromDatabase = MySQLDataToArray($this->SelectPublicData());
        foreach($dataFromDatabase as $i => $row){
            $dataFromDatabase[$i][DB_COLUMN_THEMEIDEA_DATETIME] = gmdate("Y-m-d H:i:s", time());
            $dataFromDatabase[$i][DB_COLUMN_THEMEIDEA_IP] = "MIGRATION";
            $dataFromDatabase[$i][DB_COLUMN_THEMEIDEA_USER_AGENT] = "MIGRATION";
            $dataFromDatabase[$i][DB_COLUMN_THEMEIDEA_IDEAS] = "Idea";
        }

        StopTimer("ThemeIdeasData_GetAllPublicData");
        return $dataFromDatabase;
    }

    private function SelectPublicData(){
        AddActionLog("ThemeIdeasData_SelectPublicData");
        StartTimer("ThemeIdeasData_SelectPublicData");

        $sql = "
            SELECT ".implode(",", $this->publicColumns)."
            FROM ".DB_TABLE_THEMEIDEA.";
        ";

        StopTimer("ThemeIdeasData_SelectPublicData");
        return mysqli_query($this->dbConnection, $sql);
    }

//////////////////////// END PUBLIC DATA EXPORT
}

?>