<?php
namespace Plugins\AdminLog;

define("PAGE_ADMIN_LOG", "adminlog");
define("RENDER_ADMIN_LOG", "RenderAdminLog");

class AdminLogPlugin extends \AbstractPlugin{
    public $NameInTemplate = "admin_log";

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

    public function GetTemplateFolder(){
        return "php/adminlog/template/";
    }

    public function GetPartialsFolder(){
        return "php/adminlog/partial/";
    }


    public function PageSettings(){
        return Array(
            PAGE_ADMIN_LOG => Array(
                "page_title" => "Admin Log",
                "authorization_level" => AUTHORIZATION_LEVEL_ADMIN,
                "template_file" => $this->GetTemplateFolder()."adminlog.html",
                "dependencies" => Array(RENDER_ADMIN_LOG => RENDER_DEPTH_NONE),
            )
        );
    }
    
    public function GetPartials(){
        $partials = Array();
        return $partials;
    }

    public function CommonDependencies(){
        return Array();
    }

    public function FormSettings(){
        return Array(
            "get" => Array(),
            "form" => Array(),
            "action" => Array(),
            "constant" => Array(),
            "preference" => Array()
        );
    }

    public function EstablishDatabaseConnection(){
        $database = new \Database();
	    $this->AdminLogDbInterface = new AdminLogDbInterface($database);
    }

    public function RetrieveData(){
        $this->AdminLogData = new AdminLogData($this->AdminLogDbInterface);
    }

    public function GetUserDataExport($userId){
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

    public function Render($page, \IUserDisplay &$userData){
        $render = Array();
        $render["adminlog"] = AdminLogPresenter::RenderAdminLog($this->AdminLogData, $userData);
        return $render;
    }

    public function GetSiteActionSettings(){
        $actions = Array();
        return $actions;
    }
}

?>