<?php
define("DB_TABLE_SATISFACTION", "satisfaction");

define("DB_COLUMN_SATISFACTION_ID",             "satisfaction_id");
define("DB_COLUMN_SATISFACTION_DATETIME",       "satisfaction_datetime");
define("DB_COLUMN_SATISFACTION_IP",             "satisfaction_ip");
define("DB_COLUMN_SATISFACTION_USER_AGENT",     "satisfaction_user_agent");
define("DB_COLUMN_SATISFACTION_QUESTION_ID",    "satisfaction_question_id");
define("DB_COLUMN_SATISFACTION_USER_ID",        "satisfaction_user_id");
define("DB_COLUMN_SATISFACTION_SCORE",          "satisfaction_score");

class SatisfactionModel{
	public $QuestionId;
	public $AverageScore;
	public $SubmittedScores;
	public $EnoughScoresToShowSatisfaction;
	public $Scores;
}

class SatisfactionData{
    public $SatisfactionModels;

    private $dbConnection;
    private $publicColumns = Array(DB_COLUMN_SATISFACTION_ID, DB_COLUMN_SATISFACTION_QUESTION_ID, DB_COLUMN_SATISFACTION_USER_ID);
    private $privateColumns = Array(DB_COLUMN_SATISFACTION_DATETIME, DB_COLUMN_SATISFACTION_IP, DB_COLUMN_SATISFACTION_USER_AGENT, DB_COLUMN_SATISFACTION_SCORE);

    function __construct(&$dbConn, &$configData) {
        $this->dbConnection = $dbConn;
        $this->SatisfactionModels = $this->LoadSatisfaction($configData);
    }

//////////////////////// MODEL CONSTRUCTOR

    function LoadSatisfaction(&$configData){
        AddActionLog("LoadSatisfaction");
        StartTimer("LoadSatisfaction");
    
        $data = $this->SelectAllWithPercentageResultsAndCount();
    
        $satisfactionModels = Array();
        while($satisfactionData = mysqli_fetch_array($data)){
            $satisfactionModel = new SatisfactionModel();
    
            $questionId = $satisfactionData[DB_COLUMN_SATISFACTION_QUESTION_ID];
            $averageScore = $satisfactionData["average_score"];
            $submittedScores = $satisfactionData["submitted_scores"];
    
            $satisfactionModel->QuestionId = $questionId;
            $satisfactionModel->AverageScore = $averageScore;
            $satisfactionModel->SubmittedScores = $submittedScores;
            $satisfactionModel->EnoughScoresToShowSatisfaction = $submittedScores >= $configData->ConfigModels[CONFIG_SATISFACTION_RATINGS_TO_SHOW_SCORE]->Value;
            
            for($score = -5; $score <= 5; $score++){
                $satisfactionModel->Scores[$score] = 0;
            }
    
            $satisfactionModels[$questionId] = $satisfactionModel;
        }
    
        $data = $this->SelectAllWithAbsoluteResults();
    
        while($info = mysqli_fetch_array($data)){
            $questionId = $info[DB_COLUMN_SATISFACTION_QUESTION_ID];
            $satisfactionScore = $info[DB_COLUMN_SATISFACTION_SCORE];
            $votesForScore = $info["votes_for_score"];
    
            $satisfactionModels[$questionId]->Scores[$satisfactionScore] = $votesForScore;
        }
    
        StopTimer("LoadSatisfaction");
        return $satisfactionModels;
    }

//////////////////////// END MODEL CONSTRUCTOR
    
//////////////////////// DATABASE ACTIONS (select, insert, update)

    function SubmitSatisfaction(&$loggedInUser, $satisfactionQuestionId, $score){
        global $ip, $userAgent;
        AddActionLog("SubmitSatisfaction");
        StartTimer("SubmitSatisfaction");
    
        if($score < -5){
            StopTimer("SubmitSatisfaction");
            return;
        }
        if($score > 5){
            StopTimer("SubmitSatisfaction");
            return;
        }

        $data = $this->Insert($satisfactionQuestionId, $ip, $userAgent, $loggedInUser->Id, $score);
    
        StopTimer("SubmitSatisfaction");
    }
    
