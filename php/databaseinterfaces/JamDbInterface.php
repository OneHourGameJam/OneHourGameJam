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
define("DB_COLUMN_JAM_DEFAULT_ICON_URL",  "jam_default_icon_url");
define("DB_COLUMN_JAM_DELETED",           "jam_deleted");

class JamDbInterface{
    private $database;
    private $publicColumns = Array(DB_COLUMN_JAM_ID, DB_COLUMN_JAM_USER_ID, DB_COLUMN_JAM_NUMBER, DB_COLUMN_JAM_SELECTED_THEME_ID, DB_COLUMN_JAM_THEME, DB_COLUMN_JAM_START_DATETIME, DB_COLUMN_JAM_STATE, DB_COLUMN_JAM_COLORS, DB_COLUMN_JAM_DEFAULT_ICON_URL, DB_COLUMN_JAM_DELETED);
    private $privateColumns = Array(DB_COLUMN_JAM_DATETIME, DB_COLUMN_JAM_IP, DB_COLUMN_JAM_USER_AGENT);

    function __construct(&$database) {
        $this->database = $database;
    }

    public function SelectAll(){
        AddActionLog("JamDbInterface_SelectAll");
        StartTimer("JamDbInterface_SelectAll");

        $sql = "
            SELECT ".DB_COLUMN_JAM_ID.", ".DB_COLUMN_JAM_USER_ID.", ".DB_COLUMN_JAM_NUMBER.", ".DB_COLUMN_JAM_SELECTED_THEME_ID.", ".DB_COLUMN_JAM_THEME.", ".DB_COLUMN_JAM_START_DATETIME.", ".DB_COLUMN_JAM_STATE.", ".DB_COLUMN_JAM_COLORS.", ".DB_COLUMN_JAM_DEFAULT_ICON_URL.", ".DB_COLUMN_JAM_DELETED."
            FROM ".DB_TABLE_JAM." 
            ORDER BY ".DB_COLUMN_JAM_NUMBER." DESC";
        
        StopTimer("JamDbInterface_SelectAll");
        return $this->database->Execute($sql);
    }

    public function SelectActive(){
        AddActionLog("JamDbInterface_SelectActive");
        StartTimer("JamDbInterface_SelectActive");

        $sql = "
            SELECT ".DB_COLUMN_JAM_NUMBER.", ".DB_COLUMN_JAM_THEME.", ".DB_COLUMN_JAM_START_DATETIME.", UTC_TIMESTAMP() as jam_now, UNIX_TIMESTAMP(".DB_COLUMN_JAM_START_DATETIME.") - UNIX_TIMESTAMP(UTC_TIMESTAMP()) AS jam_timediff
            FROM ".DB_TABLE_JAM."
            WHERE ".DB_COLUMN_JAM_DELETED." = 0
            ORDER BY ".DB_COLUMN_JAM_ID.";
        ";
        
        StopTimer("JamDbInterface_SelectActive");
        return $this->database->Execute($sql);
    }
    
    public function SelectJamsScheduledByUser($userId){
        AddActionLog("JamDbInterface_SelectJamsScheduledByUser");
        StartTimer("JamDbInterface_SelectJamsScheduledByUser");

        $escapedUserId = $this->database->EscapeString($userId);
        $sql = "
            SELECT *
            FROM ".DB_TABLE_JAM."
            WHERE ".DB_COLUMN_JAM_USER_ID." = '$escapedUserId';
        ";
        
        StopTimer("JamDbInterface_SelectJamsScheduledByUser");
        return $this->database->Execute($sql);
    }

    public function SelectCurrentJamNumberAndId(){
        AddActionLog("JamDbInterface_SelectCurrentJamNumberAndId");
        StartTimer("JamDbInterface_SelectCurrentJamNumberAndId");

        $sql = "
            SELECT j.".DB_COLUMN_JAM_ID.", j.".DB_COLUMN_JAM_NUMBER."
            FROM (
                SELECT MAX(".DB_COLUMN_JAM_ID.") as max_jam_id
                FROM ".DB_TABLE_JAM."
                WHERE ".DB_COLUMN_JAM_START_DATETIME." <= Now()
                  AND ".DB_COLUMN_JAM_DELETED." = 0
            ) past_jams, jam j
            WHERE past_jams.max_jam_id = j.".DB_COLUMN_JAM_ID."
        ";
        
        StopTimer("JamDbInterface_SelectCurrentJamNumberAndId");
        return $this->database->Execute($sql);
    }

