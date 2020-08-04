<?php
define("DB_TABLE_ENTRY", "entry");

define("DB_COLUMN_ENTRY_ID",                "entry_id");
define("DB_COLUMN_ENTRY_DATETIME",          "entry_datetime");
define("DB_COLUMN_ENTRY_IP",                "entry_ip");
define("DB_COLUMN_ENTRY_USER_AGENT",        "entry_user_agent");
define("DB_COLUMN_ENTRY_JAM_ID",            "entry_jam_id");
define("DB_COLUMN_ENTRY_JAM_NUMBER",        "entry_jam_number");
define("DB_COLUMN_ENTRY_TITLE",             "entry_title");
define("DB_COLUMN_ENTRY_DESCRIPTION",       "entry_description");
define("DB_COLUMN_ENTRY_AUTHOR_USER_ID",    "entry_author_user_id");
define("DB_COLUMN_ENTRY_SCREENSHOT_URL",    "entry_screenshot_url");
define("DB_COLUMN_ENTRY_BACKGROUND_COLOR",  "entry_background_color");
define("DB_COLUMN_ENTRY_TEXT_COLOR",        "entry_text_color");
define("DB_COLUMN_ENTRY_DELETED",           "entry_deleted");

class GameDbInterface{
    private $dbConnection;
    private $publicColumns = Array(DB_COLUMN_ENTRY_ID, DB_COLUMN_ENTRY_JAM_ID, DB_COLUMN_ENTRY_JAM_NUMBER, DB_COLUMN_ENTRY_TITLE, DB_COLUMN_ENTRY_DESCRIPTION, DB_COLUMN_ENTRY_AUTHOR_USER_ID, DB_COLUMN_ENTRY_SCREENSHOT_URL, DB_COLUMN_ENTRY_BACKGROUND_COLOR, DB_COLUMN_ENTRY_TEXT_COLOR, DB_COLUMN_ENTRY_DELETED);
    private $privateColumns = Array(DB_COLUMN_ENTRY_DATETIME, DB_COLUMN_ENTRY_IP, DB_COLUMN_ENTRY_USER_AGENT);

    function __construct(&$dbConn) {
        $this->dbConnection = $dbConn;
    }

    public function SelectAll(){
        AddActionLog("GameDbInterface_SelectAll");
        StartTimer("GameDbInterface_SelectAll");

        $sql = "
            SELECT ".DB_COLUMN_ENTRY_ID.", ".DB_COLUMN_ENTRY_JAM_ID.", ".DB_COLUMN_ENTRY_JAM_NUMBER.", ".DB_COLUMN_ENTRY_TITLE.", ".DB_COLUMN_ENTRY_DESCRIPTION.", ".DB_COLUMN_ENTRY_AUTHOR_USER_ID.", ".DB_COLUMN_ENTRY_SCREENSHOT_URL.", ".DB_COLUMN_ENTRY_BACKGROUND_COLOR.", ".DB_COLUMN_ENTRY_TEXT_COLOR.", ".DB_COLUMN_ENTRY_DELETED."
            FROM ".DB_TABLE_ENTRY."
            ORDER BY ".DB_COLUMN_ENTRY_ID." DESC";
        
        StopTimer("GameDbInterface_SelectAll");
        return mysqli_query($this->dbConnection, $sql);
    }

    public function SelectIfExists($entryId){
        AddActionLog("GameDbInterface_SelectIfExists");
        StartTimer("GameDbInterface_SelectIfExists");

        $escapedEntryID = mysqli_real_escape_string($this->dbConnection, intval($entryId));
        $sql = "
            SELECT 1
            FROM ".DB_TABLE_ENTRY."
            WHERE ".DB_COLUMN_ENTRY_ID." = $escapedEntryID
              AND ".DB_COLUMN_ENTRY_DELETED." = 0;";
        
        StopTimer("GameDbInterface_SelectIfExists");
        return mysqli_query($this->dbConnection, $sql);
    }

    public function SelectEntriesForAuthor($authorUserId){
        AddActionLog("GameDbInterface_SelectEntriesForAuthor");
        StartTimer("GameDbInterface_SelectEntriesForAuthor");
    
        $escapedAuthorUserId = mysqli_real_escape_string($this->dbConnection, $authorUserId);
        $sql = "
            SELECT *
            FROM ".DB_TABLE_ENTRY."
            WHERE ".DB_COLUMN_ENTRY_AUTHOR_USER_ID." = '$escapedAuthorUserId';
        ";
        
        StopTimer("GameDbInterface_SelectEntriesForAuthor");
        return mysqli_query($this->dbConnection, $sql);
    }

    public function SelectSingleEntryId($jamId, $authorId){
        AddActionLog("GameDbInterface_SelectSingleEntryId");
        StartTimer("GameDbInterface_SelectSingleEntryId");
        
        $escaped_jamId = mysqli_real_escape_string($this->dbConnection, $jamId);
        $escaped_author_user_id = mysqli_real_escape_string($this->dbConnection, $authorId);

        $sql = "
            SELECT entry_id
            FROM entry
            WHERE entry_jam_id = $escaped_jamId
              AND entry_author_user_id = $escaped_author_user_id
              AND entry_deleted = 0
        ";
        
        StopTimer("GameDbInterface_SelectSingleEntryId");
        return mysqli_query($this->dbConnection, $sql);
    }

