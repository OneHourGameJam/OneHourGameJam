<?php

class GameModel{
	public $Id;
	public $JamId;
	public $JamNumber;
	public $Title;
	public $Description;
	public $AuthorUserId;
	public $UrlScreenshot;
	public $Color;
	public $Deleted;
}

class GameData{
    public $GameModels;
    public $GamesByUserId;

    function __construct() {
        $this->GameModels = $this->LoadGames();
        $this->GamesByUserId = $this->GroupGamesByUserId();
    }

    function LoadGames(){
        global $dbConn;
        AddActionLog("LoadGames");
        StartTimer("LoadGames");

        $gameModels = Array();

        $sql = "SELECT entry_id, entry_jam_id, entry_jam_number, entry_title, entry_description, entry_author_user_id, entry_screenshot_url, entry_color, entry_deleted
        FROM entry ORDER BY entry_id DESC";
        $data = mysqli_query($dbConn, $sql);
        $sql = "";

        while($info = mysqli_fetch_array($data)){
            $game = new GameModel();

            $game->Id = intval($info["entry_id"]);
            $game->JamId = intval($info["entry_jam_id"]);
            $game->JamNumber = intval($info["entry_jam_number"]);
            $game->Title = $info["entry_title"];
            $game->Description = $info["entry_description"];
            $game->AuthorUserId = $info["entry_author_user_id"];
            $game->UrlScreenshot = $info["entry_screenshot_url"];
            $game->Color = $info["entry_color"];
            $game->Deleted = $info["entry_deleted"];

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

    //Returns true / false based on whether or not the specified entry exists (and has not been deleted)
    function EntryExists($gameID){
        global $dbConn;
        AddActionLog("EntryExists");
        StartTimer("EntryExists");
    
        //Validate values
        $gameID = intval($gameID);
        if($gameID <= 0){
            StopTimer("EntryExists");
            return FALSE;
        }
    
        $escapedEntryID = mysqli_real_escape_string($dbConn, "$gameID");
    
        $sql = "
            SELECT 1
            FROM entry
            WHERE entry_id = $escapedEntryID
            AND entry_deleted = 0;
            ";
        $data = mysqli_query($dbConn, $sql);
        $sql = "";
    
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
        global $dbConn;
        AddActionLog("GetEntriesOfUserFormatted");
        StartTimer("GetEntriesOfUserFormatted");
    
        $escapedAuthorUserId = mysqli_real_escape_string($dbConn, $authorUserId);
        $sql = "
            SELECT *
            FROM entry
            WHERE entry_author_user_id = '$escapedAuthorUserId';
        ";
        $data = mysqli_query($dbConn, $sql);
        $sql = "";
    
        StopTimer("GetEntriesOfUserFormatted");
        return ArrayToHTML(MySQLDataToArray($data));
    }
}

?>