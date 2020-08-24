<?php
namespace Plugins\AdminLog;

define("ADMINVOTE_FOR", "FOR");
define("ADMINVOTE_NEUTRAL", "NEUTRAL");
define("ADMINVOTE_AGAINST", "AGAINST");
define("ADMINVOTE_SPONSOR", "SPONSOR");
define("ADMINVOTE_VETO", "VETO");

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

    private $adminLogDbInterface;

    function __construct(&$adminLogDbInterface) {
        $this->adminLogDbInterface = $adminLogDbInterface;
        $this->AdminLogModels = $this->LoadAdminLog();
    }

//////////////////////// MODEL CONSTRUCTOR

    function LoadAdminLog(){
        AddActionLog("LoadAdminLog");
        StartTimer("LoadAdminLog");

        $data = $this->adminLogDbInterface->SelectAll();

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

        $this->adminLogDbInterface->Insert($ip, $userAgent, $logAdminUsernameOverride, $logAdminUserId, $logSubjectUserId, $logType, $logContent);

        StopTimer("AddToAdminLog");
    }
    
    function GetAdminLogForAdmin($adminUserId){
        AddActionLog("GetAdminLogForAdmin");
        StartTimer("GetAdminLogForAdmin");
    
        $data = $this->adminLogDbInterface->SelectWhereAdminUserId($adminUserId);
    
        StopTimer("GetAdminLogForAdmin");
        return MySQLDataToArray($data);
    }
    
    function GetAdminLogForSubject($subjectUserId){
        AddActionLog("GetAdminLogForSubject");
        StartTimer("GetAdminLogForSubject");
    
        $data = $this->adminLogDbInterface->SelectWhereSubjectUserId($subjectUserId);
    
        StopTimer("GetAdminLogForSubject");
        return MySQLDataToArray($data);
    }

//////////////////////// END DATABASE ACTIONS

//////////////////////// PUBLIC DATA EXPORT

    function GetAllPublicData(){
        AddActionLog("AdminLogData_GetAllPublicData");
        StartTimer("AdminLogData_GetAllPublicData");
        
        $dataFromDatabase = MySQLDataToArray($this->adminLogDbInterface->SelectPublicData());

        foreach($dataFromDatabase as $i => $row){
            $dataFromDatabase[$i][DB_COLUMN_ADMIN_LOG_DATETIME] = gmdate("Y-m-d H:i:s", time());
            $dataFromDatabase[$i][DB_COLUMN_ADMIN_LOG_IP] = OVERRIDE_MIGRATION;
            $dataFromDatabase[$i][DB_COLUMN_ADMIN_LOG_USER_AGENT] = OVERRIDE_MIGRATION;
        }

        StopTimer("AdminLogData_GetAllPublicData");
        return $dataFromDatabase;
    }

//////////////////////// END PUBLIC DATA EXPORT
}

?>