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

class GameModel{
	public $Id;
	public $JamId;
	public $JamNumber;
	public $Title;
	public $Description;
	public $AuthorUserId;
	public $UrlScreenshot;
	public $BackgroundColor;
	public $TextColor;
	public $Deleted;
}

class GameData{
    public $GameModels;
    public $GamesByUserId;

    private $dbConnection;
    private $publicColumns = Array(DB_COLUMN_ENTRY_ID, DB_COLUMN_ENTRY_JAM_ID, DB_COLUMN_ENTRY_JAM_NUMBER, DB_COLUMN_ENTRY_TITLE, DB_COLUMN_ENTRY_DESCRIPTION, DB_COLUMN_ENTRY_AUTHOR_USER_ID, DB_COLUMN_ENTRY_SCREENSHOT_URL, DB_COLUMN_ENTRY_BACKGROUND_COLOR, DB_COLUMN_ENTRY_TEXT_COLOR, DB_COLUMN_ENTRY_DELETED);
    private $privateColumns = Array(DB_COLUMN_ENTRY_DATETIME, DB_COLUMN_ENTRY_IP, DB_COLUMN_ENTRY_USER_AGENT);

    function __construct(&$dbConn) {
        $this->dbConnection = $dbConn;
        $this->GameModels = $this->LoadGames();
        $this->GamesByUserId = $this->GroupGamesByUserId();
    }

//////////////////////// MODEL CONSTRUCTOR

    function LoadGames(){
        AddActionLog("LoadGames");
        StartTimer("LoadGames");

        $gameModels = Array();

        $data = $this->SelectAll();

        while($info = mysqli_fetch_array($data)){
            $game = new GameModel();

            $game->Id = intval($info[DB_COLUMN_ENTRY_ID]);
            $game->JamId = intval($info[DB_COLUMN_ENTRY_JAM_ID]);
            $game->JamNumber = intval($info[DB_COLUMN_ENTRY_JAM_NUMBER]);
            $game->Title = $info[DB_COLUMN_ENTRY_TITLE];
            $game->Description = $info[DB_COLUMN_ENTRY_DESCRIPTION];
            $game->AuthorUserId = $info[DB_COLUMN_ENTRY_AUTHOR_USER_ID];
            $game->UrlScreenshot = $info[DB_COLUMN_ENTRY_SCREENSHOT_URL];
            $game->BackgroundColor = $info[DB_COLUMN_ENTRY_BACKGROUND_COLOR];
            $game->TextColor = $info[DB_COLUMN_ENTRY_TEXT_COLOR];
            $game->Deleted = $info[DB_COLUMN_ENTRY_DELETED];

            $gameModels[$game->Id] = $game;
        }

        StopTimer("LoadGames");
        return $gameModels;
    }

    private function GroupGamesByUserId()
    {
        AddActionLog("GroupGamesByUserId");
        StartTimer("GroupGamesByUserId");
        
        $GamesByUserId = Array();
        foreach($this->GameModels as $i => $gameModel) {
            $userId = $gameModel->AuthorUserId;
            if (!isset($GamesByUserId[$userId])){
                $GamesByUserId[$userId] = Array();
            }
            $GamesByUserId[$userId][] = $gameModel;
        }
    
        StopTimer("GroupGamesByUserId");
        return $GamesByUserId;
    }

    public function GetGamesMadeByUserId($userId){
        if(isset($this->GamesByUserId[$userId])){
            return $this->GamesByUserId[$userId];
        }
        return Array();
    }

//////////////////////// END MODEL CONSTRUCTOR
    
//////////////////////// DATABASE ACTIONS (select, insert, update)

    //Returns true / false based on whether or not the specified entry exists (and has not been deleted)
    function EntryExists($entryId){
        AddActionLog("EntryExists");
        StartTimer("EntryExists");
    
        //Validate values
        if($gameID <= 0){
            StopTimer("EntryExists");
            return FALSE;
        }
    
        $data = $this->SelectIfExists($entryId);
    
        if(mysqli_fetch_array($data)){
            StopTimer("EntryExists");
            return true;
        }else{
            StopTimer("EntryExists");
            return false;
        }
        
        StopTimer("EntryExists");
    }
    
