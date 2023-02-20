<?php
namespace Plugins\Notification;

define("PAGE_EDIT_NOTIFICATIONS", "editnotifications");
define("PAGE_EDIT_NOTIFICATION", "editnotification");

define("RENDER_NOTIFICATIONS", "RenderNotifications");

define("GET_EDITNOTIFICATION_NOTIFICATION_ID", "notification_id");

define("FORM_EDITNOTIFICATION_ID", "notification_id");
define("FORM_EDITNOTIFICATION_TITLE", "notification_title");
define("FORM_EDITNOTIFICATION_TEXT", "notification_text");
define("FORM_EDITNOTIFICATION_ICON_IMAGE_URL", "notification_icon_image_url");
define("FORM_EDITNOTIFICATION_ICON_LINK_URL", "notification_icon_link_url");
define("FORM_EDITNOTIFICATION_START_DATE", "notification_start_date");
define("FORM_EDITNOTIFICATION_START_TIME", "notification_start_time");
define("FORM_EDITNOTIFICATION_END_DATE", "notification_end_date");
define("FORM_EDITNOTIFICATION_END_TIME", "notification_end_time");

define("ACTION_EDIT_NOTIFICATION", "editnotification");
define("ACTION_DELETE_NOTIFICATION", "deletenotification");

class NotificationPlugin extends \AbstractPlugin{
    public $NameInTemplate = "notifications";

    public $NotificationDbInterface;
    public $NotificationData;

    public function ReceiveMessage(\AbstractMessage &$message){}

    public function GetActionsFolder(){
        return __DIR__."/actions/";
    }

    public function GetTemplateFolder(){
        return __DIR__."/template/";
    }

    public function GetPartialsFolder(){
        return __DIR__."/partial/";
    }

    public function PageSettings(){
        return Array(
            PAGE_EDIT_NOTIFICATIONS => Array(
                "page_title" => "Manage Notifications",
                "authorization_level" => AUTHORIZATION_LEVEL_ADMIN,
                "template_file" => $this->GetTemplateFolder()."editnotifications.mustache",
                "dependencies" => Array(RENDER_NOTIFICATIONS => RENDER_DEPTH_NONE),
            ),
            PAGE_EDIT_NOTIFICATION => Array(
                "page_title" => "Edit Notification",
                "authorization_level" => AUTHORIZATION_LEVEL_ADMIN,
                "template_file" => $this->GetTemplateFolder()."editnotification.mustache",
                "dependencies" => Array(),
            )
        );
    }
    
    public function GetPartials(){
        $partials = Array(
            "notification" => $this->GetPartialsFolder()."notification.mustache"
        );
        return $partials;
    }

    public function CommonDependencies(){
        return Array(
            "header" => Array(RENDER_NOTIFICATIONS => RENDER_DEPTH_NONE)
        );
    }

    public function FormSettings(){
        return Array(
            "get" => Array(PAGE_EDIT_NOTIFICATION => GET_EDITNOTIFICATION_NOTIFICATION_ID),
            "form" => Array(
                "editnotification" => Array(
                    "id" => FORM_EDITNOTIFICATION_ID, 
                    "title" => FORM_EDITNOTIFICATION_TITLE, 
                    "text" => FORM_EDITNOTIFICATION_TEXT, 
                    "icon" => Array(
                        "image_url" => FORM_EDITNOTIFICATION_ICON_IMAGE_URL, 
                        "link_url" => FORM_EDITNOTIFICATION_ICON_LINK_URL), 
                    "start" => Array(
                        "date" => FORM_EDITNOTIFICATION_START_DATE,
                        "time" => FORM_EDITNOTIFICATION_START_TIME),
                    "end" => Array(
                        "date" => FORM_EDITNOTIFICATION_END_DATE,
                        "time" => FORM_EDITNOTIFICATION_END_TIME)
                )
            ),
            "action" => Array(
                "editnotification" => ACTION_EDIT_NOTIFICATION, 
                "deletenotification" => ACTION_DELETE_NOTIFICATION),
            "constant" => Array(),
            "preference" => Array()
        );
    }

    public function EstablishDatabaseConnection(){
        $database = new \Database();
	    $this->NotificationDbInterface = new NotificationDbInterface($database);
    }

    public function RetrieveData(){
        $this->NotificationData = new NotificationData($this->NotificationDbInterface);
    }

    public function GetUserDataExport($userId){
        $userData = Array();
        $userData["Notifications"] = $this->NotificationData->GetNotificationsByUser($userId);
        return  $userData;
    }

    public function ShouldBeRendered(&$dependencies){
        if(FindDependency(RENDER_NOTIFICATIONS, $dependencies) !== false){
            return true;
        }
        return false;
    }

