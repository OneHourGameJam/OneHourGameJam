<?php

define("JAM_STATE_COMPLETED", "COMPLETED");
define("JAM_STATE_ACTIVE", "ACTIVE");
define("JAM_STATE_SCHEDULED", "SCHEDULED");
define("JAM_STATE_DELETED", "DELETED");

class JamModel{
	public $Id;
	public $SchedulerUserId;
	public $JamNumber;
	public $ThemeId;
	public $Theme;
	public $StartTime;
	public $State;
	public $Colors;
	public $Deleted;
}

class JamData{
    public $JamModels;

    private $jamDbInterface;

    function __construct(&$jamDbInterface) {
        $this->jamDbInterface = $jamDbInterface;
        $this->JamModels = $this->LoadJams();
    }

//////////////////////// MODEL CONSTRUCTOR

    function LoadJams(){
        AddActionLog("LoadJams");
        StartTimer("LoadJams");

        $jamModels = Array();

        $data = $this->jamDbInterface->SelectAll();

        while($info = mysqli_fetch_array($data)){
            $jamModel = new JamModel();
            $jamID = intval($info[DB_COLUMN_JAM_ID]);

            $jamModel->Id = $jamID;
            $jamModel->SchedulerUserId = $info[DB_COLUMN_JAM_USER_ID];
            $jamModel->JamNumber = intval($info[DB_COLUMN_JAM_NUMBER]);
            $jamModel->ThemeId = intval($info[DB_COLUMN_JAM_SELECTED_THEME_ID]);
            $jamModel->Theme = $info[DB_COLUMN_JAM_THEME];
            $jamModel->StartTime = $info[DB_COLUMN_JAM_START_DATETIME];
            $jamModel->State = $info[DB_COLUMN_JAM_STATE];
            $jamModel->Colors = $this->ParseJamColors($info[DB_COLUMN_JAM_COLORS]);
            $jamModel->Deleted = $info[DB_COLUMN_JAM_DELETED];

            $jamModels[$jamID] = $jamModel;
        }

        StopTimer("LoadJams");
        return $jamModels;
    }

//////////////////////// END MODEL CONSTRUCTOR
    
//////////////////////// DATABASE ACTIONS (select, insert, update)

    //Adds the jam with the provided data into the database
    function AddJamToDatabase($ip, $userAgent, $userId, $jamNumber, $selectedThemeId, $theme, $startTime, $colors){
        AddActionLog("AddJamToDatabase");
        StartTimer("AddJamToDatabase");
    
        $this->jamDbInterface->Insert($ip, $userAgent, $userId, $jamNumber, $selectedThemeId, $theme, $startTime, $colors);
        
        StopTimer("AddJamToDatabase");
    }

    function UpdateJamStateInDatabase($jamId, $newJamState){
        AddActionLog("ChangeJamStateInDatabase");
        StartTimer("ChangeJamStateInDatabase");

        $data = $this->jamDbInterface->UpdateJamState($jamId, $newJamState);

        StopTimer("ChangeJamStateInDatabase");
    }

    function GetNextJamDateAndTime(){
        AddActionLog("GetNextJamDateAndTime");
        StartTimer("GetNextJamDateAndTime");

        $nextJamStartTime = null;

        $now = time();
        foreach($this->JamModels as $i => $jamModel){
            $nextJamTime = strtotime($jamModel->StartTime . " UTC");

            if($nextJamTime > $now){
                $nextJamStartTime = $nextJamTime;
            }
        }

        StopTimer("GetNextJamDateAndTime");
        return $nextJamStartTime;
    }

    // Returns a jam given its number.
    // The dictionary of jams must have been previously loaded.
    function GetJamByNumber($jamNumber) {
        AddActionLog("GetJamByNumber");
        StartTimer("GetJamByNumber");
    
        foreach ($this->JamModels as $jamModel) {
            if ($jamModel->JamNumber == $jamNumber && $jamModel->Deleted != 1) {
                StopTimer("GetJamByNumber");
                return $jamModel;
            }
        }
    
        StopTimer("GetJamByNumber");
        return null;
    }
    
    function GetJamsOfUserFormatted($userId){
        AddActionLog("GetJamsOfUserFormatted");
        StartTimer("GetJamsOfUserFormatted");
    
        $data = $this->jamDbInterface->SelectJamsScheduledByUser($userId);
    
        StopTimer("GetJamsOfUserFormatted");
        return ArrayToHTML(MySQLDataToArray($data));
    }

    function ParseJamColors($colorString){
        AddActionLog("ParseJamColors");
        StartTimer("ParseJamColors");
    
        $jamColors = explode("|", $colorString);
        if(count($jamColors) == 0){
            StopTimer("ParseJamColors");
            return Array("FFFFFF");
        }
    
        StopTimer("ParseJamColors");
        return $jamColors;
    }

//////////////////////// END DATABASE ACTIONS

//////////////////////// PUBLIC DATA EXPORT

    function GetAllPublicData(){
        AddActionLog("JamData_GetAllPublicData");
        StartTimer("JamData_GetAllPublicData");
        
        $dataFromDatabase = MySQLDataToArray($this->jamDbInterface->SelectPublicData());

        foreach($dataFromDatabase as $i => $row){
            $dataFromDatabase[$i][DB_COLUMN_JAM_DATETIME] = gmdate("Y-m-d H:i:s", time());
            $dataFromDatabase[$i][DB_COLUMN_JAM_IP] = OVERRIDE_MIGRATION;
            $dataFromDatabase[$i][DB_COLUMN_JAM_USER_AGENT] = OVERRIDE_MIGRATION;
        }

        StopTimer("JamData_GetAllPublicData");
        return $dataFromDatabase;
    }

//////////////////////// END PUBLIC DATA EXPORT
}

?>