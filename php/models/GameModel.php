<?php

class GameModel{
	public string $Id;
	public string $JamId;
	public string $JamNumber;
	public string $Title;
	public string $Description;
	public string $AuthorUserId;
	public string $UrlScreenshot;
	public string $BackgroundColor;
	public string $TextColor;
	public bool $Deleted;
}

class GameData{
    public array $GameModels;
    public array $GamesByUserId;

    private GameDbInterface $gameDbInterface;

    function __construct(GameDbInterface &$gameDbInterface) {
        $this->gameDbInterface = $gameDbInterface;
        $this->GameModels = $this->LoadGames();
        $this->GamesByUserId = $this->GroupGamesByUserId();
    }

//////////////////////// MODEL CONSTRUCTOR

    function LoadGames(): array
    {
        AddActionLog("LoadGames");
        StartTimer("LoadGames");

        $gameModels = Array();

        $data = $this->gameDbInterface->SelectAll();

        while($info = mysqli_fetch_array($data)){
            $game = new GameModel();

            $game->Id = intval($info[DB_COLUMN_ENTRY_ID]);
            $game->JamId = intval($info[DB_COLUMN_ENTRY_JAM_ID]);
            $game->JamNumber = intval($info[DB_COLUMN_ENTRY_JAM_NUMBER]);
            $game->Title = ($info[DB_COLUMN_ENTRY_TITLE]) ? $info[DB_COLUMN_ENTRY_TITLE] : "Untitled";
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

    private function GroupGamesByUserId(): array
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

    public function GetGamesMadeByUserId($userId): array{
        if(isset($this->GamesByUserId[$userId])){
            return $this->GamesByUserId[$userId];
        }
        return Array();
    }

//////////////////////// END MODEL CONSTRUCTOR
    
//////////////////////// DATABASE ACTIONS (select, insert, update)

    //Returns true / false based on whether or not the specified entry exists (and has not been deleted)
    public function EntryExists($entryId): bool
    {
        AddActionLog("EntryExists");
        StartTimer("EntryExists");
    
        //Validate values
        if($entryId <= 0){
            StopTimer("EntryExists");
            return FALSE;
        }
    
        $data = $this->gameDbInterface->SelectIfExists($entryId);

        StopTimer("EntryExists");
        if(mysqli_fetch_array($data)){
            return true;
        }
        return false;
    }
    
    function GetEntriesOfUserFormatted($authorUserId): string
    {
        AddActionLog("GetEntriesOfUserFormatted");
        StartTimer("GetEntriesOfUserFormatted");
    
        $data = $this->gameDbInterface->SelectEntriesForAuthor($authorUserId);
    
        StopTimer("GetEntriesOfUserFormatted");
        return ArrayToHTML(MySQLDataToArray($data));
    }

//////////////////////// END DATABASE ACTIONS

//////////////////////// PUBLIC DATA EXPORT

    function GetAllPublicData(): array
    {
        AddActionLog("GameData_GetAllPublicData");
        StartTimer("GameData_GetAllPublicData");
        
        $dataFromDatabase = MySQLDataToArray($this->gameDbInterface->SelectPublicData());

        foreach($dataFromDatabase as $i => $row){
            $dataFromDatabase[$i][DB_COLUMN_ENTRY_DATETIME] = gmdate("Y-m-d H:i:s", time());
            $dataFromDatabase[$i][DB_COLUMN_ENTRY_IP] = OVERRIDE_MIGRATION;
            $dataFromDatabase[$i][DB_COLUMN_ENTRY_USER_AGENT] = OVERRIDE_MIGRATION;
        }

        StopTimer("GameData_GetAllPublicData");
        return $dataFromDatabase;
    }

//////////////////////// END PUBLIC DATA EXPORT
}

?>