    public function Render($page, \IUserDisplay &$userData){
        $render = Array();
        $render["notifications"] = NotificationPresenter::RenderNotifications($this->NotificationData);

        switch($page){
            case PAGE_EDIT_NOTIFICATION:
                if(isset($_GET[GET_EDITNOTIFICATION_NOTIFICATION_ID])){
                    $notificationId = intval($_GET[GET_EDITNOTIFICATION_NOTIFICATION_ID]);
                    $render["page"]["editingnotification"] = NotificationPresenter::RenderNotification($this->NotificationData, $notificationId, Array());
                }
                break;
        }

        return $render;
    }

    public function GetSiteActionSettings(){
        $actions = Array(
            new \SiteActionModel(
                ACTION_EDIT_NOTIFICATION,
                $this->GetActionsFolder()."savenotification.php",
                "?".GET_PAGE."=".PAGE_MAIN,
                Array(
                    "SUCESS_UPDATE" => new \SiteActionResultModel("?".GET_PAGE."=".PAGE_EDIT_NOTIFICATIONS, MESSAGE_SUCCESS, "Notification updated."),
                    "SUCESS_INSERT" => new \SiteActionResultModel("?".GET_PAGE."=".PAGE_EDIT_NOTIFICATIONS, MESSAGE_SUCCESS, "Notification added."),
                    "MISSING_TITLE" => new \SiteActionResultModel("?".GET_PAGE."=".PAGE_EDIT_NOTIFICATIONS, MESSAGE_WARNING, "No notification title provided."),
                    "MISSING_TEXT" => new \SiteActionResultModel("?".GET_PAGE."=".PAGE_EDIT_NOTIFICATIONS, MESSAGE_WARNING, "No notification text provided."),
                    "ICON_LINK_WITH_NO_IMAGE" => new \SiteActionResultModel("?".GET_PAGE."=".PAGE_EDIT_NOTIFICATIONS, MESSAGE_WARNING, "Icon link url provided, but no icon image url."),
                    "END_TIME_INVALID" => new \SiteActionResultModel("?".GET_PAGE."=".PAGE_EDIT_NOTIFICATIONS, MESSAGE_WARNING, "End time is invalid"),
                    "END_TIME_BEFORE_START_TIME" => new \SiteActionResultModel("?".GET_PAGE."=".PAGE_EDIT_NOTIFICATIONS, MESSAGE_WARNING, "End time is before start time."),
                    "START_TIME_INVALID" => new \SiteActionResultModel("?".GET_PAGE."=".PAGE_EDIT_NOTIFICATIONS, MESSAGE_WARNING, "Start time is invalid."),
                    "END_TIME_IN_THE_PAST" => new \SiteActionResultModel("?".GET_PAGE."=".PAGE_EDIT_NOTIFICATIONS, MESSAGE_WARNING, "Can't schedule/edit a notification from the past."),
                    "NOTIFICATION_ID_NOT_FOUND" => new \SiteActionResultModel("?".GET_PAGE."=".PAGE_EDIT_NOTIFICATIONS, MESSAGE_WARNING, "Notification with that id was not found"),
                    "NOT_AUTHORIZED" => new \SiteActionResultModel("?".GET_PAGE."=".PAGE_MAIN, MESSAGE_ERROR, "Only admins can perform this action."),
                    "NOT_LOGGED_IN" => new \SiteActionResultModel("?".GET_PAGE."=".PAGE_LOGIN, MESSAGE_WARNING, "Not logged in."),
                )
            ),
            new \SiteActionModel(
                ACTION_DELETE_NOTIFICATION,
                $this->GetActionsFolder()."deletenotification.php",
                "?".GET_PAGE."=".PAGE_MAIN,
                Array(
                    "SUCESS" => new \SiteActionResultModel("?".GET_PAGE."=".PAGE_EDIT_NOTIFICATIONS, MESSAGE_SUCCESS, "Notification deleted."),
                    "UNKNOWN_NOTIFICATION" => new \SiteActionResultModel("?".GET_PAGE."=".PAGE_EDIT_NOTIFICATIONS, MESSAGE_WARNING, "Notification does not exist."),
                    "NOT_AUTHORIZED" => new \SiteActionResultModel("?".GET_PAGE."=".PAGE_MAIN, MESSAGE_ERROR, "Only admins can perform this action."),
                    "NOT_LOGGED_IN" => new \SiteActionResultModel("?".GET_PAGE."=".PAGE_LOGIN, MESSAGE_WARNING, "Not logged in."),
                )
            )
        );

        return $actions;
    }

}

?>