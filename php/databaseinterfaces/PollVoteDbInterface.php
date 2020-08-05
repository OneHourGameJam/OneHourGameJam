<?php
define("DB_TABLE_POLLVOTE", "poll_vote");

define("DB_COLUMN_POLLVOTE_ID",         "vote_id");
define("DB_COLUMN_POLLVOTE_OPTION_ID",  "vote_option_id");
define("DB_COLUMN_POLLVOTE_USER_ID",    "vote_user_id");
define("DB_COLUMN_POLLVOTE_DELETED",    "vote_deleted");

class PollVoteDbInterface{
    private $dbConnection;
    private $publicColumns = Array(DB_COLUMN_POLLVOTE_ID, DB_COLUMN_POLLVOTE_USER_ID, DB_COLUMN_POLLVOTE_DELETED);
    private $privateColumns = Array(DB_COLUMN_POLLVOTE_OPTION_ID);

    function __construct(&$dbConn) {
        $this->dbConnection = $dbConn;
    }

    public function SelectUserVoteForOption($userId, $pollOptionId){
        AddActionLog("PollVoteDbInterface_SelectUserVoteForOption");
        StartTimer("PollVoteDbInterface_SelectUserVoteForOption");

        $escapedUserId = mysqli_real_escape_string($this->dbConnection, $userId);
        $escapedPollOptionId = mysqli_real_escape_string($this->dbConnection, $pollOptionId);
        $sql = "
            SELECT vote_id, vote_deleted 
            FROM poll_vote 
            WHERE vote_option_id = $escapedPollOptionId 
              AND vote_user_id = $escapedUserId";
        
        StopTimer("PollVoteDbInterface_SelectUserVoteForOption");
        return mysqli_query($this->dbConnection, $sql);
    }

    public function Insert($userId, $pollOptionId){
        AddActionLog("PollVoteDbInterface_Insert");
        StartTimer("PollVoteDbInterface_Insert");

        $escapedUserId = mysqli_real_escape_string($this->dbConnection, $userId);
        $escapedPollOptionId = mysqli_real_escape_string($this->dbConnection, $pollOptionId);

        $sql = "
            INSERT INTO poll_vote
            (vote_id, vote_option_id, vote_user_id, vote_deleted)
            VALUES
            (null, $escapedPollOptionId, $escapedUserId, 0);";
        
        mysqli_query($this->dbConnection, $sql);

        StopTimer("PollVoteDbInterface_Insert");
    }

    public function UpdateIsDeleted($pollVoteId, $isDeleted){
        AddActionLog("PollVoteDbInterface_UpdateIsDeleted");
        StartTimer("PollVoteDbInterface_UpdateIsDeleted");

        $escapedPollVoteId = mysqli_real_escape_string($this->dbConnection, $pollVoteId);
        $escapedIsDeleted = mysqli_real_escape_string($this->dbConnection, $isDeleted);

        $sql = "
            UPDATE poll_vote 
            SET vote_deleted = $escapedIsDeleted 
            WHERE vote_id = $escapedPollVoteId";
        
        mysqli_query($this->dbConnection, $sql);

        StopTimer("PollVoteDbInterface_UpdateIsDeleted");
    }

    public function SelectPublicData(){
        AddActionLog("PollVoteDbInterface_SelectPublicData");
        StartTimer("PollVoteDbInterface_SelectPublicData");

        $sql = "
            SELECT ".implode(",", $this->publicColumnsPollVote)."
            FROM ".DB_TABLE_POLLVOTE.";
        ";

        StopTimer("PollVoteDbInterface_SelectPublicData");
        return mysqli_query($this->dbConnection, $sql);
    }
}

?>