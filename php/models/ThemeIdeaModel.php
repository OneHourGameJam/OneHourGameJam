<?php

class ThemeIdeaModel
{
    public $Id;
    public $ThemeId;
    public $UserId;
    public $Ideas;
}

class ThemeIdeaData{
    public $ThemeIdeas;
    
    private $themeIdeaDbInterface;

    function __construct(&$themeIdeaDbInterface, &$loggedInUser) {
        $this->themeIdeaDbInterface = $themeIdeaDbInterface;
        $this->ThemeIdeas = $this->LoadThemeIdeasForUser($loggedInUser);
    }

//////////////////////// MODEL CONSTRUCTOR

    function LoadThemeIdeasForUser(&$loggedInUser){
        AddActionLog("LoadThemeIdeasForUser");
        StartTimer("LoadThemeIdeasForUser");

        $themeIdeas = Array();
        if($loggedInUser !== false){
            $data = $this->themeIdeaDbInterface->SelectThemeIdeasByUser($loggedInUser->Id);

            while($info = mysqli_fetch_array($data)){
                $themeIdea = new ThemeIdeaModel();
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

        $data = $this->themeIdeaDbInterface->SelectAllThemeIdeasByUserWithTheme($userId);
    
        StopTimer("GetThemeIdeasOfUserFormatted");
        return ArrayToHTML(MySQLDataToArray($data));
    }

//////////////////////// END DATABASE ACTIONS

//////////////////////// PUBLIC DATA EXPORT

    function GetAllPublicData(){
        AddActionLog("ThemeIdeaData_GetAllPublicData");
        StartTimer("ThemeIdeaData_GetAllPublicData");
        
        $dataFromDatabase = MySQLDataToArray($this->themeIdeaDbInterface->SelectPublicData());
        foreach($dataFromDatabase as $i => $row){
            $dataFromDatabase[$i][DB_COLUMN_THEMEIDEA_DATETIME] = gmdate("Y-m-d H:i:s", time());
            $dataFromDatabase[$i][DB_COLUMN_THEMEIDEA_IP] = "MIGRATION";
            $dataFromDatabase[$i][DB_COLUMN_THEMEIDEA_USER_AGENT] = "MIGRATION";
            $dataFromDatabase[$i][DB_COLUMN_THEMEIDEA_IDEAS] = "Idea";
        }

        StopTimer("ThemeIdeaData_GetAllPublicData");
        return $dataFromDatabase;
    }

//////////////////////// END PUBLIC DATA EXPORT
}

?>