<?php

class ThemeIdeasModel
{
    public $Id;
    public $ThemeId;
    public $UserId;
    public $Ideas;
}

class ThemeIdeasData{
    public $ThemeIdeas;

    function __construct(&$loggedInUser) {
        $this->ThemeIdeas = $this->LoadThemeIdeasForUser($loggedInUser);
    }

    function LoadThemeIdeasForUser(&$loggedInUser){
        global $dbConn;
        AddActionLog("LoadThemeIdeasForUser");
        StartTimer("LoadThemeIdeasForUser");
    
        $themeIdeas = Array();

        if($loggedInUser !== false){
            $escapedUserId = mysqli_real_escape_string($dbConn, $loggedInUser->Id);
            
            $sql = "
                SELECT i.idea_id, i.idea_theme_id, i.idea_user_id, i.idea_ideas
                FROM theme_ideas i
                WHERE i.idea_user_id = $escapedUserId
            ";
            $data = mysqli_query($dbConn, $sql);
            $sql = "";

            while($themeIdea = mysqli_fetch_array($data)){
                $ideaId = $themeIdea["idea_id"];
                $themeId = $themeIdea["idea_theme_id"];
                $userId = $themeIdea["idea_user_id"];
                $ideas = $themeIdea["idea_ideas"];

                $themeIdea = new ThemeIdeasModel();
                $themeIdea->Id = $ideaId;
                $themeIdea->ThemeId = $themeId;
                $themeIdea->UserId = $userId;
                $themeIdea->Ideas = $ideas;

                $themeIdeas[] = $themeIdea;
            }
        }
    
        StopTimer("LoadThemeIdeasForUser");
        return $themeIdeas;
    }

    function GetThemeIdeasOfUserFormatted($userId){
        global $dbConn;
        AddActionLog("GetThemeIdeasOfUserFormatted");
        StartTimer("GetThemeIdeasOfUserFormatted");
    
        $escapedUserId = mysqli_real_escape_string($dbConn, $userId);
        $sql = "
            SELECT i.idea_id, i.idea_datetime, i.idea_ip, i.idea_user_agent, i.idea_theme_id, t.theme_text, i.idea_user_id, i.idea_ideas
            FROM theme_ideas i, theme t
            WHERE i.idea_user_id = $escapedUserId
              AND i.idea_theme_id = t.theme_id
        ";
        $data = mysqli_query($dbConn, $sql);
        $sql = "";
    
        StopTimer("GetThemeIdeasOfUserFormatted");
        return ArrayToHTML(MySQLDataToArray($data));
    }
}

?>