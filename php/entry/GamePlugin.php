<?php
//namespace Plugins\Entry;

define("PAGE_EDIT_ENTRY", "editentry");
define("PAGE_SUBMIT", "submit");
define("PAGE_ENTRIES", "entries");
define("RENDER_GAMES", "RenderGames");

class GamePlugin extends \AbstractPlugin{
    public $NameInTemplate = "entry";

    public $AdminLogDbInterface;
    public $AdminLogData;

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
	    //$this->AdminLogDbInterface = new AdminLogDbInterface($database);
    }

    public function RetrieveData(){
        //$this->AdminLogData = new AdminLogData($this->AdminLogDbInterface);
    }

    public function GetUserData($userId){
        $userData = Array();
        //$userData["Admin Log (when admin)"] = $this->AdminLogData->GetAdminLogForAdmin($userId);
        //$userData["Admin Log (when subject)"] = $this->AdminLogData->GetAdminLogForSubject($userId);
        return  $userData;
    }

    public function ShouldBeRendered(&$dependencies){
        if(FindDependency(RENDER_GAMES, $dependencies) !== false){
            return true;
        }
        return false;
    }

    public function Render(\IUserDisplay &$userData){
        $render = Array();
        //$render["adminlog"] = AdminLogPresenter::RenderAdminLog($this->AdminLogData, $userData);
        return $render;
    }
}

?>