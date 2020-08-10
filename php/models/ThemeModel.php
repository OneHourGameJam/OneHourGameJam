<?php

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

    private $themeDbInterface;
    private $themeVoteDbInterface;

    function __construct(&$themeDbInterface, &$themeVoteDbInterface, &$loggedInUser) {
        $this->themeDbInterface = $themeDbInterface;
        $this->themeVoteDbInterface = $themeVoteDbInterface;
        $this->ThemeModels = $this->LoadThemes();
        $this->LoggedInUserThemeVotes = $this->LoadUserThemeVotes($loggedInUser);
    }

//////////////////////// MODEL CONSTRUCTOR

    function LoadThemes(){
        AddActionLog("LoadThemes");
        StartTimer("LoadThemes");
        
        $data = $this->themeDbInterface->SelectAllWithResults();

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
    
        $data = $this->themeVoteDbInterface->SelectThemeVotesByUser($loggedInUser->Id);
    
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

        $data = $this->themeDbInterface->SoftDelete($themeId);
        
        StopTimer("LoadThemes");
    }

    function GetThemesOfUserFormatted($userId){
        AddActionLog("GetThemesOfUserFormatted");
        StartTimer("GetThemesOfUserFormatted");

        $data = $this->themeDbInterface->SelectThemesByUser($userId);
    
        StopTimer("GetThemesOfUserFormatted");
        return ArrayToHTML(MySQLDataToArray($data));
    }

    function GetThemeVotesOfUserFormatted($userId){
        AddActionLog("GetThemeVotesOfUserFormatted");
        StartTimer("GetThemeVotesOfUserFormatted");
    
        $data = $this->themeVoteDbInterface->SelectThemeVotesWithThemeByUser($userId);
    
        StopTimer("GetThemeVotesOfUserFormatted");
        return ArrayToHTML(MySQLDataToArray($data));
    }

//////////////////////// END DATABASE ACTIONS

//////////////////////// PUBLIC DATA EXPORT

    function GetAllThemePublicData(){
        AddActionLog("ThemesData_GetAllThemePublicData");
        StartTimer("ThemesData_GetAllThemePublicData");
        
        $dataFromDatabase = MySQLDataToArray($this->themeDbInterface->SelectThemePublicData());
        foreach($dataFromDatabase as $i => $row){
            $dataFromDatabase[$i][DB_COLUMN_THEME_DATETIME] = gmdate("Y-m-d H:i:s", time());
            $dataFromDatabase[$i][DB_COLUMN_THEME_IP] = OVERRIDE_MIGRATION;
            $dataFromDatabase[$i][DB_COLUMN_THEME_USER_AGENT] = OVERRIDE_MIGRATION;
        }

        StopTimer("ThemesData_GetAllThemePublicData");
        return $dataFromDatabase;
    }

    function GetAllThemeVotePublicData(){
        AddActionLog("ThemesData_GetAllThemeVotePublicData");
        StartTimer("ThemesData_GetAllThemeVotePublicData");
        
        $dataFromDatabase = MySQLDataToArray($this->themeVoteDbInterface->SelectThemeVotePublicData());
        foreach($dataFromDatabase as $i => $row){
            $dataFromDatabase[$i][DB_COLUMN_THEMEVOTE_DATETIME] = gmdate("Y-m-d H:i:s", time());
            $dataFromDatabase[$i][DB_COLUMN_THEMEVOTE_IP] = OVERRIDE_MIGRATION;
            $dataFromDatabase[$i][DB_COLUMN_THEMEVOTE_USER_AGENT] = OVERRIDE_MIGRATION;
            $dataFromDatabase[$i][DB_COLUMN_THEMEVOTE_TYPE] = rand(1, 3);
        }

        StopTimer("ThemesData_GetAllThemeVotePublicData");
        return $dataFromDatabase;
    }

//////////////////////// END PUBLIC DATA EXPORT
}

?>