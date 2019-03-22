<?php

class SatisfactionModel{
	public $QuestionId;
	public $AverageScore;
	public $SubmittedScores;
	public $EnoughScoresToShowSatisfaction;
	public $Scores;
}

class SatisfactionData{
    public $SatisfactionModels;

    function __construct(&$configData) {
        $this->SatisfactionModels = $this->LoadSatisfaction($configData);
    }

    function LoadSatisfaction(&$configData){
        global $dbConn;
        AddActionLog("LoadSatisfaction");
        StartTimer("LoadSatisfaction");
    
        $satisfactionModels = Array();
    
        $sql = "
            SELECT
                satisfaction_question_id,
                AVG(satisfaction_score) AS average_score,
                COUNT(satisfaction_score) AS submitted_scores
            FROM satisfaction
            GROUP BY satisfaction_question_id
        ";
        $data = mysqli_query($dbConn, $sql);
        $sql = "";
    
        //Get data
        while($satisfactionData = mysqli_fetch_array($data)){
            $satisfactionModel = new SatisfactionModel();
    
            $questionId = $satisfactionData["satisfaction_question_id"];
            $averageScore = $satisfactionData["average_score"];
            $submittedScores = $satisfactionData["submitted_scores"];
    
            $satisfactionModel->QuestionId = $questionId;
            $satisfactionModel->AverageScore = $averageScore;
            $satisfactionModel->SubmittedScores = $submittedScores;
            $satisfactionModel->EnoughScoresToShowSatisfaction = $submittedScores >= $configData->ConfigModels["SATISFACTION_RATINGS_TO_SHOW_SCORE"]->Value;
            
            for($score = -5; $score <= 5; $score++){
                $satisfactionModel->Scores[$score] = 0;
            }
    
            $satisfactionModels[$questionId] = $satisfactionModel;
        }
    
        $sql = "
            SELECT
                satisfaction_question_id,
                satisfaction_score,
                COUNT(1) AS votes_for_score
            FROM satisfaction
            GROUP BY satisfaction_question_id, satisfaction_score
        ";
        $data = mysqli_query($dbConn, $sql);
        $sql = "";
    
        //Get data
        while($info = mysqli_fetch_array($data)){
            $questionId = $info["satisfaction_question_id"];
            $satisfactionScore = $info["satisfaction_score"];
            $votesForScore = $info["votes_for_score"];
    
            $satisfactionModels[$questionId]->Scores[$satisfactionScore] = $votesForScore;
        }
    
        StopTimer("LoadSatisfaction");
        return $satisfactionModels;
    }

    function SubmitSatisfaction(&$loggedInUser, $satisfactionQuestionId, $score){
        global $dbConn, $ip, $userAgent;
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
    
        $username = trim($loggedInUser->Username);
    
        $escapedSatisfactionQuestionId = mysqli_real_escape_string($dbConn, $satisfactionQuestionId);
        $escapedIP = mysqli_real_escape_string($dbConn, $ip);
        $escapedUserAgent = mysqli_real_escape_string($dbConn, $userAgent);
        $escapedUsername = mysqli_real_escape_string($dbConn, $username);
        $escapedScore = mysqli_real_escape_string($dbConn, $score);
    
        $sql = "
            INSERT INTO satisfaction
            (satisfaction_id,
            satisfaction_datetime,
            satisfaction_ip,
            satisfaction_user_agent,
            satisfaction_question_id,
            satisfaction_username,
            satisfaction_score)
            VALUES
            (null,
            Now(),
            '$escapedIP',
            '$escapedUserAgent',
            '$escapedSatisfactionQuestionId',
            '$escapedUsername',
            '$escapedScore');";
        $data = mysqli_query($dbConn, $sql);
        $sql = "";
    
        StopTimer("SubmitSatisfaction");
    }
    
    function GetSatisfactionVotesOfUserFormatted($username){
        global $dbConn;
        AddActionLog("GetSatisfactionVotesOfUserFormatted");
        StartTimer("GetSatisfactionVotesOfUserFormatted");
    
        $escapedUsername = mysqli_real_escape_string($dbConn, $username);
        $sql = "
            SELECT *
            FROM satisfaction
            WHERE satisfaction_username = '$escapedUsername';
        ";
        $data = mysqli_query($dbConn, $sql);
        $sql = "";
    
        StopTimer("GetSatisfactionVotesOfUserFormatted");
        return ArrayToHTML(MySQLDataToArray($data));
    }
}

?>