    function GetSatisfactionVotesOfUserFormatted($userId){
        AddActionLog("GetSatisfactionVotesOfUserFormatted");
        StartTimer("GetSatisfactionVotesOfUserFormatted");
    
        $data = $this->SelectSatisfactionVotesByUser($userId);
    
        StopTimer("GetSatisfactionVotesOfUserFormatted");
        return ArrayToHTML(MySQLDataToArray($data));
    }

//////////////////////// END DATABASE ACTIONS
    
//////////////////////// FUCNTION SQL

    private function SelectAllWithPercentageResultsAndCount(){
        AddActionLog("SatisfactionData_SelectAllWithPercentageResultsAndCount");
        StartTimer("SatisfactionData_SelectAllWithPercentageResultsAndCount");

        $sql = "
            SELECT
                ".DB_COLUMN_SATISFACTION_QUESTION_ID.",
                AVG(".DB_COLUMN_SATISFACTION_SCORE.") AS average_score,
                COUNT(".DB_COLUMN_SATISFACTION_SCORE.") AS submitted_scores
            FROM ".DB_TABLE_SATISFACTION."
            GROUP BY ".DB_COLUMN_SATISFACTION_QUESTION_ID."
        ";
        
        StopTimer("SatisfactionData_SelectAllWithPercentageResultsAndCount");
        return mysqli_query($this->dbConnection, $sql);
    }

    private function SelectAllWithAbsoluteResults(){
        AddActionLog("SatisfactionData_SelectAllWithAbsoluteResults");
        StartTimer("SatisfactionData_SelectAllWithAbsoluteResults");

        $sql = "
            SELECT
                ".DB_COLUMN_SATISFACTION_QUESTION_ID.",
                ".DB_COLUMN_SATISFACTION_SCORE.",
                COUNT(1) AS votes_for_score
            FROM ".DB_TABLE_SATISFACTION."
            GROUP BY ".DB_COLUMN_SATISFACTION_QUESTION_ID.", ".DB_COLUMN_SATISFACTION_SCORE."
        ";
        
        StopTimer("SatisfactionData_SelectAllWithAbsoluteResults");
        return mysqli_query($this->dbConnection, $sql);
    }

    private function Insert($satisfactionQuestionId, $ip, $userAgent, $userId, $score){
        AddActionLog("SatisfactionData_Insert");
        StartTimer("SatisfactionData_Insert");

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
        StopTimer("SatisfactionData_Insert");
    }

    private function SelectSatisfactionVotesByUser($userId){
        AddActionLog("SatisfactionData_SelectAll");
        StartTimer("SatisfactionData_SelectAll");

        $escapedUserId = mysqli_real_escape_string($this->dbConnection, $userId);
        $sql = "
            SELECT *
            FROM ".DB_TABLE_SATISFACTION."
            WHERE ".DB_COLUMN_SATISFACTION_USER_ID." = $escapedUserId;
        ";
        
        StopTimer("SatisfactionData_SelectAll");
        return mysqli_query($this->dbConnection, $sql);
    }

//////////////////////// END FUCNTION SQL

//////////////////////// PUBLIC DATA EXPORT

    function GetAllPublicData(){
        AddActionLog("SatisfactionData_GetAllPublicData");
        StartTimer("SatisfactionData_GetAllPublicData");
        
        $dataFromDatabase = MySQLDataToArray($this->SelectPublicData());

        foreach($dataFromDatabase as $i => $row){
            $dataFromDatabase[$i][DB_COLUMN_SATISFACTION_DATETIME] = gmdate("Y-m-d H:i:s", time());
            $dataFromDatabase[$i][DB_COLUMN_SATISFACTION_IP] = "MIGRATION";
            $dataFromDatabase[$i][DB_COLUMN_SATISFACTION_USER_AGENT] = "MIGRATION";
            $dataFromDatabase[$i][DB_COLUMN_SATISFACTION_SCORE] = rand(-5, 5);
        }

        StopTimer("SatisfactionData_GetAllPublicData");
        return $dataFromDatabase;
    }

    private function SelectPublicData(){
        AddActionLog("SatisfactionData_SelectPublicData");
        StartTimer("SatisfactionData_SelectPublicData");

        $sql = "
            SELECT ".implode(",", $this->publicColumns)."
            FROM ".DB_TABLE_SATISFACTION.";
        ";

        StopTimer("SatisfactionData_SelectPublicData");
        return mysqli_query($this->dbConnection, $sql);
    }

//////////////////////// END PUBLIC DATA EXPORT
}

?>