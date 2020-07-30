<?php

define("DB_TABLE_POLL", "poll");
define("DB_TABLE_POLLOPTION", "poll_option");
define("DB_TABLE_POLLVOTE", "poll_vote");

define("DB_COLUMN_POLL_ID",             "poll_id");
define("DB_COLUMN_POLL_QUESTION",       "poll_question");
define("DB_COLUMN_POLL_TYPE",           "poll_type");
define("DB_COLUMN_POLL_START_DATETIME", "poll_start_datetime");
define("DB_COLUMN_POLL_END_DATETIME",   "poll_end_datetime");
define("DB_COLUMN_POLL_DELETED",        "poll_deleted");

define("DB_COLUMN_POLLOPTION_ID",        "option_id");
define("DB_COLUMN_POLLOPTION_POLL_ID",   "option_poll_id");
define("DB_COLUMN_POLLOPTION_POLL_TEXT", "option_poll_text");

define("DB_COLUMN_POLLVOTE_ID",         "vote_id");
define("DB_COLUMN_POLLVOTE_OPTION_ID",  "vote_option_id");
define("DB_COLUMN_POLLVOTE_USER_ID",    "vote_user_id");
define("DB_COLUMN_POLLVOTE_DELETED",    "vote_deleted");

class PollModel{
	public $Id;
	public $Question;
	public $PollType;
	public $DateStart;
	public $DateEnd;
	public $IsActive;
    public $Options;
    public $UsersVotedInPoll;
}

class PollOptionModel{
	public $Id;
	public $Text;
	public $Votes;
}

class PollData{
    public $PollModels;
    public $LoggedInUserPollVotes;

    private $dbConnection;
    private $publicColumnsPoll = Array(DB_COLUMN_POLL_ID, DB_COLUMN_POLL_QUESTION, DB_COLUMN_POLL_TYPE, DB_COLUMN_POLL_START_DATETIME, DB_COLUMN_POLL_END_DATETIME, DB_COLUMN_POLL_DELETED);
    private $privateColumnsPoll = Array();
    private $publicColumnsPollOption = Array(DB_COLUMN_POLLOPTION_ID, DB_COLUMN_POLLOPTION_POLL_ID, DB_COLUMN_POLLOPTION_POLL_TEXT);
    private $privateColumnsPollOption = Array();
    private $publicColumnsPollVote = Array(DB_COLUMN_POLLVOTE_ID, DB_COLUMN_POLLVOTE_USER_ID, DB_COLUMN_POLLVOTE_DELETED);
    private $privateColumnsPollVote = Array(DB_COLUMN_POLLVOTE_OPTION_ID);

    function __construct(&$dbConn, &$loggedInUser) {
        $this->dbConnection = $dbConn;
        $this->PollModels = $this->LoadPolls();
        $this->LoggedInUserPollVotes = $this->LoadLoggedInUserPollVotes($loggedInUser);
    }

//////////////////////// MODEL CONSTRUCTOR

    function LoadPolls(){
        AddActionLog("LoadPolls");
        StartTimer("LoadPolls");

        $data = $this->SelectAllPollOptionsAndVotes();

        $pollModels = Array();
        while($pollData = mysqli_fetch_array($data)){
            $pollId = intval($pollData[DB_COLUMN_POLL_ID]);
            $pollQuestion = $pollData[DB_COLUMN_POLL_QUESTION];
            $pollType = $pollData[DB_COLUMN_POLL_TYPE];
            $pollDateStart = $pollData[DB_COLUMN_POLL_START_DATETIME];
            $pollDateEnd = $pollData[DB_COLUMN_POLL_END_DATETIME];
            $pollIsActive = intval($pollData["is_active"]);
            $optionId = intval($pollData[DB_COLUMN_POLLOPTION_ID]);
            $optionText = $pollData[DB_COLUMN_POLLOPTION_POLL_TEXT];
            $optionVotes = intval($pollData["vote_num"]);

            if(!isset($pollModels[$pollId])){
                $pollModel = new PollModel();

                $pollModel->Id = $pollId;
                $pollModel->Question = $pollQuestion;
                $pollModel->PollType = $pollType;
                $pollModel->DateStart = $pollDateStart;
                $pollModel->DateEnd = $pollDateEnd;
                $pollModel->IsActive = $pollIsActive;
                $pollModel->Options = Array();

                $pollModels[$pollId] = $pollModel;
            }

            $pollOptionModel = new PollOptionModel();

            $pollOptionModel->Id = $optionId;
            $pollOptionModel->Text = $optionText;
            $pollOptionModel->Votes = $optionVotes;

            $pollModels[$pollId]->Options[$optionId] = $pollOptionModel;
        }

        $data = $this->SelectNumberOfUsersVotedInEachPoll();
        while($pollData = mysqli_fetch_array($data)){
            $pollId = intval($pollData[DB_COLUMN_POLL_ID]);
            $usersVotedInPoll = intval($pollData["users_voted_in_poll"]);
            $pollModels[$pollId]->UsersVotedInPoll = $usersVotedInPoll;
        }

        StopTimer("LoadPolls");
        return $pollModels;
    }

