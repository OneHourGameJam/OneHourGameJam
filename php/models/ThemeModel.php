<?php
    
define("DB_TABLE_THEME", "theme");
define("DB_TABLE_THEMEVOTE", "themevote");

define("DB_COLUMN_THEME_ID",                "theme_id");
define("DB_COLUMN_THEME_DATETIME",          "theme_datetime");
define("DB_COLUMN_THEME_IP",                "theme_ip");
define("DB_COLUMN_THEME_USER_AGENT",        "theme_user_agent");
define("DB_COLUMN_THEME_TEXT",              "theme_text");
define("DB_COLUMN_THEME_AUTHOR_USER_ID",    "theme_author_user_id");
define("DB_COLUMN_THEME_BANNED",            "theme_banned");
define("DB_COLUMN_THEME_DELETED",           "theme_deleted");

define("DB_COLUMN_THEMEVOTE_ID",            "themevote_id");
define("DB_COLUMN_THEMEVOTE_DATETIME",      "themevote_datetime");
define("DB_COLUMN_THEMEVOTE_IP",            "themevote_ip");
define("DB_COLUMN_THEMEVOTE_USER_AGENT",    "themevote_user_agent");
define("DB_COLUMN_THEMEVOTE_THEME_ID",      "themevote_theme_id");
define("DB_COLUMN_THEMEVOTE_USER_ID",       "themevote_user_id");
define("DB_COLUMN_THEMEVOTE_TYPE",          "themevote_type");

class ThemeModel{
	public $Id;
	public $Theme;
	public $AuthorUserId;
	public $Banned;
	public $Deleted;
	public $VotesAgainst;
	public $VotesNeutral;
	public $VotesFor;
	public $VotesReport;
	public $DaysAgo;
}

class ThemeData{
    public $ThemeModels;
    public $LoggedInUserThemeVotes;

    private $dbConnection;
    private $publicColumnsTheme = Array(DB_COLUMN_THEME_ID, DB_COLUMN_THEME_TEXT, DB_COLUMN_THEME_AUTHOR_USER_ID, DB_COLUMN_THEME_BANNED, DB_COLUMN_THEME_DELETED);
    private $privateColumnsTheme = Array(DB_COLUMN_THEME_DATETIME, DB_COLUMN_THEME_IP, DB_COLUMN_THEME_USER_AGENT);
    private $publicColumnsThemeVote = Array(DB_COLUMN_THEMEVOTE_ID, DB_COLUMN_THEMEVOTE_THEME_ID, DB_COLUMN_THEMEVOTE_USER_ID);
    private $privateColumnsThemeVote = Array(DB_COLUMN_THEMEVOTE_DATETIME, DB_COLUMN_THEMEVOTE_IP, DB_COLUMN_THEMEVOTE_USER_AGENT, DB_COLUMN_THEMEVOTE_TYPE);

    function __construct($dbConn, &$loggedInUser) {
        $this->dbConnection = $dbConn;
        $this->ThemeModels = $this->LoadThemes();
        $this->LoggedInUserThemeVotes = $this->LoadUserThemeVotes($loggedInUser);
    }

//////////////////////// MODEL CONSTRUCTOR

    function LoadThemes(){
        AddActionLog("LoadThemes");
        StartTimer("LoadThemes");
        
        $data = $this->SelectAllWithResults();

        $themeModels = Array();
        while($themeData = mysqli_fetch_array($data)){
            $themeID = $themeData[DB_COLUMN_THEME_ID];
            $themeVoteType = $themeData[DB_COLUMN_THEMEVOTE_TYPE];
            $themeVoteCount = intval($themeData["themevote_count"]);
            $themeVotesFor = ($themeVoteType == "3") ? $themeVoteCount : 0;
            $themeVotesNeutral = ($themeVoteType == "2") ? $themeVoteCount : 0;
            $themeVotesAgainst = ($themeVoteType == "1") ? $themeVoteCount : 0;

            if(isset($themeModels[$themeID])){
                //Theme already processed, simply log numbers for vote type
                $theme = $themeModels[$themeID];
                $theme->VotesAgainst = max($theme->VotesAgainst, $themeVotesAgainst);
                $theme->VotesNeutral =  max($theme->VotesNeutral, $themeVotesNeutral);
                $theme->VotesFor =  max($theme->VotesFor, $themeVotesFor);
                continue;
            }

            $theme = new ThemeModel();
            $theme->Id = $themeID;
            $theme->Theme = $themeData[DB_COLUMN_THEME_TEXT];
            $theme->AuthorUserId = $themeData[DB_COLUMN_THEME_AUTHOR_USER_ID];
            $theme->Banned = $themeData[DB_COLUMN_THEME_BANNED];
            $theme->Deleted = $themeData[DB_COLUMN_THEME_DELETED];
            $theme->VotesAgainst = $themeVotesAgainst;
            $theme->VotesNeutral = $themeVotesNeutral;
            $theme->VotesFor = $themeVotesFor;
            $theme->VotesReport = 0;
            $theme->DaysAgo = intval($themeData["theme_daysago"]);

            $themeModels[$themeID] = $theme;
        }

        StopTimer("LoadThemes");
        return $themeModels;
    }

