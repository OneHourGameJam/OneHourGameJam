<?php

define("DB_TABLE_SESSION", "session");

define("DB_COLUMN_SESSION_ID",                  "session_id");
define("DB_COLUMN_SESSION_USER_ID",             "session_user_id");
define("DB_COLUMN_SESSION_DATETIME_STARTED",    "session_datetime_started");
define("DB_COLUMN_SESSION_DATETIME_LAST_USED",  "session_datetime_last_used");

class SessionDbInterface{
    private $database;
    private $publicColumns = Array(DB_COLUMN_SESSION_USER_ID);
    private $privateColumns = Array(DB_COLUMN_SESSION_ID, DB_COLUMN_SESSION_DATETIME_STARTED, DB_COLUMN_SESSION_DATETIME_LAST_USED);

    function __construct(&$database) {
        $this->database = $database;
    }

    public function SelectSingleSession($sessionIdHash){
        AddActionLog("SessionDbInterface_SelectSingleSession");
        StartTimer("SessionDbInterface_SelectSingleSession");
        
        $cleanSessionIdHash = $this->database->EscapeString($sessionIdHash);

        $sql = "
            SELECT ".DB_COLUMN_SESSION_ID.", ".DB_COLUMN_SESSION_USER_ID."
            FROM ".DB_TABLE_SESSION."
            WHERE ".DB_COLUMN_SESSION_ID." = '$cleanSessionIdHash';
        ";
        
        StopTimer("SessionDbInterface_SelectSingleSession");
        return $this->database->Execute($sql);;
    }

    public function SelectSessionsOfUser($userId){
        AddActionLog("SessionDbInterface_SelectSessionsOfUser");
        StartTimer("SessionDbInterface_SelectSessionsOfUser");

        $escapedUserId = $this->database->EscapeString($userId);
        $sql = "
            SELECT *
            FROM ".DB_TABLE_SESSION."
            WHERE ".DB_COLUMN_SESSION_USER_ID." = $escapedUserId;
        ";
        
        StopTimer("SessionDbInterface_SelectSessionsOfUser");
        return $this->database->Execute($sql);;
    }

    public function Insert($userId, $sessionIdHash){
        AddActionLog("SessionDbInterface_Insert");
        StartTimer("SessionDbInterface_Insert");

        $escapedUserId = $this->database->EscapeString($userId);
        $escapedSessionIdHash = $this->database->EscapeString($sessionIdHash);

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
        $this->database->Execute($sql);;

        StopTimer("SessionDbInterface_Insert");
    }

    public function UpdateLastUsedTime($sessionIdHash){
        AddActionLog("SessionDbInterface_UpdateLastUsedTime");
        StartTimer("SessionDbInterface_UpdateLastUsedTime");

        $escapedSessionIdHash = $this->database->EscapeString($sessionIdHash);

		$sql = "
			UPDATE ".DB_TABLE_SESSION."
			SET ".DB_COLUMN_SESSION_DATETIME_LAST_USED." = Now()
			WHERE ".DB_COLUMN_SESSION_ID." = '$escapedSessionIdHash'
		";
        $this->database->Execute($sql);;

        StopTimer("SessionDbInterface_UpdateLastUsedTime");
    }

    public function Delete($sessionIdHash){
        AddActionLog("SessionDbInterface_Insert");
        StartTimer("SessionDbInterface_Insert");

        $escapedSessionIdHash = $this->database->EscapeString($sessionIdHash);

        $sql = "
            DELETE FROM ".DB_TABLE_SESSION."
            WHERE ".DB_COLUMN_SESSION_ID." = '$escapedSessionIdHash';
        ";
        $this->database->Execute($sql);;
        
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
        return $this->database->Execute($sql);;
    }
}

?>