    function LoadLoggedInUserPollVotes(&$loggedInUser){
        AddActionLog("LoadLoggedInUserPollVotes");
        StartTimer("LoadLoggedInUserPollVotes");
    
        $loggedInUserPollVotes = Array();
        if($loggedInUser !== false){
            $data = $this->SelectActivePollVotesOfUser($loggedInUser->Id);
    
            while($userVoteData = mysqli_fetch_array($data)){
                $votePollID = intval($userVoteData[DB_COLUMN_POLLOPTION_POLL_ID]);
                $voteOptionID = intval($userVoteData[DB_COLUMN_POLLOPTION_ID]);
                $loggedInUserPollVotes[$votePollID][$voteOptionID] = true;
            }
        }
    
        StopTimer("LoadLoggedInUserPollVotes");
        return $loggedInUserPollVotes;
    }

//////////////////////// END MODEL CONSTRUCTOR
    
//////////////////////// DATABASE ACTIONS (select, insert, update)

    function GetPollVotesOfUserFormatted($userId){
        AddActionLog("GetPollVotesOfUserFormatted");
        StartTimer("GetPollVotesOfUserFormatted");
    
        $data = $this->SelectPollVotesOfUser($userId);
    
        StopTimer("GetPollVotesOfUserFormatted");
        return ArrayToHTML(MySQLDataToArray($data));
    }

//////////////////////// END DATABASE ACTIONS
    
//////////////////////// FUCNTION SQL

private function SelectAllPollOptionsAndVotes(){
    AddActionLog("PollData_SelectAllPollOptionsAndVotes");
    StartTimer("PollData_SelectAllPollOptionsAndVotes");

    $sql = "
        SELECT * 
        FROM(
            SELECT *, NOW() BETWEEN p.".DB_COLUMN_POLL_START_DATETIME." AND p.".DB_COLUMN_POLL_END_DATETIME." AS is_active
            FROM ".DB_TABLE_POLL." p, ".DB_TABLE_POLLOPTION." o 
            WHERE p.".DB_COLUMN_POLL_DELETED." = 0 
            AND p.".DB_COLUMN_POLL_ID." = o.".DB_COLUMN_POLLOPTION_POLL_ID.") a
        LEFT JOIN(
            SELECT ".DB_COLUMN_POLLVOTE_OPTION_ID.", count(1) AS vote_num 
            FROM ".DB_TABLE_POLLVOTE." v 
            WHERE ".DB_COLUMN_POLLVOTE_DELETED." = 0 
            GROUP BY v.".DB_COLUMN_POLLVOTE_OPTION_ID.") b
        ON (a.".DB_COLUMN_POLLOPTION_ID." = b.".DB_COLUMN_POLLVOTE_OPTION_ID.")
    ";
    
    StopTimer("PollData_SelectAllPollOptionsAndVotes");
    return mysqli_query($this->dbConnection, $sql);
}

private function SelectNumberOfUsersVotedInEachPoll(){
    AddActionLog("PollData_SelectNumberOfUsersVotedInEachPoll");
    StartTimer("PollData_SelectNumberOfUsersVotedInEachPoll");

    $sql = "
        SELECT voters_in_poll.".DB_COLUMN_POLL_ID.", COUNT(1) AS users_voted_in_poll
        FROM 
        (
            SELECT p.".DB_COLUMN_POLL_ID.", v.".DB_COLUMN_POLLVOTE_USER_ID."
            FROM ".DB_TABLE_POLL." p, ".DB_TABLE_POLLOPTION." o, ".DB_TABLE_POLLVOTE." v
            WHERE ".DB_COLUMN_POLL_DELETED." = 0
            AND v.".DB_COLUMN_POLLVOTE_DELETED." = 0
            AND p.".DB_COLUMN_POLL_ID." = o.".DB_COLUMN_POLLOPTION_POLL_ID." 
            AND o.".DB_COLUMN_POLLOPTION_ID." = v.".DB_COLUMN_POLLVOTE_OPTION_ID."
            GROUP BY v.".DB_COLUMN_POLLVOTE_USER_ID.", p.".DB_COLUMN_POLL_ID."
        ) voters_in_poll
        GROUP BY voters_in_poll.".DB_COLUMN_POLL_ID."
    ";
    
    StopTimer("PollData_SelectNumberOfUsersVotedInEachPoll");
    return mysqli_query($this->dbConnection, $sql);
}

private function SelectActivePollVotesOfUser($userId){
    AddActionLog("PollData_SelectActivePollVotesOfUser");
    StartTimer("PollData_SelectActivePollVotesOfUser");

    $escapedUserId = mysqli_real_escape_string($this->dbConnection, $userId);
    $sql = "
        SELECT o.".DB_COLUMN_POLLOPTION_POLL_ID.", o.".DB_COLUMN_POLLOPTION_ID."
        FROM ".DB_TABLE_POLLVOTE." v, ".DB_TABLE_POLLOPTION." o
        WHERE v.".DB_COLUMN_POLLVOTE_OPTION_ID." = o.".DB_COLUMN_POLLOPTION_ID."
          AND v.".DB_COLUMN_POLLVOTE_DELETED." != 1
          AND v.".DB_COLUMN_POLLVOTE_USER_ID." = ".$escapedUserId."
    ";
    
    StopTimer("PollData_SelectActivePollVotesOfUser");
    return mysqli_query($this->dbConnection, $sql);
}

private function SelectPollVotesOfUser($userId){
    AddActionLog("PollData_SelectPollVotesOfUser");
    StartTimer("PollData_SelectPollVotesOfUser");

    $escapedUserId = mysqli_real_escape_string($this->dbConnection, $userId);
    $sql = "
        SELECT p.".DB_COLUMN_POLL_QUESTION.", o.".DB_COLUMN_POLLOPTION_POLL_TEXT.", v.*
        FROM ".DB_TABLE_POLLVOTE." v, ".DB_TABLE_POLLOPTION." o, ".DB_TABLE_POLL." p
        WHERE v.".DB_COLUMN_POLLVOTE_OPTION_ID." = o.".DB_COLUMN_POLLOPTION_ID."
          AND o.".DB_COLUMN_POLLOPTION_POLL_ID." = p.".DB_COLUMN_POLL_ID."
          AND v.".DB_COLUMN_POLLVOTE_USER_ID." = $escapedUserId;
    ";
    
    StopTimer("PollData_SelectPollVotesOfUser");
    return mysqli_query($this->dbConnection, $sql);
}

//////////////////////// END FUCNTION SQL

//////////////////////// PUBLIC DATA EXPORT

