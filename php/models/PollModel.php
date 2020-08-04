<?php

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

    private $pollDbInterface;
    private $pollOptionDbInterface;
    private $pollVoteDbInterface;

    function __construct(&$pollDbInterface, &$pollOptionDbInterface, &$pollVoteDbInterface, &$loggedInUser) {
        $this->pollDbInterface = $pollDbInterface;
        $this->pollOptionDbInterface = $pollOptionDbInterface;
        $this->pollVoteDbInterface = $pollVoteDbInterface;
        $this->PollModels = $this->LoadPolls();
        $this->LoggedInUserPollVotes = $this->LoadLoggedInUserPollVotes($loggedInUser);
    }

//////////////////////// MODEL CONSTRUCTOR

    function LoadPolls(){
        AddActionLog("LoadPolls");
        StartTimer("LoadPolls");

        $data = $this->pollDbInterface->SelectAllPollOptionsAndVotes();

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

        $data = $this->pollDbInterface->SelectNumberOfUsersVotedInEachPoll();
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
            $data = $this->pollDbInterface->SelectActivePollVotesOfUser($loggedInUser->Id);
    
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
    
        $data = $this->pollDbInterface->SelectPollVotesOfUser($userId);
    
        StopTimer("GetPollVotesOfUserFormatted");
        return ArrayToHTML(MySQLDataToArray($data));
    }

//////////////////////// END DATABASE ACTIONS

//////////////////////// PUBLIC DATA EXPORT

    function GetAllPublicPollData(){
        AddActionLog("PollData_GetAllPublicData");
        StartTimer("PollData_GetAllPublicData");
        
        $dataFromDatabase = MySQLDataToArray($this->pollDbInterface->SelectPublicData());

        foreach($dataFromDatabase as $i => $row){
            //private data overrides
        }

        StopTimer("PollData_GetAllPublicData");
        return $dataFromDatabase;
    }

    function GetAllPublicPollOptionData(){
        AddActionLog("PollData_GetAllPublicData");
        StartTimer("PollData_GetAllPublicData");
        
        $dataFromDatabase = MySQLDataToArray($this->pollOptionDbInterface->SelectPublicData());

        foreach($dataFromDatabase as $i => $row){
            //private data overrides
        }

        StopTimer("PollData_GetAllPublicData");
        return $dataFromDatabase;
    }

    function GetAllPublicPollVoteData(){
        AddActionLog("PollData_GetAllPublicData");
        StartTimer("PollData_GetAllPublicData");
        
        $dataFromDatabase = MySQLDataToArray($this->pollVoteDbInterface->SelectPublicData());

        foreach($dataFromDatabase as $i => $row){
            $dataFromDatabase[$i][DB_COLUMN_POLLVOTE_OPTION_ID] = rand(1, 10);
        }

        StopTimer("PollData_GetAllPublicData");
        return $dataFromDatabase;
    }

//////////////////////// END PUBLIC DATA EXPORT
}

?>