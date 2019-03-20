<?php

class ThemeModel{
	public $Id;
	public $Theme;
	public $Author;
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

    function __construct() {
        $this->ThemeModels = $this->LoadThemes();
    }

    function LoadThemes(){
        global $dbConn;
        AddActionLog("LoadThemes");
        StartTimer("LoadThemes");
        
        $themeModels = Array();

        //Fill list of themes - will return same theme row multiple times (once for each valid themevote_type)
        $sql = "
            SELECT theme_id, theme_text, theme_author, theme_banned, themevote_type, count(themevote_id) AS themevote_count, DATEDIFF(Now(), theme_datetime) as theme_daysago, theme_deleted
            FROM (theme LEFT JOIN themevote ON (themevote.themevote_theme_id = theme.theme_id))
            WHERE theme_deleted != 1
            GROUP BY theme_id, themevote_type
            ORDER BY theme_banned ASC, theme_id ASC
        ";
        $data = mysqli_query($dbConn, $sql);
        $sql = "";

        while($themeData = mysqli_fetch_array($data)){
            $themeID = $themeData["theme_id"];
            $themeVoteType = $themeData["themevote_type"];
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
            $theme->Theme = htmlspecialchars($themeData["theme_text"], ENT_QUOTES);
            $theme->Author = $themeData["theme_author"];
            $theme->Banned = $themeData["theme_banned"];
            $theme->Deleted = $themeData["theme_deleted"];
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
}

?>