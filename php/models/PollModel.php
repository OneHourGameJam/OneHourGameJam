<?php

class PollModel{
	public $Id;
	public $Question;
	public $PollType;
	public $DateStart;
	public $DateEnd;
	public $IsActive;
	public $Options;
}

class PollOptionModel{
	public $Id;
	public $Text;
	public $Votes;
}

class PollData{
    public $PollModels;
    public $LoggedInUserPollVotes;

    function __construct(&$loggedInUser) {
        $this->PollModels = $this->LoadPolls();
        $this->LoggedInUserPollVotes = $this->LoadLoggedInUserPollVotes($loggedInUser);
    }

    function LoadPolls(){
        global $dbConn;
        AddActionLog("LoadPolls");
        StartTimer("LoadPolls");

        $pollModels = Array();
        
        $sql = "
            SELECT * FROM
            (SELECT *, NOW() BETWEEN p.poll_start_datetime AND p.poll_end_datetime AS is_active FROM poll p, poll_option o WHERE p.poll_deleted = 0 and p.poll_id = o.option_poll_id) a
            LEFT JOIN
            (SELECT vote_option_id, count(1) AS vote_num FROM poll_vote v WHERE vote_deleted = 0 GROUP BY v.vote_option_id) b
            ON (a.option_id = b.vote_option_id)
        ";
        $data = mysqli_query($dbConn, $sql);
        $sql = "";

        while($pollData = mysqli_fetch_array($data)){
            $pollId = intval($pollData["poll_id"]);
            $pollQuestion = $pollData["poll_question"];
            $pollType = $pollData["poll_type"];
            $pollDateStart = $pollData["poll_start_datetime"];
            $pollDateEnd = $pollData["poll_end_datetime"];
            $pollIsActive = intval($pollData["is_active"]);
            $optionId = intval($pollData["option_id"]);
            $optionText = $pollData["option_poll_text"];
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

        StopTimer("LoadPolls");
        return $pollModels;
    }

    function LoadLoggedInUserPollVotes(&$loggedInUser){
        global $dbConn;
        AddActionLog("LoadLoggedInUserPollVotes");
        StartTimer("LoadLoggedInUserPollVotes");
    
        $loggedInUserPollVotes = Array();
        
        //Get data about logged in user's votes
        if($loggedInUser !== false){
            $escapedUsername = mysqli_real_escape_string($dbConn, $loggedInUser->Username);
    
            $sql = "
                SELECT o.option_poll_id, o.option_id
                FROM poll_vote v, poll_option o
                WHERE v.vote_option_id = o.option_id
                  AND v.vote_deleted != 1
                  AND v.vote_username = '".$escapedUsername."'
            ";
            $data = mysqli_query($dbConn, $sql);
            $sql = "";
    
            //Get data
            while($userVoteData = mysqli_fetch_array($data)){
                $votePollID = intval($userVoteData["option_poll_id"]);
                $voteOptionID = intval($userVoteData["option_id"]);
                $loggedInUserPollVotes[$votePollID][$voteOptionID] = true;
            }
        }
    
        StopTimer("LoadLoggedInUserPollVotes");
        return $loggedInUserPollVotes;
    }

    function GetPollVotesOfUserFormatted($username){
        global $dbConn;
        AddActionLog("GetPollVotesOfUserFormatted");
        StartTimer("GetPollVotesOfUserFormatted");
    
        $escapedUsername = mysqli_real_escape_string($dbConn, $username);
        $sql = "
            SELECT poll.poll_question, poll_option.option_poll_text, poll_vote.*
            FROM poll_vote, poll_option, poll
            WHERE poll_vote.vote_option_id = poll_option.option_id
              AND poll_option.option_poll_id = poll.poll_id
              AND poll_vote.vote_username = '$escapedUsername';
        ";
        $data = mysqli_query($dbConn, $sql);
        $sql = "";
    
        StopTimer("GetPollVotesOfUserFormatted");
        return ArrayToHTML(MySQLDataToArray($data));
    }
}

?>