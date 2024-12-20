<?php
const DB_TABLE_JAM = "jam";

const DB_COLUMN_JAM_ID = "jam_id";
const DB_COLUMN_JAM_DATETIME = "jam_datetime";
const DB_COLUMN_JAM_IP = "jam_ip";
const DB_COLUMN_JAM_USER_AGENT = "jam_user_agent";
const DB_COLUMN_JAM_USER_ID = "jam_user_id";
const DB_COLUMN_JAM_NUMBER = "jam_jam_number";
const DB_COLUMN_JAM_SELECTED_THEME_ID = "jam_selected_theme_id";
const DB_COLUMN_JAM_THEME = "jam_theme";
const DB_COLUMN_JAM_START_DATETIME = "jam_start_datetime";
const DB_COLUMN_JAM_STREAMER_USER_ID = "jam_streamer_user_id";
const DB_COLUMN_JAM_STREAMER_TWITCH_USERNAME = "jam_streamer_twitch_username";
const DB_COLUMN_JAM_STATE = "jam_state";
const DB_COLUMN_JAM_COLORS = "jam_colors";
const DB_COLUMN_JAM_DEFAULT_ICON_URL = "jam_default_icon_url";
const DB_COLUMN_JAM_EVENT_NAME = "jam_event_name";
const DB_COLUMN_JAM_DELETED = "jam_deleted";

class JamDbInterface{
    private Database $database;
    private array $publicColumns = Array(DB_COLUMN_JAM_ID, DB_COLUMN_JAM_USER_ID, DB_COLUMN_JAM_NUMBER, DB_COLUMN_JAM_SELECTED_THEME_ID, DB_COLUMN_JAM_THEME, DB_COLUMN_JAM_START_DATETIME, DB_COLUMN_JAM_STATE, DB_COLUMN_JAM_COLORS, DB_COLUMN_JAM_DEFAULT_ICON_URL, DB_COLUMN_JAM_EVENT_NAME, DB_COLUMN_JAM_DELETED, DB_COLUMN_JAM_STREAMER_USER_ID, DB_COLUMN_JAM_STREAMER_TWITCH_USERNAME);
    private array $privateColumns = Array(DB_COLUMN_JAM_DATETIME, DB_COLUMN_JAM_IP, DB_COLUMN_JAM_USER_AGENT);

    function __construct(Database &$database) {
        $this->database = $database;
    }

    public function SelectAll(): mysqli_result
    {
        AddActionLog("JamDbInterface_SelectAll");
        StartTimer("JamDbInterface_SelectAll");

        $sql = "
            SELECT ".DB_COLUMN_JAM_ID.", ".DB_COLUMN_JAM_USER_ID.", ".DB_COLUMN_JAM_NUMBER.", ".DB_COLUMN_JAM_SELECTED_THEME_ID.", ".DB_COLUMN_JAM_THEME.", ".DB_COLUMN_JAM_START_DATETIME.", ".DB_COLUMN_JAM_STREAMER_USER_ID.", ".DB_COLUMN_JAM_STREAMER_TWITCH_USERNAME.", ".DB_COLUMN_JAM_STATE.", ".DB_COLUMN_JAM_COLORS.", ".DB_COLUMN_JAM_DEFAULT_ICON_URL.", ".DB_COLUMN_JAM_EVENT_NAME.", ".DB_COLUMN_JAM_DELETED."
            FROM ".DB_TABLE_JAM." 
            ORDER BY ".DB_COLUMN_JAM_NUMBER." DESC";
        
        StopTimer("JamDbInterface_SelectAll");
        return $this->database->Execute($sql);
    }

