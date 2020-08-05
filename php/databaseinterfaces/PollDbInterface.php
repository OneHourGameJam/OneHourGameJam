<?php
define("DB_TABLE_POLL", "poll");

define("DB_COLUMN_POLL_ID",             "poll_id");
define("DB_COLUMN_POLL_QUESTION",       "poll_question");
define("DB_COLUMN_POLL_TYPE",           "poll_type");
define("DB_COLUMN_POLL_START_DATETIME", "poll_start_datetime");
define("DB_COLUMN_POLL_END_DATETIME",   "poll_end_datetime");
define("DB_COLUMN_POLL_DELETED",        "poll_deleted");

class PollDbInterface{
    private $dbConnection;
    private $publicColumns = Array(DB_COLUMN_POLL_ID, DB_COLUMN_POLL_QUESTION, DB_COLUMN_POLL_TYPE, DB_COLUMN_POLL_START_DATETIME, DB_COLUMN_POLL_END_DATETIME, DB_COLUMN_POLL_DELETED);
    private $privateColumns = Array();

    function __construct(&$dbConn) {
        $this->dbConnection = $dbConn;
    }

    public function SelectAllPollOptionsAndVotes(){
        AddActionLog("PollDbInterface_SelectAllPollOptionsAndVotes");
        StartTimer("PollDbInterface_SelectAllPollOptionsAndVotes");

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
        
        StopTimer("PollDbInterface_SelectAllPollOptionsAndVotes");
        return mysqli_query($this->dbConnection, $sql);
    }

    public function SelectNumberOfUsersVotedInEachPoll(){
        AddActionLog("PollDbInterface_SelectNumberOfUsersVotedInEachPoll");
        StartTimer("PollDbInterface_SelectNumberOfUsersVotedInEachPoll");

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
        
        StopTimer("PollDbInterface_SelectNumberOfUsersVotedInEachPoll");
        return mysqli_query($this->dbConnection, $sql);
    }

    public function SelectActivePollVotesOfUser($userId){
        AddActionLog("PollDbInterface_SelectActivePollVotesOfUser");
        StartTimer("PollDbInterface_SelectActivePollVotesOfUser");

        $escapedUserId = mysqli_real_escape_string($this->dbConnection, $userId);
        $sql = "
            SELECT o.".DB_COLUMN_POLLOPTION_POLL_ID.", o.".DB_COLUMN_POLLOPTION_ID."
            FROM ".DB_TABLE_POLLVOTE." v, ".DB_TABLE_POLLOPTION." o
            WHERE v.".DB_COLUMN_POLLVOTE_OPTION_ID." = o.".DB_COLUMN_POLLOPTION_ID."
            AND v.".DB_COLUMN_POLLVOTE_DELETED." != 1
            AND v.".DB_COLUMN_POLLVOTE_USER_ID." = ".$escapedUserId."
        ";
        
        StopTimer("PollDbInterface_SelectActivePollVotesOfUser");
        return mysqli_query($this->dbConnection, $sql);
    }

    public function SelectPollVotesOfUser($userId){
        AddActionLog("PollDbInterface_SelectPollVotesOfUser");
        StartTimer("PollDbInterface_SelectPollVotesOfUser");

        $escapedUserId = mysqli_real_escape_string($this->dbConnection, $userId);
        $sql = "
            SELECT p.".DB_COLUMN_POLL_QUESTION.", o.".DB_COLUMN_POLLOPTION_POLL_TEXT.", v.*
            FROM ".DB_TABLE_POLLVOTE." v, ".DB_TABLE_POLLOPTION." o, ".DB_TABLE_POLL." p
            WHERE v.".DB_COLUMN_POLLVOTE_OPTION_ID." = o.".DB_COLUMN_POLLOPTION_ID."
            AND o.".DB_COLUMN_POLLOPTION_POLL_ID." = p.".DB_COLUMN_POLL_ID."
            AND v.".DB_COLUMN_POLLVOTE_USER_ID." = $escapedUserId;
        ";
        
        StopTimer("PollDbInterface_SelectPollVotesOfUser");
        return mysqli_query($this->dbConnection, $sql);
    }

    public function SelectIfPollAndPollOptionCombinationExists($pollId, $pollOptionId){
        AddActionLog("PollDbInterface_SelectIfPollAndPollOptionCombinationExists");
        StartTimer("PollDbInterface_SelectIfPollAndPollOptionCombinationExists");

        $escapedPollId = mysqli_real_escape_string($this->dbConnection, $pollId);
        $escapedPollOptionId = mysqli_real_escape_string($this->dbConnection, $pollOptionId);
        $sql = "
            SELECT 1 
            FROM poll p, poll_option o 
            WHERE p.poll_deleted != 1 
              AND NOW() BETWEEN p.poll_start_datetime AND p.poll_end_datetime 
              AND p.poll_id = o.option_poll_id 
              AND poll_id = $escapedPollId 
              AND o.option_id = $escapedPollOptionId";
        
        StopTimer("PollDbInterface_SelectIfPollAndPollOptionCombinationExists");
        return mysqli_query($this->dbConnection, $sql);
    }

    public function SelectPublicData(){
        AddActionLog("PollDbInterface_SelectPublicData");
        StartTimer("PollDbInterface_SelectPublicData");

        $sql = "
            SELECT ".implode(",", $this->publicColumnsPoll)."
            FROM ".DB_TABLE_POLL.";
        ";

        StopTimer("PollDbInterface_SelectPublicData");
        return mysqli_query($this->dbConnection, $sql);
    }
}

?>