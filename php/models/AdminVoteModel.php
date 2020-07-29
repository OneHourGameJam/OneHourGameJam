<?php

define("DB_TABLE_ADMIN_VOTE", "admin_vote");
define("DB_COLUMN_ADMIN_VOTE_ID", "vote_id");
define("DB_COLUMN_ADMIN_VOTE_DATETIME", "vote_datetime");
define("DB_COLUMN_ADMIN_VOTE_IP", "vote_ip");
define("DB_COLUMN_ADMIN_VOTE_USER_AGENT", "vote_user_agent");
define("DB_COLUMN_ADMIN_VOTE_VOTER_USER_ID", "vote_voter_user_id");
define("DB_COLUMN_ADMIN_VOTE_SUBJECT_USER_ID", "vote_subject_user_id");
define("DB_COLUMN_ADMIN_VOTE_TYPE", "vote_type");

class AdminVoteModel{
	public $SubjectUserId;
	public $VoteType;
}

class AdminVoteData{
    public $AdminVoteModels;
    public $LoggedInUserAdminVotes;
    
    private $dbConnection;
    private $publicColumns = Array(DB_COLUMN_ADMIN_VOTE_ID, DB_COLUMN_ADMIN_VOTE_VOTER_USER_ID, DB_COLUMN_ADMIN_VOTE_SUBJECT_USER_ID);
    private $privateColumns = Array(DB_COLUMN_ADMIN_VOTE_DATETIME, DB_COLUMN_ADMIN_VOTE_IP, DB_COLUMN_ADMIN_VOTE_USER_AGENT, DB_COLUMN_ADMIN_VOTE_TYPE);

    function __construct(&$dbConn, &$loggedInUser) {
        $this->dbConnection = $dbConn;
        $this->AdminVoteModels = $this->LoadAdminVotes();
        $this->LoggedInUserAdminVotes = $this->LoadLoggedInUsersAdminVotes($loggedInUser);
    }

//////////////////////// MODEL CONSTRUCTOR

    function LoadAdminVotes(){
        AddActionLog("LoadAdminVotes");
        StartTimer("LoadAdminVotes");


        $data = $this->SelectCurrentlyActiveVotes();

        $adminVoteModels = Array();
        while($info = mysqli_fetch_array($data)){
            $adminVote = new AdminVoteModel();

            $adminVote->SubjectUserId = $info[DB_COLUMN_ADMIN_VOTE_SUBJECT_USER_ID];
            $adminVote->VoteType = $info[DB_COLUMN_ADMIN_VOTE_TYPE];

            $adminVoteModels[] = $adminVote;
        }

        StopTimer("LoadAdminVotes");
        return $adminVoteModels;
    }

    function LoadLoggedInUsersAdminVotes(&$loggedInUser){
        AddActionLog("LoadLoggedInUsersAdminVotes");
        StartTimer("LoadLoggedInUsersAdminVotes");

        $loggedInUserAdminVotes = Array();

        if($loggedInUser == false){
            StopTimer("LoadLoggedInUsersAdminVotes");
            return $loggedInUserAdminVotes;
        }

        $data = $this->SelectVotesByUser($loggedInUser->Id);

        while($info = mysqli_fetch_array($data)){
            $adminVoteData = new AdminVoteModel();

            $adminVoteData->SubjectUserId = $info[DB_COLUMN_ADMIN_VOTE_SUBJECT_USER_ID];
            $adminVoteData->VoteType = $info[DB_COLUMN_ADMIN_VOTE_TYPE];

            $loggedInUserAdminVotes[] = $adminVoteData;
        }

        StopTimer("LoadLoggedInUsersAdminVotes");
        return $loggedInUserAdminVotes;
    }

//////////////////////// END MODEL CONSTRUCTOR
    
//////////////////////// DATABASE ACTIONS (select, insert, update)

    function GetAdminVotesCastByUserFormatted($userId){
        AddActionLog("GetAdminVotesCastByUserFormatted");
        StartTimer("GetAdminVotesCastByUserFormatted");
    
        $data = $this->SelectWhereVoterUserId($userId);

        StopTimer("GetAdminVotesCastByUserFormatted");
        return ArrayToHTML(MySQLDataToArray($data));
    }
    
    function GetAdminVotesForSubjectUserFormatted($userId){
        AddActionLog("GetAdminVotesForSubjectUserFormatted");
        StartTimer("GetAdminVotesForSubjectUserFormatted");
    
        $data = $this->SelectWhereSubjectUserId($userId);
    
        StopTimer("GetAdminVotesForSubjectUserFormatted");
        return ArrayToHTML(MySQLDataToArray($data));
    }

//////////////////////// END DATABASE ACTIONS
    
//////////////////////// FUCNTION SQL

