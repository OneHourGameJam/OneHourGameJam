<?php
const DB_TABLE_POLLVOTE = "poll_vote";

const DB_COLUMN_POLLVOTE_ID = "vote_id";
const DB_COLUMN_POLLVOTE_OPTION_ID = "vote_option_id";
const DB_COLUMN_POLLVOTE_USER_ID = "vote_user_id";
const DB_COLUMN_POLLVOTE_DELETED = "vote_deleted";

class PollVoteDbInterface{
    private Database $database;
    private array $publicColumns = Array(DB_COLUMN_POLLVOTE_ID, DB_COLUMN_POLLVOTE_USER_ID, DB_COLUMN_POLLVOTE_DELETED);
    private array $privateColumns = Array(DB_COLUMN_POLLVOTE_OPTION_ID);

    function __construct(Database &$database) {
        $this->database = $database;
    }

    public function SelectUserVoteForOption($userId, $pollOptionId): mysqli_result
    {
        AddActionLog("PollVoteDbInterface_SelectUserVoteForOption");
        StartTimer("PollVoteDbInterface_SelectUserVoteForOption");

        $escapedUserId = $this->database->EscapeString($userId);
        $escapedPollOptionId = $this->database->EscapeString($pollOptionId);
        $sql = "
            SELECT ".DB_COLUMN_POLLVOTE_ID.", ".DB_COLUMN_POLLVOTE_DELETED." 
            FROM ".DB_TABLE_POLLVOTE." 
            WHERE ".DB_COLUMN_POLLVOTE_OPTION_ID." = $escapedPollOptionId 
              AND ".DB_COLUMN_POLLVOTE_USER_ID." = $escapedUserId";
        
        StopTimer("PollVoteDbInterface_SelectUserVoteForOption");
        return $this->database->Execute($sql);;
    }

    public function Insert($userId, $pollOptionId): void
    {
        AddActionLog("PollVoteDbInterface_Insert");
        StartTimer("PollVoteDbInterface_Insert");

        $escapedUserId = $this->database->EscapeString($userId);
        $escapedPollOptionId = $this->database->EscapeString($pollOptionId);

        $sql = "
            INSERT INTO ".DB_TABLE_POLLVOTE."
            (".DB_COLUMN_POLLVOTE_ID.", ".DB_COLUMN_POLLVOTE_OPTION_ID.", ".DB_COLUMN_POLLVOTE_USER_ID.", ".DB_COLUMN_POLLVOTE_DELETED.")
            VALUES
            (null, $escapedPollOptionId, $escapedUserId, 0);";
        
        $this->database->Execute($sql);;

        StopTimer("PollVoteDbInterface_Insert");
    }

    public function UpdateIsDeleted($pollVoteId, $isDeleted): void
    {
        AddActionLog("PollVoteDbInterface_UpdateIsDeleted");
        StartTimer("PollVoteDbInterface_UpdateIsDeleted");

        $escapedPollVoteId = $this->database->EscapeString($pollVoteId);
        $escapedIsDeleted = $this->database->EscapeString($isDeleted);

        $sql = "
            UPDATE ".DB_TABLE_POLLVOTE." 
            SET ".DB_COLUMN_POLLVOTE_DELETED." = $escapedIsDeleted 
            WHERE ".DB_COLUMN_POLLVOTE_ID." = $escapedPollVoteId";
        
        $this->database->Execute($sql);;

        StopTimer("PollVoteDbInterface_UpdateIsDeleted");
    }

    public function SelectPublicData(): mysqli_result
    {
        AddActionLog("PollVoteDbInterface_SelectPublicData");
        StartTimer("PollVoteDbInterface_SelectPublicData");

        $sql = "
            SELECT ".implode(",", $this->publicColumns)."
            FROM ".DB_TABLE_POLLVOTE.";
        ";

        StopTimer("PollVoteDbInterface_SelectPublicData");
        return $this->database->Execute($sql);;
    }
}

?>