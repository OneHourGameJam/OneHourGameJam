<?php

function LoadAdminLog(){
    global $dbConn;

	AddActionLog("LoadAdminLog");
	StartTimer("LoadAdminLog");

    $adminLog = Array();

	$sql = "select log_id, log_datetime, log_ip, log_user_agent, log_admin_username, log_subject_username, log_type, log_content from admin_log order by log_id desc";
	$data = mysqli_query($dbConn, $sql);
	$sql = "";

	while($info = mysqli_fetch_array($data)){

		//Read data about the jam
		$log = Array();
		$log["id"] = $info["log_id"];
		$log["datetime"] = $info["log_datetime"];
		$log["ip"] = $info["log_ip"];
		$log["user_agent"] = $info["log_user_agent"];
		$log["admin_username"] = $info["log_admin_username"];
		$log["subject_username"] = $info["log_subject_username"];
		$log["log_type"] = $info["log_type"];
        $log["log_content"] = $info["log_content"];

        $adminLog[] = $log;
    }

	StopTimer("LoadAdminLog");
    return $adminLog;
}

function AddToAdminLog($logType, $logContent, $logSubjectUsername){
    global $dbConn, $ip, $userAgent, $loggedInUser;
	AddActionLog("AddToAdminLog");
	StartTimer("AddToAdminLog");

	$escapedIP = mysqli_real_escape_string($dbConn, $ip);
	$escapedUserAgent = mysqli_real_escape_string($dbConn, $userAgent);
	$escapedAdminUsername = mysqli_real_escape_string($dbConn, $loggedInUser["username"]);
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

function RenderAdminLog($adminLog){
	AddActionLog("RenderAdminLog");
    return $adminLog;
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