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

class AdminLogDbInterface{
    private $dbConnection;
    private $publicColumns = Array(DB_COLUMN_ADMIN_LOG_ID, DB_COLUMN_ADMIN_LOG_ADMIN_USERNAME_OVERRIDE, DB_COLUMN_ADMIN_LOG_ADMIN_USER_ID, DB_COLUMN_ADMIN_LOG_SUBJECT_USER_ID, DB_COLUMN_ADMIN_LOG_TYPE, DB_COLUMN_ADMIN_LOG_CONTENT);
    private $privateColumns = Array(DB_COLUMN_ADMIN_LOG_DATETIME, DB_COLUMN_ADMIN_LOG_IP, DB_COLUMN_ADMIN_LOG_USER_AGENT);

    function __construct(&$dbConn) {
        $this->dbConnection = $dbConn;
    }

    public function Insert($ip, $userAgent, $logAdminUsernameOverride, $logAdminUserId, $logSubjectUserId, $logType, $logContent){
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

    public function SelectAll(){
        AddActionLog("AdminLogData_SelectAll");
        StartTimer("AdminLogData_SelectAll");

        $sql = "
            SELECT ".DB_COLUMN_ADMIN_LOG_ID.", ".DB_COLUMN_ADMIN_LOG_DATETIME.", ".DB_COLUMN_ADMIN_LOG_IP.", ".DB_COLUMN_ADMIN_LOG_USER_AGENT.", ".DB_COLUMN_ADMIN_LOG_ADMIN_USERNAME_OVERRIDE.", ".DB_COLUMN_ADMIN_LOG_ADMIN_USER_ID.", ".DB_COLUMN_ADMIN_LOG_SUBJECT_USER_ID.", ".DB_COLUMN_ADMIN_LOG_TYPE.", ".DB_COLUMN_ADMIN_LOG_CONTENT."
            FROM ".DB_TABLE_ADMIN_LOG."
            ORDER BY ".DB_COLUMN_ADMIN_LOG_ID." DESC";

        StopTimer("AdminLogData_SelectAll");
        return mysqli_query($this->dbConnection, $sql);
    }

    public function SelectWhereAdminUserId($adminUserId){
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

    public function SelectWhereSubjectUserId($subjectUserId){
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

    public function SelectPublicData(){
        AddActionLog("AdminLogData_SelectPublicData");
        StartTimer("AdminLogData_SelectPublicData");

        $sql = "
            SELECT ".implode(",", $this->publicColumns)."
            FROM ".DB_TABLE_ADMIN_LOG.";
        ";

        StopTimer("AdminLogData_SelectPublicData");
        return mysqli_query($this->dbConnection, $sql);
    }
}

?>