    public function SelectIfJamExists($jamId){
        AddActionLog("JamDbInterface_SelectIfJamExists");
        StartTimer("JamDbInterface_SelectIfJamExists");

        $escapedJamId = $this->database->EscapeString($jamId);
        $sql = "
            SELECT 1
            FROM ".DB_TABLE_JAM."
            WHERE ".DB_COLUMN_JAM_ID." = $escapedJamId
            AND ".DB_COLUMN_JAM_DELETED." = 0;
            ";
        
        StopTimer("JamDbInterface_SelectIfJamExists");
        return $this->database->Execute($sql);
    }

    public function Insert($ip, $userAgent, $userId, $jamNumber, $selectedThemeId, $theme, $startTime, $colors, $defaultEntryIconUrl){
        AddActionLog("JamDbInterface_Insert");
        StartTimer("JamDbInterface_Insert");

        $escapedIp = $this->database->EscapeString($ip);
        $escapedUserAgent = $this->database->EscapeString($userAgent);
        $escapedUserId = $this->database->EscapeString($userId);
        $escapedJamNumber = $this->database->EscapeString($jamNumber);
        $escapedSelectedThemeId = $this->database->EscapeString($selectedThemeId);
        $escapedTheme = $this->database->EscapeString($theme);
        $escapedStartTime = $this->database->EscapeString($startTime);
        $escapedColors = $this->database->EscapeString($colors);
        $escapedDefaultEntryIconUrl = $this->database->EscapeString($defaultEntryIconUrl);
            
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
            ".DB_COLUMN_JAM_DEFAULT_ICON_URL.",
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
            '$escapedDefaultEntryIconUrl',
            0);";

        $this->database->Execute($sql);

        StopTimer("JamDbInterface_Insert");
    }

    public function Update($jamId, $theme, $startTime, $color, $defaultEntryIconUrl){
        AddActionLog("JamDbInterface_Update");
        StartTimer("JamDbInterface_Update");

        $escapedJamId = $this->database->EscapeString(intval($jamId));
        $escapedTheme = $this->database->EscapeString($theme);
        $escapedStartTime = $this->database->EscapeString($startTime);
        $escapedColors = $this->database->EscapeString($color);
        $escapedDefaultEntryIconUrl = $this->database->EscapeString($defaultEntryIconUrl);

        $sql = "
            UPDATE ".DB_TABLE_JAM."
            SET ".DB_COLUMN_JAM_THEME." = '$escapedTheme',
                ".DB_COLUMN_JAM_START_DATETIME." = '$escapedStartTime',
                ".DB_COLUMN_JAM_COLORS." = '$escapedColors',
                ".DB_COLUMN_JAM_DEFAULT_ICON_URL." = '$escapedDefaultEntryIconUrl'
            WHERE ".DB_COLUMN_JAM_ID." = $escapedJamId;";
        $this->database->Execute($sql);
        
        StopTimer("JamDbInterface_Update");
    }

    public function UpdateJamState($jamId, $jamState){
        AddActionLog("JamDbInterface_UpdateJamState");
        StartTimer("JamDbInterface_UpdateJamState");

        $escapedJamId = $this->database->EscapeString(intval($jamId));
        $escapedJamState = $this->database->EscapeString($jamState);

        $sql = "
            UPDATE ".DB_TABLE_JAM."
            SET ".DB_COLUMN_JAM_STATE." = '$escapedJamState'
            WHERE ".DB_COLUMN_JAM_ID." = $escapedJamId";
        $this->database->Execute($sql);
        
        StopTimer("JamDbInterface_UpdateJamState");
    }

    public function SoftDelete($jamId){
        AddActionLog("JamDbInterface_SoftDelete");
        StartTimer("JamDbInterface_SoftDelete");

        $escapedJamId = $this->database->EscapeString(intval($jamId));

        $sql = "
            UPDATE ".DB_TABLE_JAM." 
            SET ".DB_COLUMN_JAM_DELETED." = 1 
            WHERE ".DB_COLUMN_JAM_ID." = $escapedJamId";
        $this->database->Execute($sql);
        
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
        return $this->database->Execute($sql);
    }
}

?>