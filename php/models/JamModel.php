<?php

define("DB_TABLE_JAM", "jam");
define("DB_COLUMN_JAM_ID",                "jam_id");
define("DB_COLUMN_JAM_DATETIME",          "jam_datetime");
define("DB_COLUMN_JAM_IP",                "jam_ip");
define("DB_COLUMN_JAM_USER_AGENT",        "jam_user_agent");
define("DB_COLUMN_JAM_USER_ID",           "jam_user_id");
define("DB_COLUMN_JAM_NUMBER",            "jam_jam_number");
define("DB_COLUMN_JAM_SELECTED_THEME_ID", "jam_selected_theme_id");
define("DB_COLUMN_JAM_THEME",             "jam_theme");
define("DB_COLUMN_JAM_START_DATETIME",    "jam_start_datetime");
define("DB_COLUMN_JAM_STATE",             "jam_state");
define("DB_COLUMN_JAM_COLORS",            "jam_colors");
define("DB_COLUMN_JAM_DELETED",           "jam_deleted");

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

    private $dbConnection;
    private $publicColumns = Array(DB_COLUMN_JAM_ID, DB_COLUMN_JAM_USER_ID, DB_COLUMN_JAM_NUMBER, DB_COLUMN_JAM_SELECTED_THEME_ID, DB_COLUMN_JAM_THEME, DB_COLUMN_JAM_START_DATETIME, DB_COLUMN_JAM_STATE, DB_COLUMN_JAM_COLORS, DB_COLUMN_JAM_DELETED);
    private $privateColumns = Array(DB_COLUMN_JAM_DATETIME, DB_COLUMN_JAM_IP, DB_COLUMN_JAM_USER_AGENT);

    function __construct(&$dbConn) {
        $this->dbConnection = $dbConn;
        $this->JamModels = $this->LoadJams();
    }