    private function SelectCurrentlyActiveVotes(){
        AddActionLog("AdminVoteData_SelectCurrentlyActiveVotes");
        StartTimer("AdminVoteData_SelectCurrentlyActiveVotes");

        $escapedVoterId = mysqli_real_escape_string($this->dbConnection, $voterUserId);
        $sql = "
            SELECT v.".DB_COLUMN_ADMIN_VOTE_SUBJECT_USER_ID.", v.".DB_COLUMN_ADMIN_VOTE_TYPE."
            FROM ".DB_TABLE_ADMIN_VOTE." v, ".DB_TABLE_USER." u
            WHERE v.".DB_COLUMN_ADMIN_VOTE_VOTER_USER_ID." = u.".DB_COLUMN_USER_ID."
            AND u.".DB_COLUMN_USER_ROLE." = 1
        ";

        StopTimer("AdminVoteData_SelectCurrentlyActiveVotes");
        return mysqli_query($this->dbConnection, $sql);
    }

    private function SelectVotesByUser($voterUserId){
        AddActionLog("AdminVoteData_SelectVotesByUser");
        StartTimer("AdminVoteData_SelectVotesByUser");

        $escapedVoterId = mysqli_real_escape_string($this->dbConnection, $voterUserId);
        $sql = "
            SELECT ".DB_COLUMN_ADMIN_VOTE_SUBJECT_USER_ID.", ".DB_COLUMN_ADMIN_VOTE_TYPE."
            FROM ".DB_TABLE_ADMIN_VOTE."
            WHERE ".DB_COLUMN_ADMIN_VOTE_VOTER_USER_ID." = '$escapedVoterId';
        ";

        StopTimer("AdminVoteData_SelectVotesByUser");
        return mysqli_query($this->dbConnection, $sql);
    }

    private function SelectWhereVoterUserId($voterUserId){
        AddActionLog("AdminVoteData_SelectWhereAdminUserId");
        StartTimer("AdminVoteData_SelectWhereAdminUserId");

        $escapedVoterUserId = mysqli_real_escape_string($this->dbConnection, $voterUserId);
        $sql = "
            SELECT *
            FROM ".DB_TABLE_ADMIN_VOTE."
            WHERE ".DB_COLUMN_ADMIN_VOTE_VOTER_USER_ID." = '$escapedVoterUserId';";

        StopTimer("AdminVoteData_SelectWhereAdminUserId");
        return mysqli_query($this->dbConnection, $sql);
    }

    private function SelectWhereSubjectUserId($subjectUserId){
        AddActionLog("AdminVoteData_SelectWhereSubjectUserId");
        StartTimer("AdminVoteData_SelectWhereSubjectUserId");

        $escapedSubjectUserId = mysqli_real_escape_string($this->dbConnection, $subjectUserId);
        $sql = "
            SELECT ".DB_COLUMN_ADMIN_VOTE_ID.", ".DB_COLUMN_ADMIN_VOTE_DATETIME.", 'redacted' AS ".DB_COLUMN_ADMIN_VOTE_VOTER_USER_ID.", ".DB_COLUMN_ADMIN_VOTE_SUBJECT_USER_ID.", 'redacted' AS ".DB_COLUMN_ADMIN_VOTE_TYPE."
            FROM ".DB_TABLE_ADMIN_VOTE."
            WHERE ".DB_COLUMN_ADMIN_VOTE_SUBJECT_USER_ID." = '$escapedSubjectUserId';";
            
        StopTimer("AdminVoteData_SelectWhereSubjectUserId");
        return mysqli_query($this->dbConnection, $sql);
    }

//////////////////////// END FUCNTION SQL

//////////////////////// PUBLIC DATA EXPORT

    function GetAllPublicData(){
        AddActionLog("AdminVoteData_GetAllPublicData");
        StartTimer("AdminVoteData_GetAllPublicData");
        
        $dataFromDatabase = MySQLDataToArray($this->SelectPublicData());

        $voteTypesToSelectFrom = array("FOR", "NEUTRAL", "AGAINST");

        foreach($dataFromDatabase as $i => $row){
            $dataFromDatabase[$i][DB_COLUMN_ADMIN_VOTE_DATETIME] = gmdate("Y-m-d H:i:s", time());
            $dataFromDatabase[$i][DB_COLUMN_ADMIN_VOTE_IP] = "MIGRATION";
            $dataFromDatabase[$i][DB_COLUMN_ADMIN_VOTE_USER_AGENT] = "MIGRATION";
            $dataFromDatabase[$i][DB_COLUMN_ADMIN_VOTE_TYPE] = $voteTypesToSelectFrom[rand(0, 2)];
        }

        StopTimer("AdminVoteData_GetAllPublicData");
        return $dataFromDatabase;
    }

    private function SelectPublicData(){
        AddActionLog("AdminVoteData_SelectPublicData");
        StartTimer("AdminVoteData_SelectPublicData");

        $sql = "
            SELECT ".implode(",", $this->publicColumns)."
            FROM ".DB_TABLE_ADMIN_VOTE.";
        ";

        StopTimer("AdminVoteData_SelectPublicData");
        return mysqli_query($this->dbConnection, $sql);
    }

//////////////////////// END PUBLIC DATA EXPORT
}

?>