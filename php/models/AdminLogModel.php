<?php

define("DB_TABLE_ADMIN_LOG", "admin_log");
define("DB_COLUMN_ADMIN_LOG_ID", "log_id");
define("DB_COLUMN_ADMIN_LOG_DATETIME", "log_datetime");
define("DB_COLUMN_ADMIN_LOG_IP", "log_ip");
define("DB_COLUMN_ADMIN_LOG_USER_AGENT", "log_user_agent");
define("DB_COLUMN_ADMIN_LOG_ADMIN_USERNAME_OVERRIDE", "log_admin_username_override");
define("DB_COLUMN_ADMIN_LOG_ADMIN_USER_ID", "log_admin_user_id");
define("DB_COLUMN_ADMIN_LOG_SUBJECT_USER_ID", "log_subject_user_id");
define("DB_COLUMN_ADMIN_LOG_TYPE", "log_type");
define("DB_COLUMN_ADMIN_LOG_CONTENT", "log_content");

class AdminLogModel{
	public $Id;
	public $DateTime;
	public $Ip;
	public $UserAgent;
	public $AdminUsernameOverride;
	public $AdminUserId;
	public $SubjectUserId;
	public $LogType;
	public $LogContent;
}

class AdminLogData{
    public $AdminLogModels;

    private $dbConnection;
    private $publicColumns = Array(DB_COLUMN_ADMIN_LOG_ID, DB_COLUMN_ADMIN_LOG_ADMIN_USERNAME_OVERRIDE, DB_COLUMN_ADMIN_LOG_ADMIN_USER_ID, DB_COLUMN_ADMIN_LOG_SUBJECT_USER_ID, DB_COLUMN_ADMIN_LOG_TYPE, DB_COLUMN_ADMIN_LOG_CONTENT);
    private $privateColumns = Array(DB_COLUMN_ADMIN_LOG_DATETIME, DB_COLUMN_ADMIN_LOG_IP, DB_COLUMN_ADMIN_LOG_USER_AGENT);

    function __construct(&$dbConn) {
        $this->dbConnection = $dbConn;
        $this->AdminLogModels = $this->LoadAdminLog();
    }

//////////////////////// MODEL CONSTRUCTOR

    function LoadAdminLog(){
        AddActionLog("LoadAdminLog");
        StartTimer("LoadAdminLog");

        $data = $this->SelectAll();

        $adminLogModels = Array();
        while($info = mysqli_fetch_array($data)){
            $adminLogModel = new AdminLogModel();

            $adminLogModel->Id = $info[DB_COLUMN_ADMIN_LOG_ID];
            $adminLogModel->DateTime = $info[DB_COLUMN_ADMIN_LOG_DATETIME];
            $adminLogModel->Ip = $info[DB_COLUMN_ADMIN_LOG_IP];
            $adminLogModel->UserAgent = $info[DB_COLUMN_ADMIN_LOG_USER_AGENT];
            $adminLogModel->AdminUsernameOverride = $info[DB_COLUMN_ADMIN_LOG_ADMIN_USERNAME_OVERRIDE];
            $adminLogModel->AdminUserId = $info[DB_COLUMN_ADMIN_LOG_ADMIN_USER_ID];
            $adminLogModel->SubjectUserId = $info[DB_COLUMN_ADMIN_LOG_SUBJECT_USER_ID];
            $adminLogModel->LogType = $info[DB_COLUMN_ADMIN_LOG_TYPE];
            $adminLogModel->LogContent = $info[DB_COLUMN_ADMIN_LOG_CONTENT];

            $adminLogModels[] = $adminLogModel;
        }

        StopTimer("LoadAdminLog");
        return $adminLogModels;
    }

//////////////////////// END MODEL CONSTRUCTOR
    
//////////////////////// DATABASE ACTIONS (select, insert, update)

