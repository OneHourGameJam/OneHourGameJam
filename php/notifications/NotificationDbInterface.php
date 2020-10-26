<?php
namespace Plugins\Notification;

define("DB_TABLE_NOTIFICATION", "notification");
define("DB_COLUMN_NOTIFICAITON_ID", "notification_id");
define("DB_COLUMN_NOTIFICAITON_IP", "notification_ip");
define("DB_COLUMN_NOTIFICAITON_USER_AGENT", "notification_user_agent");
define("DB_COLUMN_NOTIFICAITON_USER_ID", "notification_user_id");
define("DB_COLUMN_NOTIFICAITON_TITLE", "notification_title");
define("DB_COLUMN_NOTIFICAITON_TEXT", "notification_text");
define("DB_COLUMN_NOTIFICATION_ICON_IMAGE_URL", "notification_icon_image_url");
define("DB_COLUMN_NOTIFICATION_ICON_LINK_URL", "notification_icon_link_url");
define("DB_COLUMN_NOTIFICAITON_START_DATETIME", "notification_start_datetime");
define("DB_COLUMN_NOTIFICAITON_END_DATETIME", "notification_end_datetime");

class NotificationDbInterface{
    private $database;
    private $publicColumns = Array(DB_COLUMN_NOTIFICAITON_ID, DB_COLUMN_NOTIFICAITON_TITLE, DB_COLUMN_NOTIFICAITON_TEXT, DB_COLUMN_NOTIFICATION_ICON_IMAGE_URL, DB_COLUMN_NOTIFICATION_ICON_LINK_URL, DB_COLUMN_NOTIFICAITON_START_DATETIME, DB_COLUMN_NOTIFICAITON_END_DATETIME);
    private $privateColumns = Array(DB_COLUMN_NOTIFICAITON_IP, DB_COLUMN_NOTIFICAITON_USER_AGENT, DB_COLUMN_NOTIFICAITON_USER_ID);

    function __construct(&$database) {
        $this->database = $database;
    }

    public function Insert($ip, $userAgent, $userId, $title, $text, $iconImageUrl, $iconLinkUrl, $startDatetime, $endDatetime){
        AddActionLog("NotificationDbInterface_Insert");
        StartTimer("NotificationDbInterface_Insert");

        $escapedIp = $this->database->EscapeString($ip);
        $escapedUserAgent = $this->database->EscapeString($userAgent);
        $escapedUserId = $this->database->EscapeString($userId);
        $escapedTitle = $this->database->EscapeString($title);
        $escapedText = $this->database->EscapeString($text);
        $escapedIconImageUrl = $this->database->EscapeString($iconImageUrl);
        $escapedIconLinkUrl = $this->database->EscapeString($iconLinkUrl);
        $escapedStartDatetime = $this->database->EscapeString($startDatetime);
        $escapedEndDatetime = $this->database->EscapeString($endDatetime);
    
        $sql = "
            INSERT INTO ".DB_TABLE_NOTIFICATION."
                (".DB_COLUMN_NOTIFICAITON_ID.",
                ".DB_COLUMN_NOTIFICAITON_IP.",
                ".DB_COLUMN_NOTIFICAITON_USER_AGENT.",
                ".DB_COLUMN_NOTIFICAITON_USER_ID.",
                ".DB_COLUMN_NOTIFICAITON_TITLE.",
                ".DB_COLUMN_NOTIFICAITON_TEXT.",
                ".DB_COLUMN_NOTIFICATION_ICON_IMAGE_URL.",
                ".DB_COLUMN_NOTIFICATION_ICON_LINK_URL.",
                ".DB_COLUMN_NOTIFICAITON_START_DATETIME.",
                ".DB_COLUMN_NOTIFICAITON_END_DATETIME.")
                VALUES
                (null,
                '".$escapedIp."',
                '".$escapedUserAgent."',
                ".$escapedUserId.",
                '".$escapedTitle."',
                '".$escapedText."',
                '".$escapedIconImageUrl."',
                '".$escapedIconLinkUrl."',
                '".$escapedStartDatetime."',
                '".$escapedEndDatetime."'
            );";

        $data = $this->database->Execute($sql);
        $sql = "";

        StopTimer("NotificationDbInterface_Insert");
    }

