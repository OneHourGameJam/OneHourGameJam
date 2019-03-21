<?php

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
	StartTimer("RenderAdminLog");
	$render = Array();

	foreach($adminLog as $i => $adminLogModel){
		$log = Array();
		
		$log["id"] = $adminLogModel->Id;
		$log["datetime"] = $adminLogModel->DateTime;
		$log["ip"] = $adminLogModel->Ip;
		$log["user_agent"] = $adminLogModel->UserAgent;
		$log["admin_username"] = $adminLogModel->AdminUsername;
		$log["subject_username"] = $adminLogModel->SubjectUsername;
		$log["log_type"] = $adminLogModel->LogType;
		$log["log_content"] = $adminLogModel->LogContent;

		$render[] = $log;
	}

	StopTimer("RenderAdminLog");
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