    public function SelectEntriesInJam($jamId, $authorId){
        AddActionLog("GameDbInterface_SelectEntriesForJam");
        StartTimer("GameDbInterface_SelectEntriesForJam");
        
        $escapedJamID = mysqli_real_escape_string($this->dbConnection, $jamId);

        $sql = "
            SELECT *
            FROM entry
            WHERE entry_jam_id = $escapedJamID
              AND entry_deleted = 0;
        ";
        
        StopTimer("GameDbInterface_SelectEntriesForJam");
        return mysqli_query($this->dbConnection, $sql);
    }

    public function Insert($ip, $userAgent, $jamId, $jamNumber, $gameName, $description, $userId, $screenshotURL, $colorBackgroundWithoutHash, $colorTextWithoutHash){
        AddActionLog("GameDbInterface_Insert");
        StartTimer("GameDbInterface_Insert");

        $escaped_ip = mysqli_real_escape_string($this->dbConnection, $ip);
        $escaped_userAgent = mysqli_real_escape_string($this->dbConnection, $userAgent);
        $escaped_jamId = mysqli_real_escape_string($this->dbConnection, $jamId);
        $escaped_jamNumber = mysqli_real_escape_string($this->dbConnection, $jamNumber);
        $escaped_gameName = mysqli_real_escape_string($this->dbConnection, $gameName);
        $escaped_description = mysqli_real_escape_string($this->dbConnection, $description);
        $escaped_author_user_id = mysqli_real_escape_string($this->dbConnection, $userId);
        $escaped_ssURL = mysqli_real_escape_string($this->dbConnection, $screenshotURL);
        $escaped_colorBackgroundWithoutHash = mysqli_real_escape_string($this->dbConnection, $colorBackgroundWithoutHash);
        $escaped_colorTextWithoutHash = mysqli_real_escape_string($this->dbConnection, $colorTextWithoutHash);

        $sql = "
            INSERT INTO entry
            (entry_id,
            entry_datetime,
            entry_ip,
            entry_user_agent,
            entry_jam_id,
            entry_jam_number,
            entry_title,
            entry_description,
            entry_author_user_id,
            entry_screenshot_url,
            entry_background_color,
            entry_text_color)
            VALUES
            (null,
            Now(),
            '$escaped_ip',
            '$escaped_userAgent',
            $escaped_jamId,
            $escaped_jamNumber,
            '$escaped_gameName',
            '$escaped_description',
            $escaped_author_user_id,
            '$escaped_ssURL',
            '$escaped_colorBackgroundWithoutHash',
            '$escaped_colorTextWithoutHash');
        ";
        mysqli_query($this->dbConnection, $sql);
        
        StopTimer("GameDbInterface_Insert");
    }

    public function Update($jamNumber, $userId, $gameName, $screenshotURL, $description, $colorBackgroundWithoutHash, $colorTextWithoutHash){
        AddActionLog("GameDbInterface_Update");
        StartTimer("GameDbInterface_Update");

		$escapedGameName = mysqli_real_escape_string($this->dbConnection, $gameName);
		$escapedScreenshotURL = mysqli_real_escape_string($this->dbConnection, $screenshotURL);
		$escapedDescription = mysqli_real_escape_string($this->dbConnection, $description);
		$escapedAuthorUserId = mysqli_real_escape_string($this->dbConnection, $userId);
		$escaped_jamNumber = mysqli_real_escape_string($this->dbConnection, $jamNumber);
		$escaped_colorBackgroundWithoutHash = mysqli_real_escape_string($this->dbConnection, $colorBackgroundWithoutHash);
		$escaped_colorTextWithoutHash = mysqli_real_escape_string($this->dbConnection, $colorTextWithoutHash);

		$sql = "
		UPDATE entry
		SET
			entry_title = '$escapedGameName',
			entry_screenshot_url = '$escapedScreenshotURL',
			entry_description = '$escapedDescription',
			entry_background_color = '$escaped_colorBackgroundWithoutHash',
			entry_text_color = '$escaped_colorTextWithoutHash'
		WHERE
			entry_author_user_id = $escapedAuthorUserId
		AND entry_jam_number = $escaped_jamNumber
		AND entry_deleted = 0;
		";
		mysqli_query($this->dbConnection, $sql);
        
        StopTimer("GameDbInterface_Update");
    }

    public function SoftDelete($entryId){
        AddActionLog("GameDbInterface_SoftDelete");
        StartTimer("GameDbInterface_SoftDelete");

		$escapedEntryId = mysqli_real_escape_string($this->dbConnection, $entryId);

        $sql = "
            UPDATE entry 
            SET entry_deleted = 1 
            WHERE entry_id = $escapedEntryId";
		mysqli_query($this->dbConnection, $sql);
        
        StopTimer("GameDbInterface_SoftDelete");
    }

    public function SelectPublicData(){
        AddActionLog("GameDbInterface_SelectPublicData");
        StartTimer("GameDbInterface_SelectPublicData");

        $sql = "
            SELECT ".implode(",", $this->publicColumns)."
            FROM ".DB_TABLE_ENTRY.";
        ";

        StopTimer("GameDbInterface_SelectPublicData");
        return mysqli_query($this->dbConnection, $sql);
    }
}

?>