    function AddToAdminLog($logType, $logContent, $logSubjectUserId, $logAdminUserId, $logAdminUsernameOverride){
        global $ip, $userAgent;
        AddActionLog("AddToAdminLog");
        StartTimer("AddToAdminLog");

        $this->Insert($ip, $userAgent, $logAdminUsernameOverride, $logAdminUserId, $logSubjectUserId, $logType, $logContent);

        StopTimer("AddToAdminLog");
    }
    
    function GetAdminLogForAdminFormatted($adminUserId){
        AddActionLog("GetAdminLogForAdminFormatted");
        StartTimer("GetAdminLogForAdminFormatted");
    
        $data = $this->SelectWhereAdminUserId($adminUserId);
    
        StopTimer("GetAdminLogForAdminFormatted");
        return ArrayToHTML(MySQLDataToArray($data));
    }
    
    function GetAdminLogForSubjectFormatted($subjectUserId){
        AddActionLog("GetAdminLogForSubjectFormatted");
        StartTimer("GetAdminLogForSubjectFormatted");
    
        $data = $this->SelectWhereSubjectUserId($subjectUserId);
    
        StopTimer("GetAdminLogForSubjectFormatted");
        return ArrayToHTML(MySQLDataToArray($data));
    }

//////////////////////// END DATABASE ACTIONS
    
//////////////////////// FUCNTION SQL

    private function Insert($ip, $userAgent, $logAdminUsernameOverride, $logAdminUserId, $logSubjectUserId, $logType, $logContent){
        AddActionLog("AdminLogData_Insert");
        StartTimer("AdminLogData_Insert");

        $escapedIP = mysqli_real_escape_string($this->dbConnection, $ip);
        $escapedUserAgent = mysqli_real_escape_string($this->dbConnection, $userAgent);
        $escapedAdminUsernameOverride = mysqli_real_escape_string($this->dbConnection, $logAdminUsernameOverride);
        $escapedAdminUserId = mysqli_real_escape_string($this->dbConnection, $logAdminUserId);
        $escapedSubjectUserId = mysqli_real_escape_string($this->dbConnection, $logSubjectUserId);
        $escapedLogType = mysqli_real_escape_string($this->dbConnection, $logType);
        $escapedLogContent = mysqli_real_escape_string($this->dbConnection, $logContent);
    
        $sql = "
            INSERT INTO ".DB_TABLE_ADMIN_LOG."
            (".DB_COLUMN_ADMIN_LOG_ID.", ".DB_COLUMN_ADMIN_LOG_DATETIME.", ".DB_COLUMN_ADMIN_LOG_IP.", ".DB_COLUMN_ADMIN_LOG_USER_AGENT.", ".DB_COLUMN_ADMIN_LOG_ADMIN_USERNAME_OVERRIDE.", ".DB_COLUMN_ADMIN_LOG_ADMIN_USER_ID.", ".DB_COLUMN_ADMIN_LOG_SUBJECT_USER_ID.", ".DB_COLUMN_ADMIN_LOG_TYPE.", ".DB_COLUMN_ADMIN_LOG_CONTENT.")
            VALUES
            (
                null,
                Now(),
                '$escapedIP',
                '$escapedUserAgent',
                '$escapedAdminUsernameOverride',
                $escapedAdminUserId,
                $escapedSubjectUserId,
                '$escapedLogType',
                '$escapedLogContent'
            );";
        $data = mysqli_query($this->dbConnection, $sql);
        $sql = "";

        StopTimer("AdminLogData_Insert");
    }

