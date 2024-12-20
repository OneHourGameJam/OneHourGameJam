<?php

class SatisfactionModel{
	public string $QuestionId;
	public float $AverageScore;
	public int $SubmittedScores;
	public bool $EnoughScoresToShowSatisfaction;
	public array $Scores;
}

class SatisfactionData{
    public array $SatisfactionModels;
    
    private SatisfactionDbInterface $satisfactionDbInterface;

    function __construct(SatisfactionDbInterface &$satisfactionDbInterface, &$configData) {
        $this->satisfactionDbInterface = $satisfactionDbInterface;
        $this->SatisfactionModels = $this->LoadSatisfaction($configData);
    }

//////////////////////// MODEL CONSTRUCTOR

    function LoadSatisfaction(ConfigData &$configData): array
    {
        AddActionLog("LoadSatisfaction");
        StartTimer("LoadSatisfaction");
    
        $data = $this->satisfactionDbInterface->SelectAllWithPercentageResultsAndCount();
    
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
    
        $data = $this->satisfactionDbInterface->SelectAllWithAbsoluteResults();
    
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

    function SubmitSatisfaction(&$loggedInUser, $satisfactionQuestionId, $score): void
    {
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

        $this->satisfactionDbInterface->Insert($satisfactionQuestionId, $ip, $userAgent, $loggedInUser->Id, $score);
    
        StopTimer("SubmitSatisfaction");
    }
    
    function GetSatisfactionVotesOfUserFormatted($userId): string
    {
        AddActionLog("GetSatisfactionVotesOfUserFormatted");
        StartTimer("GetSatisfactionVotesOfUserFormatted");
    
        $data = $this->satisfactionDbInterface->SelectSatisfactionVotesByUser($userId);
    
        StopTimer("GetSatisfactionVotesOfUserFormatted");
        return ArrayToHTML(MySQLDataToArray($data));
    }

//////////////////////// END DATABASE ACTIONS

//////////////////////// PUBLIC DATA EXPORT

    function GetAllPublicData(): array
    {
        AddActionLog("SatisfactionData_GetAllPublicData");
        StartTimer("SatisfactionData_GetAllPublicData");
        
        $dataFromDatabase = MySQLDataToArray($this->satisfactionDbInterface->SelectPublicData());

        foreach($dataFromDatabase as $i => $row){
            $dataFromDatabase[$i][DB_COLUMN_SATISFACTION_DATETIME] = gmdate("Y-m-d H:i:s", time());
            $dataFromDatabase[$i][DB_COLUMN_SATISFACTION_IP] = OVERRIDE_MIGRATION;
            $dataFromDatabase[$i][DB_COLUMN_SATISFACTION_USER_AGENT] = OVERRIDE_MIGRATION;
            $dataFromDatabase[$i][DB_COLUMN_SATISFACTION_SCORE] = rand(-5, 5);
        }

        StopTimer("SatisfactionData_GetAllPublicData");
        return $dataFromDatabase;
    }

//////////////////////// END PUBLIC DATA EXPORT
}

?>