<?php

const ADMINVOTE_FOR = "FOR";
const ADMINVOTE_NEUTRAL = "NEUTRAL";
const ADMINVOTE_AGAINST = "AGAINST";
const ADMINVOTE_SPONSOR = "SPONSOR";
const ADMINVOTE_VETO = "VETO";

class AdminVoteModel{
	public string $SubjectUserId;
	public string $VoteType;
}

class AdminVoteData{
    public array $AdminVoteModels;
    public array $LoggedInUserAdminVotes;

    private AdminVoteDbInterface $adminVoteDbInterface;

    function __construct(AdminVoteDbInterface &$adminVoteDbInterface, &$loggedInUser) {
        $this->adminVoteDbInterface = $adminVoteDbInterface;
        $this->AdminVoteModels = $this->LoadAdminVotes();
        $this->LoggedInUserAdminVotes = $this->LoadLoggedInUsersAdminVotes($loggedInUser);
    }

//////////////////////// MODEL CONSTRUCTOR

    function LoadAdminVotes(): array
    {
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

    function LoadLoggedInUsersAdminVotes(&$loggedInUser): array
    {
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

    function GetAdminVotesCastByUserFormatted($userId): string
    {
        AddActionLog("GetAdminVotesCastByUserFormatted");
        StartTimer("GetAdminVotesCastByUserFormatted");
    
        $data = $this->adminVoteDbInterface->SelectWhereVoterUserId($userId);

        StopTimer("GetAdminVotesCastByUserFormatted");
        return ArrayToHTML(MySQLDataToArray($data));
    }
    
    function GetAdminVotesForSubjectUserFormatted($userId): string
    {
        AddActionLog("GetAdminVotesForSubjectUserFormatted");
        StartTimer("GetAdminVotesForSubjectUserFormatted");
    
        $data = $this->adminVoteDbInterface->SelectWhereSubjectUserId($userId);
    
        StopTimer("GetAdminVotesForSubjectUserFormatted");
        return ArrayToHTML(MySQLDataToArray($data));
    }

//////////////////////// END DATABASE ACTIONS

//////////////////////// PUBLIC DATA EXPORT

    function GetAllPublicData(): array
    {
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