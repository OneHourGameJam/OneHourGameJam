<?php

class AdminVoteModel{
	public $SubjectUserId;
	public $VoteType;
}

class AdminVoteData{
    public $AdminVoteModels;
    public $LoggedInUserAdminVotes;

    private $adminVoteDbInterface;

    function __construct(&$adminVoteDbInterface, &$loggedInUser) {
        $this->adminVoteDbInterface = $adminVoteDbInterface;
        $this->AdminVoteModels = $this->LoadAdminVotes();
        $this->LoggedInUserAdminVotes = $this->LoadLoggedInUsersAdminVotes($loggedInUser);
    }

//////////////////////// MODEL CONSTRUCTOR

    function LoadAdminVotes(){
        AddActionLog("LoadAdminVotes");
        StartTimer("LoadAdminVotes");


        $data = $this->adminVoteDbInterface->SelectCurrentlyActiveVotes();

        $adminVoteModels = Array();
        while($info = mysqli_fetch_array($data)){
            $adminVote = new AdminVoteModel();

            $adminVote->SubjectUserId = $info[DB_COLUMN_ADMINVOTE_SUBJECT_USER_ID];
            $adminVote->VoteType = $info[DB_COLUMN_ADMINVOTE_TYPE];

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

        $data = $this->adminVoteDbInterface->SelectVotesByUser($loggedInUser->Id);

        while($info = mysqli_fetch_array($data)){
            $adminVoteData = new AdminVoteModel();

            $adminVoteData->SubjectUserId = $info[DB_COLUMN_ADMINVOTE_SUBJECT_USER_ID];
            $adminVoteData->VoteType = $info[DB_COLUMN_ADMINVOTE_TYPE];

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
    
        $data = $this->adminVoteDbInterface->SelectWhereVoterUserId($userId);

        StopTimer("GetAdminVotesCastByUserFormatted");
        return ArrayToHTML(MySQLDataToArray($data));
    }
    
    function GetAdminVotesForSubjectUserFormatted($userId){
        AddActionLog("GetAdminVotesForSubjectUserFormatted");
        StartTimer("GetAdminVotesForSubjectUserFormatted");
    
        $data = $this->adminVoteDbInterface->SelectWhereSubjectUserId($userId);
    
        StopTimer("GetAdminVotesForSubjectUserFormatted");
        return ArrayToHTML(MySQLDataToArray($data));
    }

//////////////////////// END DATABASE ACTIONS

//////////////////////// PUBLIC DATA EXPORT

    function GetAllPublicData(){
        AddActionLog("AdminVoteData_GetAllPublicData");
        StartTimer("AdminVoteData_GetAllPublicData");
        
        $dataFromDatabase = MySQLDataToArray($this->adminVoteDbInterface->SelectPublicData());

        $voteTypesToSelectFrom = array(ADMINVOTE_FOR, ADMINVOTE_NEUTRAL, ADMINVOTE_AGAINST);

        foreach($dataFromDatabase as $i => $row){
            $dataFromDatabase[$i][DB_COLUMN_ADMINVOTE_DATETIME] = gmdate("Y-m-d H:i:s", time());
            $dataFromDatabase[$i][DB_COLUMN_ADMINVOTE_IP] = OVERRIDE_MIGRATION;
            $dataFromDatabase[$i][DB_COLUMN_ADMINVOTE_USER_AGENT] = OVERRIDE_MIGRATION;
            $dataFromDatabase[$i][DB_COLUMN_ADMINVOTE_TYPE] = $voteTypesToSelectFrom[rand(0, 2)];
        }

        StopTimer("AdminVoteData_GetAllPublicData");
        return $dataFromDatabase;
    }

//////////////////////// END PUBLIC DATA EXPORT
}

?>