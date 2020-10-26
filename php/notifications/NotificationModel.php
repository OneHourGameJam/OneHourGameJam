<?php
namespace Plugins\Notification;

class NotificationModel{
	public $Id;
	public $Title;
	public $Text;
	public $IconImageUrl;
	public $IconLinkUrl;
	public $StartDateTime;
	public $EndDateTime;
}

class NotificationData{
    public $NotificationModels;

    private $notificationDbInterface;

    function __construct(&$notificationDbInterface) {
        $this->notificationDbInterface = $notificationDbInterface;
        $this->NotificationModels = $this->LoadNotifications();
    }

//////////////////////// MODEL CONSTRUCTOR

    function LoadNotifications(){
        AddActionLog("LoadNotifications");
        StartTimer("LoadNotifications");

        $data = $this->notificationDbInterface->SelectAll();

        $notificationModels = Array();
        while($info = mysqli_fetch_array($data)){
            $notificationModel = new NotificationModel();

            $notificationId = intval($info[DB_COLUMN_NOTIFICAITON_ID]);
            $notificationModel->Id = $notificationId;
            $notificationModel->Title = $info[DB_COLUMN_NOTIFICAITON_TITLE];
            $notificationModel->Text = $info[DB_COLUMN_NOTIFICAITON_TEXT];
            $notificationModel->IconImageUrl = $info[DB_COLUMN_NOTIFICATION_ICON_IMAGE_URL];
            $notificationModel->IconLinkUrl = $info[DB_COLUMN_NOTIFICATION_ICON_LINK_URL];
            $notificationModel->StartDateTime = $info[DB_COLUMN_NOTIFICAITON_START_DATETIME];
            $notificationModel->EndDateTime = $info[DB_COLUMN_NOTIFICAITON_END_DATETIME];

            $notificationModels[$notificationId] = $notificationModel;
        }

        StopTimer("LoadNotifications");
        return $notificationModels;
    }

//////////////////////// END MODEL CONSTRUCTOR
    
//////////////////////// DATABASE ACTIONS (select, insert, update)

    function AddNotification($title, $text, $iconUrl, $startDatetime, $endDatetime){
        global $ip, $userAgent;
        AddActionLog("AddNotification");
        StartTimer("AddNotification");

        $this->notificationDbInterface->Insert($title, $text, $iconUrl, $startDatetime, $endDatetime);

        StopTimer("AddNotification");
    }

//////////////////////// END DATABASE ACTIONS

//////////////////////// PUBLIC DATA EXPORT

    function GetAllPublicData(){
        AddActionLog("NotificationData_GetAllPublicData");
        StartTimer("NotificationData_GetAllPublicData");
        
        $dataFromDatabase = MySQLDataToArray($this->notificationDbInterface->SelectPublicData());

        foreach($dataFromDatabase as $i => $row){
            $dataFromDatabase[$i][DB_COLUMN_NOTIFICAITON_IP] = OVERRIDE_MIGRATION;
            $dataFromDatabase[$i][DB_COLUMN_NOTIFICAITON_USER_AGENT] = OVERRIDE_MIGRATION;
            $dataFromDatabase[$i][DB_COLUMN_NOTIFICAITON_USER_ID] = OVERRIDE_LEGACY_NUM;
        }

        StopTimer("NotificationData_GetAllPublicData");
        return $dataFromDatabase;
    }

//////////////////////// END PUBLIC DATA EXPORT
}

?>