    function GetEntriesOfUserFormatted($authorUserId){
        AddActionLog("GetEntriesOfUserFormatted");
        StartTimer("GetEntriesOfUserFormatted");
    
        $data = $this->SelectEntriesForAuthor($authorUserId);
    
        StopTimer("GetEntriesOfUserFormatted");
        return ArrayToHTML(MySQLDataToArray($data));
    }

//////////////////////// END DATABASE ACTIONS
    
//////////////////////// FUCNTION SQL

    private function SelectAll(){
        AddActionLog("GameData_SelectAll");
        StartTimer("GameData_SelectAll");

        $sql = "
            SELECT ".DB_COLUMN_ENTRY_ID.", ".DB_COLUMN_ENTRY_JAM_ID.", ".DB_COLUMN_ENTRY_JAM_NUMBER.", ".DB_COLUMN_ENTRY_TITLE.", ".DB_COLUMN_ENTRY_DESCRIPTION.", ".DB_COLUMN_ENTRY_AUTHOR_USER_ID.", ".DB_COLUMN_ENTRY_SCREENSHOT_URL.", ".DB_COLUMN_ENTRY_BACKGROUND_COLOR.", ".DB_COLUMN_ENTRY_TEXT_COLOR.", ".DB_COLUMN_ENTRY_DELETED."
            FROM ".DB_TABLE_ENTRY."
            ORDER BY ".DB_COLUMN_ENTRY_ID." DESC";
        
        StopTimer("GameData_SelectAll");
        return mysqli_query($this->dbConnection, $sql);
    }

    private function SelectIfExists($entryId){
        AddActionLog("GameData_SelectIfExists");
        StartTimer("GameData_SelectIfExists");

        $escapedEntryID = mysqli_real_escape_string($this->dbConnection, intval($entryId));
        $sql = "
            SELECT 1
            FROM ".DB_TABLE_ENTRY."
            WHERE ".DB_COLUMN_ENTRY_ID." = $escapedEntryID
              AND ".DB_COLUMN_ENTRY_DELETED." = 0;";
        
        StopTimer("GameData_SelectIfExists");
        return mysqli_query($this->dbConnection, $sql);
    }

    private function SelectEntriesForAuthor($authorUserId){
        AddActionLog("GameData_SelectEntriesForAuthor");
        StartTimer("GameData_SelectEntriesForAuthor");
    
        $escapedAuthorUserId = mysqli_real_escape_string($this->dbConnection, $authorUserId);
        $sql = "
            SELECT *
            FROM ".DB_TABLE_ENTRY."
            WHERE ".DB_COLUMN_ENTRY_AUTHOR_USER_ID." = '$escapedAuthorUserId';
        ";
        
        StopTimer("GameData_SelectEntriesForAuthor");
        return mysqli_query($this->dbConnection, $sql);
    }

//////////////////////// END FUCNTION SQL

//////////////////////// PUBLIC DATA EXPORT

    function GetAllPublicData(){
        AddActionLog("GameData_GetAllPublicData");
        StartTimer("GameData_GetAllPublicData");
        
        $dataFromDatabase = MySQLDataToArray($this->SelectPublicData());

        foreach($dataFromDatabase as $i => $row){
            $dataFromDatabase[$i][DB_COLUMN_ENTRY_DATETIME] = gmdate("Y-m-d H:i:s", time());
            $dataFromDatabase[$i][DB_COLUMN_ENTRY_IP] = "MIGRATION";
            $dataFromDatabase[$i][DB_COLUMN_ENTRY_USER_AGENT] = "MIGRATION";
        }

        StopTimer("GameData_GetAllPublicData");
        return $dataFromDatabase;
    }

    private function SelectPublicData(){
        AddActionLog("GameData_SelectPublicData");
        StartTimer("GameData_SelectPublicData");

        $sql = "
            SELECT ".implode(",", $this->publicColumns)."
            FROM ".DB_TABLE_ENTRY.";
        ";

        StopTimer("GameData_SelectPublicData");
        return mysqli_query($this->dbConnection, $sql);
    }

//////////////////////// END PUBLIC DATA EXPORT
}

?>