    public function SelectActive(): mysqli_result
    {
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
    
    public function SelectJamsScheduledByUser($userId): mysqli_result
    {
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

    public function SelectCurrentJamNumberAndId(): mysqli_result
    {
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

    public function SelectIfJamExists($jamId): mysqli_result
    {
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

    public function Insert($ip, $userAgent, $userId, $jamNumber, $selectedThemeId, $theme, $startTime, $colors, $defaultEntryIconUrl, $eventName): void
    {
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
        $escapedEventName = $this->database->EscapeString($eventName);
            
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
            ".DB_COLUMN_JAM_STREAMER_USER_ID.",
            ".DB_COLUMN_JAM_STREAMER_TWITCH_USERNAME.",
            ".DB_COLUMN_JAM_STATE.",
            ".DB_COLUMN_JAM_COLORS.",
            ".DB_COLUMN_JAM_DEFAULT_ICON_URL.",
            ".DB_COLUMN_JAM_EVENT_NAME.",
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
            null,
            '',
            'SCHEDULED',
            '$escapedColors',
            '$escapedDefaultEntryIconUrl',
            '$escapedEventName',
            0);";

        $this->database->Execute($sql);

        StopTimer("JamDbInterface_Insert");
    }

    public function Update($jamId, $theme, $startTime, $streamerUserId, $streamerTwitchUsername, $color, $defaultEntryIconUrl, $eventName): void
    {
        AddActionLog("JamDbInterface_Update");
        StartTimer("JamDbInterface_Update");

        $escapedJamId = $this->database->EscapeString(intval($jamId));
        $escapedTheme = $this->database->EscapeString($theme);
        $escapedStartTime = $this->database->EscapeString($startTime);
        $escapedStreamerUserId = $this->database->EscapeString($streamerUserId);
        $escapedStreamerTwitchUsername = $this->database->EscapeString($streamerTwitchUsername);
        $escapedColors = $this->database->EscapeString($color);
        $escapedDefaultEntryIconUrl = $this->database->EscapeString($defaultEntryIconUrl);
        $escapedEventName = $this->database->EscapeString($eventName);

        $sql = "
            UPDATE ".DB_TABLE_JAM."
            SET ".DB_COLUMN_JAM_THEME." = '$escapedTheme',
                ".DB_COLUMN_JAM_START_DATETIME." = '$escapedStartTime',
                ".DB_COLUMN_JAM_STREAMER_USER_ID." = '$escapedStreamerUserId',
                ".DB_COLUMN_JAM_STREAMER_TWITCH_USERNAME." = '$escapedStreamerTwitchUsername',
                ".DB_COLUMN_JAM_COLORS." = '$escapedColors',
                ".DB_COLUMN_JAM_DEFAULT_ICON_URL." = '$escapedDefaultEntryIconUrl',
                ".DB_COLUMN_JAM_EVENT_NAME." = '$escapedEventName'
            WHERE ".DB_COLUMN_JAM_ID." = $escapedJamId;";
        $this->database->Execute($sql);
        
        StopTimer("JamDbInterface_Update");
    }

    public function UpdateJamState($jamId, $jamState): void
    {
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

    public function UpdateStreamer($jamId, $streamerUserId, $streamerTwitchUsername): void
    {
        AddActionLog("JamDbInterface_UpdateStreamer");
        StartTimer("JamDbInterface_UpdateStreamer");

        $escapedJamId = $this->database->EscapeString(intval($jamId));
        $escapedStreamerUserId = $this->database->EscapeString($streamerUserId);
        $escapedStreamerTwitchUsername = $this->database->EscapeString($streamerTwitchUsername);

        $sql = "
            UPDATE ".DB_TABLE_JAM."
            SET ".DB_COLUMN_JAM_STREAMER_USER_ID." = '$escapedStreamerUserId',
                ".DB_COLUMN_JAM_STREAMER_TWITCH_USERNAME." = '$escapedStreamerTwitchUsername'
            WHERE ".DB_COLUMN_JAM_ID." = $escapedJamId";
        $this->database->Execute($sql);
        
        StopTimer("JamDbInterface_UpdateStreamer");
    }

    public function SoftDelete($jamId): void
    {
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

    public function SelectPublicData(): mysqli_result
    {
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