//////////////////////// MODEL CONSTRUCTOR

    function LoadJams(){
        AddActionLog("LoadJams");
        StartTimer("LoadJams");

        $jamModels = Array();

        $data = $this->SelectAll();

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
    function AddJamToDatabase($ip, $userAgent, $userId, $jamNumber, $selectedThemeId, $theme, $startTime, $colors, &$adminLogData){
        AddActionLog("AddJamToDatabase");
        StartTimer("AddJamToDatabase");
    
        $this->Insert($ip, $userAgent, $userId, $jamNumber, $selectedThemeId, $theme, $startTime, $colors);
        
        StopTimer("AddJamToDatabase");
        $adminLogData->AddToAdminLog("JAM_ADDED", "Jam scheduled with values: JamNumber: $jamNumber, Theme: '$theme', StartTime: '$startTime', Colors: $colors", "NULL", ($userId > 0) ? $userId : "NULL", ($userId > 0) ? "" : "AUTOMATIC");
    }

    function UpdateJamStateInDatabase($jamId, $newJamState){
        AddActionLog("ChangeJamStateInDatabase");
        StartTimer("ChangeJamStateInDatabase");

        $data = $this->UpdateJamState($jamId, $newJamState);

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
    
        $data = $this->SelectJamsScheduledByUser($userId);
    
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
    
//////////////////////// FUCNTION SQL

    private function SelectAll(){
        AddActionLog("JamData_SelectAll");
        StartTimer("JamData_SelectAll");

        $sql = "
            SELECT ".DB_COLUMN_JAM_ID.", ".DB_COLUMN_JAM_USER_ID.", ".DB_COLUMN_JAM_NUMBER.", ".DB_COLUMN_JAM_SELECTED_THEME_ID.", ".DB_COLUMN_JAM_THEME.", ".DB_COLUMN_JAM_START_DATETIME.", ".DB_COLUMN_JAM_STATE.", ".DB_COLUMN_JAM_COLORS.", ".DB_COLUMN_JAM_DELETED."
            FROM ".DB_TABLE_JAM." 
            ORDER BY ".DB_COLUMN_JAM_NUMBER." DESC";
        
        StopTimer("JamData_SelectAll");
        return mysqli_query($this->dbConnection, $sql);
    }

    private function SelectJamsScheduledByUser($userId){
        AddActionLog("JamData_SelectJamsScheduledByUser");
        StartTimer("JamData_SelectJamsScheduledByUser");

        $escapedUserId = mysqli_real_escape_string($this->dbConnection, $userId);
        $sql = "
            SELECT *
            FROM ".DB_TABLE_JAM."
            WHERE ".DB_COLUMN_JAM_USER_ID." = '$escapedUserId';
        ";
        
        StopTimer("JamData_SelectJamsScheduledByUser");
        return mysqli_query($this->dbConnection, $sql);
    }

    private function Insert($ip, $userAgent, $userId, $jamNumber, $selectedThemeId, $theme, $startTime, $colors){
        AddActionLog("JamData_Insert");
        StartTimer("JamData_Insert");

        $escapedIp = mysqli_real_escape_string($this->dbConnection, $ip);
        $escapedUserAgent = mysqli_real_escape_string($this->dbConnection, $userAgent);
        $escapedUserId = mysqli_real_escape_string($this->dbConnection, $userId);
        $escapedJamNumber = mysqli_real_escape_string($this->dbConnection, $jamNumber);
        $escapedSelectedThemeId = mysqli_real_escape_string($this->dbConnection, $selectedThemeId);
        $escapedTheme = mysqli_real_escape_string($this->dbConnection, $theme);
        $escapedStartTime = mysqli_real_escape_string($this->dbConnection, $startTime);
        $escapedColors = mysqli_real_escape_string($this->dbConnection, $colors);
    
        $sql = "
            INSERT INTO ".DB_TABLE_JAM."
            (".DB_COLUMN_JAM_ID.",
            ".DB_COLUMN_JAM_DATETIME.",
            ".DB_COLUMN_JAM_IP.",
            ".DB_COLUMN_JAM_USER_AGENT.",
            ".DB_COLUMN_JAM_USER_ID.",
            ".DB_COLUMN_JAM_NUMBER.",
            ".DB_COLUMN_JAM_SELECTED_THEME_ID.",
            ".DB_COLUMN_JAM_THEME.",
            ".DB_COLUMN_JAM_START_DATETIME.",
            ".DB_COLUMN_JAM_STATE.",
            ".DB_COLUMN_JAM_COLORS.",
            ".DB_COLUMN_JAM_DELETED.")
            VALUES
            (null,
            Now(),
            '$escapedIp',
            '$escapedUserAgent',
            $escapedUserId,
            '$escapedJamNumber',
            $escapedSelectedThemeId,
            '$escapedTheme',
            '$escapedStartTime',
            'SCHEDULED',
            '$escapedColors',
            0);";

        mysqli_query($this->dbConnection, $sql);

        StopTimer("JamData_Insert");
        return;
    }

    private function UpdateJamState($jamId, $jamState){
        AddActionLog("JamData_UpdateJamState");
        StartTimer("JamData_UpdateJamState");

        $escapedJamId = mysqli_real_escape_string($this->dbConnection, intval($jamId));
        $escapedJamState = mysqli_real_escape_string($this->dbConnection, $jamState);

        $sql = "
            UPDATE ".DB_TABLE_JAM."
            SET ".DB_COLUMN_JAM_STATE." = '$escapedJamState'
            WHERE ".DB_COLUMN_JAM_ID." = $escapedJamId";
        
        StopTimer("JamData_UpdateJamState");
        return mysqli_query($this->dbConnection, $sql);
    }

//////////////////////// END FUCNTION SQL

//////////////////////// PUBLIC DATA EXPORT

    function GetAllPublicData(){
        AddActionLog("JamData_GetAllPublicData");
        StartTimer("JamData_GetAllPublicData");
        
        $dataFromDatabase = MySQLDataToArray($this->SelectPublicData());

        foreach($dataFromDatabase as $i => $row){
            $dataFromDatabase[$i][DB_COLUMN_JAM_DATETIME] = gmdate("Y-m-d H:i:s", time());
            $dataFromDatabase[$i][DB_COLUMN_JAM_IP] = "MIGRATION";
            $dataFromDatabase[$i][DB_COLUMN_JAM_USER_AGENT] = "MIGRATION";
        }

        StopTimer("JamData_GetAllPublicData");
        return $dataFromDatabase;
    }

    private function SelectPublicData(){
        AddActionLog("JamData_SelectPublicData");
        StartTimer("JamData_SelectPublicData");

        $sql = "
            SELECT ".implode(",", $this->publicColumns)."
            FROM ".DB_TABLE_JAM.";
        ";

        StopTimer("JamData_SelectPublicData");
        return mysqli_query($this->dbConnection, $sql);
    }

//////////////////////// END PUBLIC DATA EXPORT
}

?>