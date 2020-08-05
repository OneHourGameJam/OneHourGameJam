<?php

define("DB_TABLE_SESSION", "session");

define("DB_COLUMN_SESSION_ID",                  "session_id");
define("DB_COLUMN_SESSION_USER_ID",             "session_user_id");
define("DB_COLUMN_SESSION_DATETIME_STARTED",    "session_datetime_started");
define("DB_COLUMN_SESSION_DATETIME_LAST_USED",  "session_datetime_last_used");

class SessionDbInterface{
    private $dbConnection;
    private $publicColumns = Array(DB_COLUMN_SESSION_USER_ID);
    private $privateColumns = Array(DB_COLUMN_SESSION_ID, DB_COLUMN_SESSION_DATETIME_STARTED, DB_COLUMN_SESSION_DATETIME_LAST_USED);

    function __construct(&$dbConn) {
        $this->dbConnection = $dbConn;
    }

    public function SelectSingleSession($sessionIdHash){
        AddActionLog("SessionDbInterface_SelectSingleSession");
        StartTimer("SessionDbInterface_SelectSingleSession");
        
        $cleanSessionIdHash = mysqli_real_escape_string($this->dbConnection, $sessionIdHash);

        $sql = "
            SELECT ".DB_COLUMN_SESSION_ID.", ".DB_COLUMN_SESSION_USER_ID."
            FROM ".DB_TABLE_SESSION."
            WHERE ".DB_COLUMN_SESSION_ID." = '$cleanSessionIdHash';
        ";
        
        StopTimer("SessionDbInterface_SelectSingleSession");
        return mysqli_query($this->dbConnection, $sql);
    }

    public function SelectSessionsOfUser($userId){
        AddActionLog("SessionDbInterface_SelectSessionsOfUser");
        StartTimer("SessionDbInterface_SelectSessionsOfUser");

        $escapedUserId = mysqli_real_escape_string($this->dbConnection, $userId);
        $sql = "
            SELECT *
            FROM ".DB_TABLE_SESSION."
            WHERE ".DB_COLUMN_SESSION_USER_ID." = $escapedUserId;
        ";
        
        StopTimer("SessionDbInterface_SelectSessionsOfUser");
        return mysqli_query($this->dbConnection, $sql);
    }

    public function Insert($userId, $sessionIdHash){
        AddActionLog("SessionDbInterface_Insert");
        StartTimer("SessionDbInterface_Insert");

        $escapedUserId = mysqli_real_escape_string($this->dbConnection, $userId);
        $escapedSessionIdHash = mysqli_real_escape_string($this->dbConnection, $sessionIdHash);

		$sql = "
			INSERT INTO ".DB_TABLE_SESSION."
			(".DB_COLUMN_SESSION_ID.",
			".DB_COLUMN_SESSION_USER_ID.",
			".DB_COLUMN_SESSION_DATETIME_STARTED.",
			".DB_COLUMN_SESSION_DATETIME_LAST_USED.")
			VALUES
			('$escapedSessionIdHash',
			'$escapedUserId',
			Now(),
			Now());
		";
        mysqli_query($this->dbConnection, $sql);

        StopTimer("SessionDbInterface_Insert");
    }

    public function UpdateLastUsedTime($sessionIdHash){
        AddActionLog("SessionDbInterface_UpdateLastUsedTime");
        StartTimer("SessionDbInterface_UpdateLastUsedTime");

        $escapedSessionIdHash = mysqli_real_escape_string($this->dbConnection, $sessionIdHash);

		$sql = "
			UPDATE ".DB_TABLE_SESSION."
			SET ".DB_COLUMN_SESSION_DATETIME_LAST_USED." = Now()
			WHERE ".DB_COLUMN_SESSION_ID." = '$escapedSessionIdHash'
		";
        mysqli_query($this->dbConnection, $sql);

        StopTimer("SessionDbInterface_UpdateLastUsedTime");
    }

    public function Delete($sessionIdHash){
        AddActionLog("SessionDbInterface_Insert");
        StartTimer("SessionDbInterface_Insert");

        $escapedSessionIdHash = mysqli_real_escape_string($this->dbConnection, $sessionIdHash);

        $sql = "
            DELETE FROM ".DB_TABLE_SESSION."
            WHERE ".DB_COLUMN_SESSION_ID." = '$escapedSessionIdHash';
        ";
        mysqli_query($this->dbConnection, $sql);
        
        StopTimer("SessionDbInterface_Insert");
    }

    private function SelectPublicData(){
        AddActionLog("SessionDbInterface_SelectPublicData");
        StartTimer("SessionDbInterface_SelectPublicData");

        $sql = "
            SELECT ".implode(",", $this->publicColumnsSession)."
            FROM ".DB_TABLE_SESSION.";
        ";

        StopTimer("SessionDbInterface_SelectPublicData");
        return mysqli_query($this->dbConnection, $sql);
    }
}

?>