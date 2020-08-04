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