    function LoadUserThemeVotes(&$loggedInUser){
        AddActionLog("LoadUserThemeVotes");
        StartTimer("LoadUserThemeVotes");
    
        $userThemeVotes = Array();
    
        if($loggedInUser == false){
            return $userThemeVotes;
        }
    
        $data = $this->SelectThemeVotesByUser($loggedInUser->Id);
    
        while($themeVote = mysqli_fetch_array($data)){
            $themeVoteData = Array();
    
            $themeID = $themeVote[DB_COLUMN_THEMEVOTE_THEME_ID];
            $userThemeVoteType = $themeVote[DB_COLUMN_THEMEVOTE_TYPE];
            $userThemeVotes[$themeID] = $userThemeVoteType;
        }
    
        StopTimer("LoadUserThemeVotes");
        return $userThemeVotes;
    }

//////////////////////// END MODEL CONSTRUCTOR
    
//////////////////////// DATABASE ACTIONS (select, insert, update)

    function SoftDeleteThemeInDatabase($themeId){
        AddActionLog("LoadThemes");
        StartTimer("LoadThemes");

        $data = $this->SoftDelete($themeId);
        
        StopTimer("LoadThemes");
    }

    function GetThemesOfUserFormatted($userId){
        AddActionLog("GetThemesOfUserFormatted");
        StartTimer("GetThemesOfUserFormatted");

        $data = $this->SelectThemesByUser($userId);
    
        StopTimer("GetThemesOfUserFormatted");
        return ArrayToHTML(MySQLDataToArray($data));
    }

    function GetThemeVotesOfUserFormatted($userId){
        AddActionLog("GetThemeVotesOfUserFormatted");
        StartTimer("GetThemeVotesOfUserFormatted");
    
        $data = $this->SelectThemeVotesWithThemeByUser($userId);
    
        StopTimer("GetThemeVotesOfUserFormatted");
        return ArrayToHTML(MySQLDataToArray($data));
    }

//////////////////////// END DATABASE ACTIONS
    
//////////////////////// FUCNTION SQL

    private function SelectAllWithResults(){
        AddActionLog("ThemeData_SelectThemeVotesByUser");
        StartTimer("ThemeData_SelectThemeVotesByUser");
    
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
        
        StopTimer("ThemeData_SelectThemeVotesByUser");
        return mysqli_query($this->dbConnection, $sql);
    }

    private function SelectThemeVotesByUser($userId){
        AddActionLog("ThemeData_SelectThemeVotesByUser");
        StartTimer("ThemeData_SelectThemeVotesByUser");
    
        $escapedUserId = mysqli_real_escape_string($this->dbConnection, $userId);
        $sql = "
            SELECT ".DB_COLUMN_THEMEVOTE_THEME_ID.", ".DB_COLUMN_THEMEVOTE_TYPE."
            FROM ".DB_TABLE_THEMEVOTE."
            WHERE ".DB_COLUMN_THEMEVOTE_USER_ID." = $escapedUserId;
        ";
        
        StopTimer("ThemeData_SelectThemeVotesByUser");
        return mysqli_query($this->dbConnection, $sql);
    }

    private function SelectThemesByUser($userId){
        AddActionLog("ThemeData_SelectThemesByUser");
        StartTimer("ThemeData_SelectThemesByUser");
    
        $escapedUserId = mysqli_real_escape_string($this->dbConnection, $userId);
        $sql = "
            SELECT *
            FROM ".DB_TABLE_THEME."
            WHERE ".DB_COLUMN_THEME_AUTHOR_USER_ID." = $escapedUserId;
        ";
        
        StopTimer("ThemeData_SelectThemesByUser");
        return mysqli_query($this->dbConnection, $sql);
    }

