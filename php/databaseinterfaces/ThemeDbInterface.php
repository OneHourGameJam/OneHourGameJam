<?php

const DB_TABLE_THEME = "theme";

const DB_COLUMN_THEME_ID = "theme_id";
const DB_COLUMN_THEME_DATETIME = "theme_datetime";
const DB_COLUMN_THEME_IP = "theme_ip";
const DB_COLUMN_THEME_USER_AGENT = "theme_user_agent";
const DB_COLUMN_THEME_TEXT = "theme_text";
const DB_COLUMN_THEME_AUTHOR_USER_ID = "theme_author_user_id";
const DB_COLUMN_THEME_BANNED = "theme_banned";
const DB_COLUMN_THEME_DELETED = "theme_deleted";

class ThemeDbInterface{
    private Database $database;
    private array $publicColumns = Array(DB_COLUMN_THEME_ID, DB_COLUMN_THEME_TEXT, DB_COLUMN_THEME_AUTHOR_USER_ID, DB_COLUMN_THEME_BANNED, DB_COLUMN_THEME_DELETED);
    private array $privateColumns = Array(DB_COLUMN_THEME_DATETIME, DB_COLUMN_THEME_IP, DB_COLUMN_THEME_USER_AGENT);

    function __construct(Database &$database) {
        $this->database = $database;
    }

    public function SelectAllActiveWithResults(): mysqli_result
    {
        AddActionLog("ThemeDbInterface_SelectAllActiveWithResults");
        StartTimer("ThemeDbInterface_SelectAllActiveWithResults");

        //Fill list of themes - will return same theme row multiple times (once for each valid themevote_type)
        $sql = "
            SELECT ".DB_COLUMN_THEME_ID.", ".DB_COLUMN_THEME_TEXT.", ".DB_COLUMN_THEME_AUTHOR_USER_ID.", ".DB_COLUMN_THEME_BANNED.", ".DB_COLUMN_THEMEVOTE_TYPE.", count(".DB_COLUMN_THEMEVOTE_ID.") AS themevote_count, DATEDIFF(Now(), ".DB_COLUMN_THEME_DATETIME.") as theme_daysago, ".DB_COLUMN_THEME_DELETED."
            FROM (
                ".DB_TABLE_THEME." LEFT JOIN ".DB_TABLE_THEMEVOTE." 
                ON (".DB_TABLE_THEMEVOTE.".".DB_COLUMN_THEMEVOTE_THEME_ID." = ".DB_TABLE_THEME.".".DB_COLUMN_THEME_ID.")
            )
            WHERE ".DB_COLUMN_THEME_DELETED." != 1
            GROUP BY ".DB_COLUMN_THEME_ID.", ".DB_COLUMN_THEMEVOTE_TYPE."
            ORDER BY ".DB_COLUMN_THEME_BANNED." ASC, ".DB_COLUMN_THEME_ID." ASC;
        ";

        StopTimer("ThemeDbInterface_SelectAllActiveWithResults");
        return $this->database->Execute($sql);;
    }

    public function SelectAllWithResults(): mysqli_result
    {
        AddActionLog("ThemeDbInterface_SelectAllWithResults");
        StartTimer("ThemeDbInterface_SelectAllWithResults");

        //Fill list of themes - will return same theme row multiple times (once for each valid themevote_type)
        $sql = "
            SELECT ".DB_COLUMN_THEME_ID.", ".DB_COLUMN_THEME_TEXT.", ".DB_COLUMN_THEME_AUTHOR_USER_ID.", ".DB_COLUMN_THEME_BANNED.", ".DB_COLUMN_THEMEVOTE_TYPE.", count(".DB_COLUMN_THEMEVOTE_ID.") AS themevote_count, DATEDIFF(Now(), ".DB_COLUMN_THEME_DATETIME.") as theme_daysago, ".DB_COLUMN_THEME_DELETED."
            FROM (
                ".DB_TABLE_THEME." LEFT JOIN ".DB_TABLE_THEMEVOTE." 
                ON (".DB_TABLE_THEMEVOTE.".".DB_COLUMN_THEMEVOTE_THEME_ID." = ".DB_TABLE_THEME.".".DB_COLUMN_THEME_ID.")
            )
            GROUP BY ".DB_COLUMN_THEME_ID.", ".DB_COLUMN_THEMEVOTE_TYPE."
            ORDER BY ".DB_COLUMN_THEME_BANNED." ASC, ".DB_COLUMN_THEME_ID." ASC;
        ";

        StopTimer("ThemeDbInterface_SelectAllWithResults");
        return $this->database->Execute($sql);;
    }

    public function SelectThemesByUser($userId): mysqli_result
    {
        AddActionLog("ThemeDbInterface_SelectThemesByUser");
        StartTimer("ThemeDbInterface_SelectThemesByUser");
    
        $escapedUserId = $this->database->EscapeString($userId);
        $sql = "
            SELECT *
            FROM ".DB_TABLE_THEME."
            WHERE ".DB_COLUMN_THEME_AUTHOR_USER_ID." = $escapedUserId;
        ";
        
        StopTimer("ThemeDbInterface_SelectThemesByUser");
        return $this->database->Execute($sql);;
    }

