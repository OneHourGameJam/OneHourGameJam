<?php
namespace Plugins\Entry;

define("PAGE_EDIT_ENTRY", "editentry");
define("PAGE_SUBMIT", "submit");
define("PAGE_ENTRIES", "entries");
define("RENDER_GAMES", "RenderGames");

class GamePlugin extends \AbstractPlugin implements \IEntryRenderer{
    public $NameInTemplate = "entry";

    public $GameDbInterface;
    public $GameData;

    public function ReceiveMessage(\AbstractMessage &$message){
        if($message instanceof \LogMessage){
            $logType = $message->LogType;
            $text = $message->Text;
            $originatorDescriptor = $message->OriginatorDescriptor;
            $originatorUserId = $message->OriginatorUserId;
            $subjectUserId = $message->SubjectUserId;

            $this->AdminLogData->AddToAdminLog($logType, $text, $subjectUserId, $originatorUserId, $originatorDescriptor);
        }
    }

    public function PageSettings(){
        return Array(
            PAGE_SUBMIT => Array(
                "page_title" => "Submit Game",
                "authorization_level" => AUTHORIZATION_LEVEL_USER,
                "template_file" => "submit.html",
                "dependencies" => Array(RENDER_CONFIG => RENDER_DEPTH_NONE),
            ),
            PAGE_ENTRIES => Array(
                "page_title" => "Entries",
                "authorization_level" => AUTHORIZATION_LEVEL_NONE,
                "template_file" => "entries.html",
                "dependencies" => Array(RENDER_GAMES => RENDER_DEPTH_GAMES),
            ),
            PAGE_EDIT_ENTRY => Array(
                "page_title" => "Edit Entry",
                "authorization_level" => AUTHORIZATION_LEVEL_ADMIN,
                "template_file" => "editentry.html",
                "dependencies" => Array(  ),
            )
        );
    }

    public function EstablishDatabaseConnection(){
        $database = new \Database();
	    $this->GameDbInterface = new GameDbInterface($database);
    }

    public function RetrieveData(){
        $this->GameData = new GameData($this->GameDbInterface);
    }

    public function GetUserData($userId){
        $userData = Array();
        $userData["Entries"] = $this->GameData->GetEntriesOfUserFormatted($userId);
        
        return $userData;
    }

    public function ShouldBeRendered(&$dependencies){
        if(FindDependency(RENDER_GAMES, $dependencies) !== false){
            return true;
        }
        return false;
    }

    public function Render(\UserData &$userData, \JamData &$jamData, \PlatformData &$platformData, \PlatformGameData &$platformGameData, $renderDepth){
        $render = Array();
        $render["entries"] = GamePresenter::RenderGames($userData, $this->$GameData, $jamData, $platformData, $platformGameData, $renderDepth);
        return $render;
    }
    
	public function RenderEntry($entryId, &$userData, &$jamData, &$platformData, &$platformGameData, $renderDepth){
		return GamePresenter::RenderGame($userData, $this->GameData->GameModels[$entryId], $jamData, $platformData, $platformGameData, $renderDepth);
	}
}

?>