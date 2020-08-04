<?php
define("DB_TABLE_SATISFACTION", "satisfaction");

define("DB_COLUMN_SATISFACTION_ID",             "satisfaction_id");
define("DB_COLUMN_SATISFACTION_DATETIME",       "satisfaction_datetime");
define("DB_COLUMN_SATISFACTION_IP",             "satisfaction_ip");
define("DB_COLUMN_SATISFACTION_USER_AGENT",     "satisfaction_user_agent");
define("DB_COLUMN_SATISFACTION_QUESTION_ID",    "satisfaction_question_id");
define("DB_COLUMN_SATISFACTION_USER_ID",        "satisfaction_user_id");
define("DB_COLUMN_SATISFACTION_SCORE",          "satisfaction_score");

class SatisfactionDbInterface{
    private $dbConnection;
    private $publicColumns = Array(DB_COLUMN_SATISFACTION_ID, DB_COLUMN_SATISFACTION_QUESTION_ID, DB_COLUMN_SATISFACTION_USER_ID);
    private $privateColumns = Array(DB_COLUMN_SATISFACTION_DATETIME, DB_COLUMN_SATISFACTION_IP, DB_COLUMN_SATISFACTION_USER_AGENT, DB_COLUMN_SATISFACTION_SCORE);

    function __construct(&$dbConn) {
        $this->dbConnection = $dbConn;
    }

    public function SelectAllWithPercentageResultsAndCount(){
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
        return mysqli_query($this->dbConnection, $sql);
    }

    public function SelectAllWithAbsoluteResults(){
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
        return mysqli_query($this->dbConnection, $sql);
    }

    public function Insert($satisfactionQuestionId, $ip, $userAgent, $userId, $score){
        AddActionLog("SatisfactionDbInterface_Insert");
        StartTimer("SatisfactionDbInterface_Insert");

        $escapedSatisfactionQuestionId = mysqli_real_escape_string($this->dbConnection, $satisfactionQuestionId);
        $escapedIP = mysqli_real_escape_string($this->dbConnection, $ip);
        $escapedUserAgent = mysqli_real_escape_string($this->dbConnection, $userAgent);
        $escapedUserId = mysqli_real_escape_string($this->dbConnection, $userId);
        $escapedScore = mysqli_real_escape_string($this->dbConnection, $score);
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
        
        mysqli_query($this->dbConnection, $sql);
        StopTimer("SatisfactionDbInterface_Insert");
    }

    public function SelectSatisfactionVotesByUser($userId){
        AddActionLog("SatisfactionDbInterface_SelectSatisfactionVotesByUser");
        StartTimer("SatisfactionDbInterface_SelectSatisfactionVotesByUser");

        $escapedUserId = mysqli_real_escape_string($this->dbConnection, $userId);
        $sql = "
            SELECT *
            FROM ".DB_TABLE_SATISFACTION."
            WHERE ".DB_COLUMN_SATISFACTION_USER_ID." = $escapedUserId;
        ";
        
        StopTimer("SatisfactionDbInterface_SelectSatisfactionVotesByUser");
        return mysqli_query($this->dbConnection, $sql);
    }

    public function SelectPublicData(){
        AddActionLog("SatisfactionDbInterface_SelectPublicData");
        StartTimer("SatisfactionDbInterface_SelectPublicData");

        $sql = "
            SELECT ".implode(",", $this->publicColumns)."
            FROM ".DB_TABLE_SATISFACTION.";
        ";

        StopTimer("SatisfactionDbInterface_SelectPublicData");
        return mysqli_query($this->dbConnection, $sql);
    }
}

?>