    public function SelectIfExists($themeId): mysqli_result
    {
        AddActionLog("ThemeDbInterface_SelectIfExists");
        StartTimer("ThemeDbInterface_SelectIfExists");
        
        $escapedThemeId = $this->database->EscapeString($themeId);
        $sql = "
            SELECT ".DB_COLUMN_THEME_ID." 
            FROM ".DB_TABLE_THEME." 
            WHERE ".DB_COLUMN_THEME_ID." = '$escapedThemeId';
        ";
        
        StopTimer("ThemeDbInterface_SelectIfExists");
        return $this->database->Execute($sql);;
    }

    public function SelectIfActive($themeId): mysqli_result
    {
        AddActionLog("ThemeDbInterface_SelectIfActive");
        StartTimer("ThemeDbInterface_SelectIfActive");
        
        $escapedThemeId = $this->database->EscapeString($themeId);
        $sql = "
            SELECT ".DB_COLUMN_THEME_ID." 
            FROM ".DB_TABLE_THEME." 
            WHERE ".DB_COLUMN_THEME_DELETED." != 1 
              AND ".DB_COLUMN_THEME_ID." = $escapedThemeId";
        
        StopTimer("ThemeDbInterface_SelectIfActive");
        return $this->database->Execute($sql);;
    }

    public function Insert($ip, $userAgent, $newTheme, $userId): void
    {
        AddActionLog("ThemeDbInterface_Insert");
        StartTimer("ThemeDbInterface_Insert");

        $clean_ip = $this->database->EscapeString($ip);
        $clean_userAgent = $this->database->EscapeString($userAgent);
        $clean_newTheme = $this->database->EscapeString($newTheme);
        $clean_user_id = $this->database->EscapeString($userId);
    
        $sql = "
            INSERT INTO ".DB_TABLE_THEME."
            (".DB_COLUMN_THEME_DATETIME.", ".DB_COLUMN_THEME_IP.", ".DB_COLUMN_THEME_USER_AGENT.", ".DB_COLUMN_THEME_TEXT.", ".DB_COLUMN_THEME_AUTHOR_USER_ID.")
            VALUES (Now(), '$clean_ip', '$clean_userAgent', '$clean_newTheme', $clean_user_id);
        ";
        
        $this->database->Execute($sql);;
        StopTimer("ThemeDbInterface_Insert");
    }

    public function Ban($themeId): void
    {
        AddActionLog("ThemeDbInterface_Ban");
        StartTimer("ThemeDbInterface_Ban");

        $escapedThemeId = $this->database->EscapeString($themeId);
        $sql = "
            UPDATE ".DB_TABLE_THEME." 
            SET ".DB_COLUMN_THEME_BANNED." = 1 
            WHERE ".DB_COLUMN_THEME_BANNED." != 1 
              AND ".DB_COLUMN_THEME_ID." = '$escapedThemeId';
        ";
        
        $this->database->Execute($sql);;
        StopTimer("ThemeDbInterface_Ban");
    }

    public function Unban($themeId): void
    {
        AddActionLog("ThemeDbInterface_Ban");
        StartTimer("ThemeDbInterface_Ban");

        $escapedThemeId = $this->database->EscapeString($themeId);
        $sql = "
            UPDATE ".DB_TABLE_THEME." 
            SET ".DB_COLUMN_THEME_BANNED." = 0 
            WHERE ".DB_COLUMN_THEME_BANNED." != 0 
              AND ".DB_COLUMN_THEME_ID." = '$escapedThemeId';
        ";
        
        $this->database->Execute($sql);;
        StopTimer("ThemeDbInterface_Ban");
    }

    public function SoftDelete($themeId): void
    {
        AddActionLog("ThemeDbInterface_SoftDelete");
        StartTimer("ThemeDbInterface_SoftDelete");

        $escapedThemeId = $this->database->EscapeString($themeId);
        $sql = "
            UPDATE ".DB_TABLE_THEME."
            SET ".DB_COLUMN_THEME_DELETED." = 1
            WHERE ".DB_COLUMN_THEME_ID." = $escapedThemeId;
        ";

        $this->database->Execute($sql);;
        StopTimer("ThemeDbInterface_SoftDelete");        
    }

    public function SelectPublicData(): mysqli_result
    {
        AddActionLog("ThemeDbInterface_SelectPublicData");
        StartTimer("ThemeDbInterface_SelectPublicData");

        $sql = "
            SELECT ".implode(",", $this->publicColumns)."
            FROM ".DB_TABLE_THEME.";
        ";

        StopTimer("ThemeDbInterface_SelectPublicData");
        return $this->database->Execute($sql);;
    }

}

?>