    private function SelectAll(){
        AddActionLog("AdminLogData_SelectAll");
        StartTimer("AdminLogData_SelectAll");

        $sql = "
            SELECT ".DB_COLUMN_ADMIN_LOG_ID.", ".DB_COLUMN_ADMIN_LOG_DATETIME.", ".DB_COLUMN_ADMIN_LOG_IP.", ".DB_COLUMN_ADMIN_LOG_USER_AGENT.", ".DB_COLUMN_ADMIN_LOG_ADMIN_USERNAME_OVERRIDE.", ".DB_COLUMN_ADMIN_LOG_ADMIN_USER_ID.", ".DB_COLUMN_ADMIN_LOG_SUBJECT_USER_ID.", ".DB_COLUMN_ADMIN_LOG_TYPE.", ".DB_COLUMN_ADMIN_LOG_CONTENT."
            FROM ".DB_TABLE_ADMIN_LOG."
            ORDER BY ".DB_COLUMN_ADMIN_LOG_ID." DESC";

        StopTimer("AdminLogData_SelectAll");
        return mysqli_query($this->dbConnection, $sql);
    }

    private function SelectWhereAdminUserId($adminUserId){
        AddActionLog("AdminLogData_SelectWhereAdminUserId");
        StartTimer("AdminLogData_SelectWhereAdminUserId");

        $escapedAdminUserId = mysqli_real_escape_string($this->dbConnection, $adminUserId);
        $sql = "
            SELECT *
            FROM ".DB_TABLE_ADMIN_LOG."
            WHERE ".DB_COLUMN_ADMIN_LOG_ADMIN_USER_ID." = '$escapedAdminUserId';";

        StopTimer("AdminLogData_SelectWhereAdminUserId");
        return mysqli_query($this->dbConnection, $sql);
    }

    private function SelectWhereSubjectUserId($subjectUserId){
        AddActionLog("AdminLogData_SelectWhereSubjectUserId");
        StartTimer("AdminLogData_SelectWhereSubjectUserId");

        $escapedSubjectUserId = mysqli_real_escape_string($this->dbConnection, $subjectUserId);
        $sql = "
            SELECT ".DB_COLUMN_ADMIN_LOG_ID.", ".DB_COLUMN_ADMIN_LOG_ADMIN_USERNAME_OVERRIDE.", ".DB_COLUMN_ADMIN_LOG_ADMIN_USER_ID.", ".DB_COLUMN_ADMIN_LOG_SUBJECT_USER_ID.", ".DB_COLUMN_ADMIN_LOG_TYPE.", ".DB_COLUMN_ADMIN_LOG_CONTENT."
            FROM ".DB_TABLE_ADMIN_LOG."
            WHERE ".DB_COLUMN_ADMIN_LOG_SUBJECT_USER_ID." = '$escapedSubjectUserId';";

        StopTimer("AdminLogData_SelectWhereSubjectUserId");
        return mysqli_query($this->dbConnection, $sql);
    }

//////////////////////// END FUCNTION SQL

//////////////////////// PUBLIC DATA EXPORT

    function GetAllPublicData(){
        AddActionLog("AdminLogData_GetAllPublicData");
        StartTimer("AdminLogData_GetAllPublicData");
        
        $dataFromDatabase = MySQLDataToArray($this->SelectPublicData());

        foreach($dataFromDatabase as $i => $row){
            $dataFromDatabase[$i][DB_COLUMN_ADMIN_LOG_DATETIME] = gmdate("Y-m-d H:i:s", time());
            $dataFromDatabase[$i][DB_COLUMN_ADMIN_LOG_IP] = "MIGRATION";
            $dataFromDatabase[$i][DB_COLUMN_ADMIN_LOG_USER_AGENT] = "MIGRATION";
        }

        StopTimer("AdminLogData_GetAllPublicData");
        return $dataFromDatabase;
    }

    private function SelectPublicData(){
        AddActionLog("AdminLogData_SelectPublicData");
        StartTimer("AdminLogData_SelectPublicData");

        $sql = "
            SELECT ".implode(",", $this->publicColumns)."
            FROM ".DB_TABLE_ADMIN_LOG.";
        ";

        StopTimer("AdminLogData_SelectPublicData");
        return mysqli_query($this->dbConnection, $sql);
    }

//////////////////////// END PUBLIC DATA EXPORT
}

?>