    private function SelectThemeVotesWithThemeByUser($userId){
        AddActionLog("ThemeData_SelectThemeVotesWithThemeByUser");
        StartTimer("ThemeData_SelectThemeVotesWithThemeByUser");
        
        $escapedUserId = mysqli_real_escape_string($this->dbConnection, $userId);
        $sql = "
            SELECT ".DB_TABLE_THEME.".".DB_COLUMN_THEME_TEXT.", ".DB_TABLE_THEMEVOTE.".*, IF(".DB_TABLE_THEMEVOTE.".".DB_COLUMN_THEMEVOTE_TYPE." = 1, '-1', IF(".DB_TABLE_THEMEVOTE.".".DB_COLUMN_THEMEVOTE_TYPE." = 2, '0', '+1'))
            FROM ".DB_TABLE_THEMEVOTE.", ".DB_TABLE_THEME."
            WHERE ".DB_TABLE_THEME.".".DB_COLUMN_THEME_ID." = ".DB_TABLE_THEMEVOTE.".".DB_COLUMN_THEMEVOTE_THEME_ID."
              AND ".DB_COLUMN_THEMEVOTE_USER_ID." = $escapedUserId;
        ";
        
        StopTimer("ThemeData_SelectThemeVotesWithThemeByUser");
        return mysqli_query($this->dbConnection, $sql);
    }

    private function SoftDelete($themeId){
        AddActionLog("ThemeData_SoftDelete");
        StartTimer("ThemeData_SoftDelete");

        $escapedThemeId = mysqli_real_escape_string($this->dbConnection, $themeId);
        $sql = "
            UPDATE ".DB_TABLE_THEME."
            SET ".DB_COLUMN_THEME_DELETED." = 1
            WHERE ".DB_COLUMN_THEME_ID." = $escapedThemeId;
        ";
        
        StopTimer("ThemeData_SoftDelete");
        return mysqli_query($this->dbConnection, $sql);
    }

//////////////////////// END FUCNTION SQL

//////////////////////// PUBLIC DATA EXPORT

    function GetAllThemePublicData(){
        AddActionLog("ThemesData_GetAllThemePublicData");
        StartTimer("ThemesData_GetAllThemePublicData");
        
        $dataFromDatabase = MySQLDataToArray($this->SelectThemePublicData());
        foreach($dataFromDatabase as $i => $row){
            $dataFromDatabase[$i][DB_COLUMN_THEME_DATETIME] = gmdate("Y-m-d H:i:s", time());
            $dataFromDatabase[$i][DB_COLUMN_THEME_IP] = "MIGRATION";
            $dataFromDatabase[$i][DB_COLUMN_THEME_USER_AGENT] = "MIGRATION";
        }

        StopTimer("ThemesData_GetAllThemePublicData");
        return $dataFromDatabase;
    }

    private function SelectThemePublicData(){
        AddActionLog("ThemesData_SelectThemePublicData");
        StartTimer("ThemesData_SelectThemePublicData");

        $sql = "
            SELECT ".implode(",", $this->publicColumnsTheme)."
            FROM ".DB_TABLE_THEME.";
        ";

        StopTimer("ThemesData_SelectThemePublicData");
        return mysqli_query($this->dbConnection, $sql);
    }

    function GetAllThemeVotePublicData(){
        AddActionLog("ThemesData_GetAllThemeVotePublicData");
        StartTimer("ThemesData_GetAllThemeVotePublicData");
        
        $dataFromDatabase = MySQLDataToArray($this->SelectThemeVotePublicData());
        foreach($dataFromDatabase as $i => $row){
            $dataFromDatabase[$i][DB_COLUMN_THEMEVOTE_DATETIME] = gmdate("Y-m-d H:i:s", time());
            $dataFromDatabase[$i][DB_COLUMN_THEMEVOTE_IP] = "MIGRATION";
            $dataFromDatabase[$i][DB_COLUMN_THEMEVOTE_USER_AGENT] = "MIGRATION";
            $dataFromDatabase[$i][DB_COLUMN_THEMEVOTE_TYPE] = rand(1, 3);
        }

        StopTimer("ThemesData_GetAllThemeVotePublicData");
        return $dataFromDatabase;
    }

    private function SelectThemeVotePublicData(){
        AddActionLog("ThemesData_SelectThemeVotePublicData");
        StartTimer("ThemesData_SelectThemeVotePublicData");

        $sql = "
            SELECT ".implode(",", $this->publicColumnsThemeVote)."
            FROM ".DB_TABLE_THEMEVOTE.";
        ";

        StopTimer("ThemesData_SelectThemeVotePublicData");
        return mysqli_query($this->dbConnection, $sql);
    }

//////////////////////// END PUBLIC DATA EXPORT
}

?>