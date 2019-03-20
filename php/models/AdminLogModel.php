<?php

class AdminLogModel{
	public $Id;
	public $DateTime;
	public $Ip;
	public $UserAgent;
	public $AdminUsername;
	public $SubjectUsername;
	public $LogType;
	public $LogContent;
}

class AdminLogData{
    public $AdminLogModels;

    function __construct() {
        $this->AdminLogModels = $this->LoadAdminLog();
    }

    function LoadAdminLog(){
        global $dbConn;
        AddActionLog("LoadAdminLog");
        StartTimer("LoadAdminLog");

        $adminLogModels = Array();

        $sql = "select log_id, log_datetime, log_ip, log_user_agent, log_admin_username, log_subject_username, log_type, log_content from admin_log order by log_id desc";
        $data = mysqli_query($dbConn, $sql);
        $sql = "";

        while($info = mysqli_fetch_array($data)){
            $adminLogModel = new AdminLogModel();

            $adminLogModel->Id = $info["log_id"];
            $adminLogModel->DateTime = $info["log_datetime"];
            $adminLogModel->Ip = $info["log_ip"];
            $adminLogModel->UserAgent = $info["log_user_agent"];
            $adminLogModel->AdminUsername = $info["log_admin_username"];
            $adminLogModel->SubjectUsername = $info["log_subject_username"];
            $adminLogModel->LogType = $info["log_type"];
            $adminLogModel->LogContent = $info["log_content"];

            $adminLogModels[] = $adminLogModel;
        }

        StopTimer("LoadAdminLog");
        return $adminLogModels;
    }
}

?>