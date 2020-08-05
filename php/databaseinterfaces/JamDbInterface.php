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

class JamDbInterface{
    private $dbConnection;
    private $publicColumns = Array(DB_COLUMN_JAM_ID, DB_COLUMN_JAM_USER_ID, DB_COLUMN_JAM_NUMBER, DB_COLUMN_JAM_SELECTED_THEME_ID, DB_COLUMN_JAM_THEME, DB_COLUMN_JAM_START_DATETIME, DB_COLUMN_JAM_STATE, DB_COLUMN_JAM_COLORS, DB_COLUMN_JAM_DELETED);
    private $privateColumns = Array(DB_COLUMN_JAM_DATETIME, DB_COLUMN_JAM_IP, DB_COLUMN_JAM_USER_AGENT);

    function __construct(&$dbConn) {
        $this->dbConnection = $dbConn;
    }

    public function SelectAll(){
        AddActionLog("JamDbInterface_SelectAll");
        StartTimer("JamDbInterface_SelectAll");

        $sql = "
            SELECT ".DB_COLUMN_JAM_ID.", ".DB_COLUMN_JAM_USER_ID.", ".DB_COLUMN_JAM_NUMBER.", ".DB_COLUMN_JAM_SELECTED_THEME_ID.", ".DB_COLUMN_JAM_THEME.", ".DB_COLUMN_JAM_START_DATETIME.", ".DB_COLUMN_JAM_STATE.", ".DB_COLUMN_JAM_COLORS.", ".DB_COLUMN_JAM_DELETED."
            FROM ".DB_TABLE_JAM." 
            ORDER BY ".DB_COLUMN_JAM_NUMBER." DESC";
        
        StopTimer("JamDbInterface_SelectAll");
        return mysqli_query($this->dbConnection, $sql);
    }

    public function SelectJamsScheduledByUser($userId){
        AddActionLog("JamDbInterface_SelectJamsScheduledByUser");
        StartTimer("JamDbInterface_SelectJamsScheduledByUser");

        $escapedUserId = mysqli_real_escape_string($this->dbConnection, $userId);
        $sql = "
            SELECT *
            FROM ".DB_TABLE_JAM."
            WHERE ".DB_COLUMN_JAM_USER_ID." = '$escapedUserId';
        ";
        
        StopTimer("JamDbInterface_SelectJamsScheduledByUser");
        return mysqli_query($this->dbConnection, $sql);
    }

    public function SelectCurrentJamNumberAndId(){
        AddActionLog("JamDbInterface_SelectCurrentJamNumberAndId");
        StartTimer("JamDbInterface_SelectCurrentJamNumberAndId");

        $sql = "
            SELECT j.jam_id, j.jam_jam_number
            FROM (
                SELECT MAX(jam_id) as max_jam_id
                FROM jam
                WHERE jam_start_datetime <= Now()
                  AND jam_deleted = 0
            ) past_jams, jam j
            WHERE past_jams.max_jam_id = j.jam_id
        ";
        
        StopTimer("JamDbInterface_SelectCurrentJamNumberAndId");
        return mysqli_query($this->dbConnection, $sql);
    }

    public function SelectIfJamExists($jamId){
        AddActionLog("JamDbInterface_SelectIfJamExists");
        StartTimer("JamDbInterface_SelectIfJamExists");

        $escapedJamId = mysqli_real_escape_string($this->dbConnection, $jamId);
        $sql = "
            SELECT 1
            FROM jam
            WHERE jam_id = $escapedJamId
            AND jam_deleted = 0;
            ";
        
        StopTimer("JamDbInterface_SelectIfJamExists");
        return mysqli_query($this->dbConnection, $sql);
    }

    public function Insert($ip, $userAgent, $userId, $jamNumber, $selectedThemeId, $theme, $startTime, $colors){
        AddActionLog("JamDbInterface_Insert");
        StartTimer("JamDbInterface_Insert");

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

        StopTimer("JamDbInterface_Insert");
    }

    public function Update($jamId, $theme, $startTime, $color){
        AddActionLog("JamDbInterface_Update");
        StartTimer("JamDbInterface_Update");

        $escapedJamId = mysqli_real_escape_string($this->dbConnection, intval($jamId));
        $escapedTheme = mysqli_real_escape_string($this->dbConnection, $theme);
        $escapedStartTime = mysqli_real_escape_string($this->dbConnection, $startTime);
        $escapedColors = mysqli_real_escape_string($this->dbConnection, $color);

        $sql = "
            UPDATE jam
            SET jam_theme = '$escapedTheme',
                jam_start_datetime = '$escapedStartTime',
                jam_colors = '$escapedColors'
            WHERE jam_id = $escapedJamId;";
        mysqli_query($this->dbConnection, $sql);
        
        StopTimer("JamDbInterface_Update");
    }

    public function UpdateJamState($jamId, $jamState){
        AddActionLog("JamDbInterface_UpdateJamState");
        StartTimer("JamDbInterface_UpdateJamState");

        $escapedJamId = mysqli_real_escape_string($this->dbConnection, intval($jamId));
        $escapedJamState = mysqli_real_escape_string($this->dbConnection, $jamState);

        $sql = "
            UPDATE ".DB_TABLE_JAM."
            SET ".DB_COLUMN_JAM_STATE." = '$escapedJamState'
            WHERE ".DB_COLUMN_JAM_ID." = $escapedJamId";
        mysqli_query($this->dbConnection, $sql);
        
        StopTimer("JamDbInterface_UpdateJamState");
    }

    public function SoftDelete($jamId){
        AddActionLog("JamDbInterface_SoftDelete");
        StartTimer("JamDbInterface_SoftDelete");

        $escapedJamId = mysqli_real_escape_string($this->dbConnection, intval($jamId));

        $sql = "
            UPDATE jam 
            SET jam_deleted = 1 
            WHERE jam_id = $escapedJamId";
        mysqli_query($this->dbConnection, $sql);
        
        StopTimer("JamDbInterface_SoftDelete");
    }

    public function SelectPublicData(){
        AddActionLog("JamDbInterface_SelectPublicData");
        StartTimer("JamDbInterface_SelectPublicData");

        $sql = "
            SELECT ".implode(",", $this->publicColumns)."
            FROM ".DB_TABLE_JAM.";
        ";

        StopTimer("JamDbInterface_SelectPublicData");
        return mysqli_query($this->dbConnection, $sql);
    }
}

?>