    public function Update($id, $ip, $userAgent, $userId, $title, $text, $iconImageUrl, $iconLinkUrl, $startDatetime, $endDatetime){
        AddActionLog("NotificationDbInterface_Update");
        StartTimer("NotificationDbInterface_Update");

        $escapedId = $this->database->EscapeString($id);
        $escapedIp = $this->database->EscapeString($ip);
        $escapedUserAgent = $this->database->EscapeString($userAgent);
        $escapedUserId = $this->database->EscapeString($userId);
        $escapedTitle = $this->database->EscapeString($title);
        $escapedText = $this->database->EscapeString($text);
        $escapedIconImageUrl = $this->database->EscapeString($iconImageUrl);
        $escapedIconLinkUrl = $this->database->EscapeString($iconLinkUrl);
        $escapedStartDatetime = $this->database->EscapeString($startDatetime);
        $escapedEndDatetime = $this->database->EscapeString($endDatetime);
    
        $sql = "
            UPDATE notification
            SET  
                ".DB_COLUMN_NOTIFICAITON_IP." = '$escapedIp', 
                ".DB_COLUMN_NOTIFICAITON_USER_AGENT." = '$escapedUserAgent', 
                ".DB_COLUMN_NOTIFICAITON_USER_ID." = '$escapedUserId', 
                ".DB_COLUMN_NOTIFICAITON_TITLE." = '$escapedTitle', 
                ".DB_COLUMN_NOTIFICAITON_TEXT." = '$escapedText', 
                ".DB_COLUMN_NOTIFICATION_ICON_IMAGE_URL." = '$escapedIconImageUrl', 
                ".DB_COLUMN_NOTIFICATION_ICON_LINK_URL." = '$escapedIconLinkUrl', 
                ".DB_COLUMN_NOTIFICAITON_START_DATETIME." = '$escapedStartDatetime', 
                ".DB_COLUMN_NOTIFICAITON_END_DATETIME." = '$escapedEndDatetime' 
            WHERE 
                ".DB_COLUMN_NOTIFICAITON_ID." = $escapedId;
            ";

        $data = $this->database->Execute($sql);
        $sql = "";

        StopTimer("NotificationDbInterface_Update");
    }
    
    public function SelectAll(){
        AddActionLog("NotificationDbInterface_SelectAll");
        StartTimer("NotificationDbInterface_SelectAll");

        $sql = "
            SELECT ".DB_COLUMN_NOTIFICAITON_ID.", ".DB_COLUMN_NOTIFICAITON_TITLE.", ".DB_COLUMN_NOTIFICAITON_TEXT.", ".DB_COLUMN_NOTIFICATION_ICON_IMAGE_URL.", ".DB_COLUMN_NOTIFICATION_ICON_LINK_URL.", ".DB_COLUMN_NOTIFICAITON_START_DATETIME.", ".DB_COLUMN_NOTIFICAITON_END_DATETIME."
            FROM ".DB_TABLE_NOTIFICATION."
            ORDER BY ".DB_COLUMN_NOTIFICAITON_ID." DESC";

        StopTimer("NotificationDbInterface_SelectAll");
        return $this->database->Execute($sql);;
    }

    public function SelectPublicData(){
        AddActionLog("NotificationDbInterface_SelectPublicData");
        StartTimer("NotificationDbInterface_SelectPublicData");

        $sql = "
            SELECT ".implode(",", $this->publicColumns)."
            FROM ".DB_TABLE_NOTIFICATION.";
        ";

        StopTimer("NotificationDbInterface_SelectPublicData");
        return $this->database->Execute($sql);;
    }
}

?>