    function GetAllPublicPollData(){
        AddActionLog("PollData_GetAllPublicData");
        StartTimer("PollData_GetAllPublicData");
        
        $dataFromDatabase = MySQLDataToArray($this->SelectPublicPollData());

        foreach($dataFromDatabase as $i => $row){
            //private data overrides
        }

        StopTimer("PollData_GetAllPublicData");
        return $dataFromDatabase;
    }

    private function SelectPublicPollData(){
        AddActionLog("PollData_SelectPublicPollData");
        StartTimer("PollData_SelectPublicPollData");

        $sql = "
            SELECT ".implode(",", $this->publicColumnsPoll)."
            FROM ".DB_TABLE_POLL.";
        ";

        StopTimer("PollData_SelectPublicPollData");
        return mysqli_query($this->dbConnection, $sql);
    }

    function GetAllPublicPollOptionData(){
        AddActionLog("PollData_GetAllPublicData");
        StartTimer("PollData_GetAllPublicData");
        
        $dataFromDatabase = MySQLDataToArray($this->SelectPublicPollOptionData());

        foreach($dataFromDatabase as $i => $row){
            //private data overrides
        }

        StopTimer("PollData_GetAllPublicData");
        return $dataFromDatabase;
    }

    private function SelectPublicPollOptionData(){
        AddActionLog("PollData_SelectPublicPollOptionData");
        StartTimer("PollData_SelectPublicPollOptionData");

        $sql = "
            SELECT ".implode(",", $this->publicColumnsPollOption)."
            FROM ".DB_TABLE_POLLOPTION.";
        ";

        StopTimer("PollData_SelectPublicPollOptionData");
        return mysqli_query($this->dbConnection, $sql);
    }

    function GetAllPublicPollVoteData(){
        AddActionLog("PollData_GetAllPublicData");
        StartTimer("PollData_GetAllPublicData");
        
        $dataFromDatabase = MySQLDataToArray($this->SelectPublicPollVoteData());

        foreach($dataFromDatabase as $i => $row){
            $dataFromDatabase[$i][DB_COLUMN_POLLVOTE_OPTION_ID] = rand(1, 10);
        }

        StopTimer("PollData_GetAllPublicData");
        return $dataFromDatabase;
    }

    private function SelectPublicPollVoteData(){
        AddActionLog("PollData_SelectPublicPollVoteData");
        StartTimer("PollData_SelectPublicPollVoteData");

        $sql = "
            SELECT ".implode(",", $this->publicColumnsPollVote)."
            FROM ".DB_TABLE_POLLVOTE.";
        ";

        StopTimer("PollData_SelectPublicPollVoteData");
        return mysqli_query($this->dbConnection, $sql);
    }

//////////////////////// END PUBLIC DATA EXPORT
}

?>