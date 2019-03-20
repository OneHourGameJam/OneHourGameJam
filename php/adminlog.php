<?php

class AdminLog{
	public $Id;
	public $DateTime;
	public $Ip;
	public $UserAgent;
	public $AdminUsername;
	public $SubjectUsername;
	public $LogType;
	public $LogContent;
}

function LoadAdminLog(){
    global $dbConn;
	AddActionLog("LoadAdminLog");
	StartTimer("LoadAdminLog");

    $adminLog = Array();

	$sql = "select log_id, log_datetime, log_ip, log_user_agent, log_admin_username, log_subject_username, log_type, log_content from admin_log order by log_id desc";
	$data = mysqli_query($dbConn, $sql);
	$sql = "";

	while($info = mysqli_fetch_array($data)){
		$log = new AdminLog();

		$log->Id = $info["log_id"];
		$log->DateTime = $info["log_datetime"];
		$log->Ip = $info["log_ip"];
		$log->UserAgent = $info["log_user_agent"];
		$log->AdminUsername = $info["log_admin_username"];
		$log->SubjectUsername = $info["log_subject_username"];
		$log->LogType = $info["log_type"];
        $log->LogContent = $info["log_content"];

        $adminLog[] = $log;
    }

	StopTimer("LoadAdminLog");
    return $adminLog;
}

function AddToAdminLog($logType, $logContent, $logSubjectUsername, $logAdminUsername){
    global $dbConn, $ip, $userAgent;
	AddActionLog("AddToAdminLog");
	StartTimer("AddToAdminLog");

	$escapedIP = mysqli_real_escape_string($dbConn, $ip);
	$escapedUserAgent = mysqli_real_escape_string($dbConn, $userAgent);
	$escapedAdminUsername = mysqli_real_escape_string($dbConn, $logAdminUsername);
	$escapedSubjectUsername = mysqli_real_escape_string($dbConn, $logSubjectUsername);
	$escapedLogType = mysqli_real_escape_string($dbConn, $logType);
	$escapedLogContent = mysqli_real_escape_string($dbConn, $logContent);

	$sql = "
        INSERT INTO admin_log
        (log_id, log_datetime, log_ip, log_user_agent, log_admin_username, log_subject_username, log_type, log_content)
        VALUES
        (
            null,
            Now(),
            '$escapedIP',
            '$escapedUserAgent',
            '$escapedAdminUsername',
            '$escapedSubjectUsername',
            '$escapedLogType',
            '$escapedLogContent'
        );";

	$data = mysqli_query($dbConn, $sql);
	$sql = "";
	StopTimer("AddToAdminLog");
}

function RenderAdminLog(&$adminLog){
	AddActionLog("RenderAdminLog");
	$render = Array();

	foreach($adminLog as $i => $logData){
		$log = Array();
		
		$log["id"] = $logData->Id;
		$log["datetime"] = $logData->DateTime;
		$log["ip"] = $logData->Ip;
		$log["user_agent"] = $logData->UserAgent;
		$log["admin_username"] = $logData->AdminUsername;
		$log["subject_username"] = $logData->SubjectUsername;
		$log["log_type"] = $logData->LogType;
		$log["log_content"] = $logData->LogContent;

		$render[] = $log;
	}

    return $render;
}

function GetAdminLogForAdminFormatted($username){
	global $dbConn;
	AddActionLog("GetAdminLogForAdminFormatted");
	StartTimer("GetAdminLogForAdminFormatted");

	$escapedUsername = mysqli_real_escape_string($dbConn, $username);
	$sql = "
		SELECT *
		FROM admin_log
		WHERE log_admin_username = '$escapedUsername';
	";
	$data = mysqli_query($dbConn, $sql);
	$sql = "";

	StopTimer("GetAdminLogForAdminFormatted");
	return ArrayToHTML(MySQLDataToArray($data));
}

function GetAdminLogForSubjectFormatted($username){
	global $dbConn;
	AddActionLog("GetAdminLogForSubjectFormatted");
	StartTimer("GetAdminLogForSubjectFormatted");

	$escapedUsername = mysqli_real_escape_string($dbConn, $username);
	$sql = "
		SELECT log_id, log_datetime, log_admin_username, log_subject_username, log_type, log_content
		FROM admin_log
		WHERE log_subject_username = '$escapedUsername';
	";
	$data = mysqli_query($dbConn, $sql);
	$sql = "";

	StopTimer("GetAdminLogForSubjectFormatted");
	return ArrayToHTML(MySQLDataToArray($data));
}

?>