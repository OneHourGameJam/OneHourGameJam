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

    function __construct() {
        $this->PollModels = $this->LoadPolls();
    }

    function LoadPolls(){
        global $dbConn;
        AddActionLog("LoadPolls");
        StartTimer("LoadPolls");

        $polls = Array();
        
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

            if(!isset($polls[$pollId])){
                $poll = new PollModel();

                $poll->Id = $pollId;
                $poll->Question = $pollQuestion;
                $poll->PollType = $pollType;
                $poll->DateStart = $pollDateStart;
                $poll->DateEnd = $pollDateEnd;
                $poll->IsActive = $pollIsActive;
                $poll->Options = Array();

                $polls[$pollId] = $poll;
            }

            $pollOption = new PollOptionModel();

            $pollOption->Id = $optionId;
            $pollOption->Text = $optionText;
            $pollOption->Votes = $optionVotes;

            $poll->Options[$optionId] = $pollOption;
        }

        StopTimer("LoadPolls");
        return $polls;
    }
}

?>