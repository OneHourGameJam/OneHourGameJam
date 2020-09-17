<?php
namespace Plugins\AdminLog;

include_once("php/adminlog/AdminLogDbInterface.php");
include_once("php/adminlog/AdminLogModel.php");
include_once("php/adminlog/AdminLogViewModel.php");
include_once("php/adminlog/AdminLogPresenter.php");

define("PAGE_ADMIN_LOG", "adminlog");
define("RENDER_ADMIN_LOG", "RenderAdminLog");

class AdminLogPlugin extends \AbstractPlugin{
    public $NameInTemplate = "admin_log";

    private $AdminLogDbInterface;
    private $AdminLogData;

    public function ReceiveMessage(\AbstractMessage &$message){
        if($message instanceof LogMessage){
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
            PAGE_ADMIN_LOG => Array(
                "page_title" => "Admin Log",
                "authorization_level" => AUTHORIZATION_LEVEL_ADMIN,
                "template_file" => "adminlog.html",
                "dependencies" => Array(RENDER_ADMIN_LOG => RENDER_DEPTH_NONE),
            )
        );
    }

    public function EstablishDatabaseConnection(){
        $database = new \Database();
	    $this->AdminLogDbInterface = new AdminLogDbInterface($database);
    }

    public function RetrieveData(){
        $this->AdminLogData = new AdminLogData($this->AdminLogDbInterface);
    }

    public function GetUserData($userId){
        $userData = Array();
        $userData["Admin Log (when admin)"] = $this->AdminLogData->GetAdminLogForAdmin($userId);
        $userData["Admin Log (when subject)"] = $this->AdminLogData->GetAdminLogForSubject($userId);
        return  $userData;
    }

    public function ShouldBeRendered(&$dependencies){
        if(FindDependency(RENDER_ADMIN_LOG, $dependencies) !== false){
            return true;
        }
        return false;
    }

    public function Render(\IUserDisplay &$userData){
        $render = Array();
        $render["adminlog"] = AdminLogPresenter::RenderAdminLog($this->AdminLogData, $userData);
        return $render;
    }
}

?>