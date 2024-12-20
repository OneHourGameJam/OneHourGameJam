<?php
const DB_TABLE_SATISFACTION = "satisfaction";

const DB_COLUMN_SATISFACTION_ID = "satisfaction_id";
const DB_COLUMN_SATISFACTION_DATETIME = "satisfaction_datetime";
const DB_COLUMN_SATISFACTION_IP = "satisfaction_ip";
const DB_COLUMN_SATISFACTION_USER_AGENT = "satisfaction_user_agent";
const DB_COLUMN_SATISFACTION_QUESTION_ID = "satisfaction_question_id";
const DB_COLUMN_SATISFACTION_USER_ID = "satisfaction_user_id";
const DB_COLUMN_SATISFACTION_SCORE = "satisfaction_score";

class SatisfactionDbInterface{
    private Database $database;
    private array $publicColumns = Array(DB_COLUMN_SATISFACTION_ID, DB_COLUMN_SATISFACTION_QUESTION_ID, DB_COLUMN_SATISFACTION_USER_ID);
    private array $privateColumns = Array(DB_COLUMN_SATISFACTION_DATETIME, DB_COLUMN_SATISFACTION_IP, DB_COLUMN_SATISFACTION_USER_AGENT, DB_COLUMN_SATISFACTION_SCORE);

    function __construct(Database &$database) {
        $this->database = $database;
    }

    public function SelectAllWithPercentageResultsAndCount(): mysqli_result
    {
        AddActionLog("SatisfactionDbInterface_SelectAllWithPercentageResultsAndCount");
        StartTimer("SatisfactionDbInterface_SelectAllWithPercentageResultsAndCount");

        $sql = "
            SELECT
                ".DB_COLUMN_SATISFACTION_QUESTION_ID.",
                AVG(".DB_COLUMN_SATISFACTION_SCORE.") AS average_score,
                COUNT(".DB_COLUMN_SATISFACTION_SCORE.") AS submitted_scores
            FROM ".DB_TABLE_SATISFACTION."
            GROUP BY ".DB_COLUMN_SATISFACTION_QUESTION_ID."
        ";
        
        StopTimer("SatisfactionDbInterface_SelectAllWithPercentageResultsAndCount");
        return $this->database->Execute($sql);;
    }

    public function SelectAllWithAbsoluteResults(): mysqli_result
    {
        AddActionLog("SatisfactionDbInterface_SelectAllWithAbsoluteResults");
        StartTimer("SatisfactionDbInterface_SelectAllWithAbsoluteResults");

        $sql = "
            SELECT
                ".DB_COLUMN_SATISFACTION_QUESTION_ID.",
                ".DB_COLUMN_SATISFACTION_SCORE.",
                COUNT(1) AS votes_for_score
            FROM ".DB_TABLE_SATISFACTION."
            GROUP BY ".DB_COLUMN_SATISFACTION_QUESTION_ID.", ".DB_COLUMN_SATISFACTION_SCORE."
        ";
        
        StopTimer("SatisfactionDbInterface_SelectAllWithAbsoluteResults");
        return $this->database->Execute($sql);;
    }

    public function Insert($satisfactionQuestionId, $ip, $userAgent, $userId, $score): void
    {
        AddActionLog("SatisfactionDbInterface_Insert");
        StartTimer("SatisfactionDbInterface_Insert");

        $escapedSatisfactionQuestionId = $this->database->EscapeString($satisfactionQuestionId);
        $escapedIP = $this->database->EscapeString($ip);
        $escapedUserAgent = $this->database->EscapeString($userAgent);
        $escapedUserId = $this->database->EscapeString($userId);
        $escapedScore = $this->database->EscapeString($score);
        $sql = "
            INSERT INTO ".DB_TABLE_SATISFACTION."
            (".DB_COLUMN_SATISFACTION_ID.",
            ".DB_COLUMN_SATISFACTION_DATETIME.",
            ".DB_COLUMN_SATISFACTION_IP.",
            ".DB_COLUMN_SATISFACTION_USER_AGENT.",
            ".DB_COLUMN_SATISFACTION_QUESTION_ID.",
            ".DB_COLUMN_SATISFACTION_USER_ID.",
            ".DB_COLUMN_SATISFACTION_SCORE.")
            VALUES
            (null,
            Now(),
            '$escapedIP',
            '$escapedUserAgent',
            '$escapedSatisfactionQuestionId',
            $escapedUserId,
            '$escapedScore');";
        
        $this->database->Execute($sql);;
        StopTimer("SatisfactionDbInterface_Insert");
    }

    public function SelectSatisfactionVotesByUser($userId): mysqli_result
    {
        AddActionLog("SatisfactionDbInterface_SelectSatisfactionVotesByUser");
        StartTimer("SatisfactionDbInterface_SelectSatisfactionVotesByUser");

        $escapedUserId = $this->database->EscapeString($userId);
        $sql = "
            SELECT *
            FROM ".DB_TABLE_SATISFACTION."
            WHERE ".DB_COLUMN_SATISFACTION_USER_ID." = $escapedUserId;
        ";
        
        StopTimer("SatisfactionDbInterface_SelectSatisfactionVotesByUser");
        return $this->database->Execute($sql);;
    }

    public function SelectPublicData(): mysqli_result
    {
        AddActionLog("SatisfactionDbInterface_SelectPublicData");
        StartTimer("SatisfactionDbInterface_SelectPublicData");

        $sql = "
            SELECT ".implode(",", $this->publicColumns)."
            FROM ".DB_TABLE_SATISFACTION.";
        ";

        StopTimer("SatisfactionDbInterface_SelectPublicData");
        return $this->database->Execute($sql);;
    }
}

?>