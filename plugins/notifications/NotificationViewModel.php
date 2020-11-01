<?php
namespace Plugins\Notification;

class NotificationsViewModel{
	public $notifications = Array();
}

class NotificationViewModel{
    public $id;
    public $visible;
    public $title;
    public $text;
    public $icon_image_url;
    public $icon_link_url;
    public $start_datetime;
    public $end_datetime;
    public $start_date_html_format;
    public $start_time_html_format;
    public $end_date_html_format;
    public $end_time_html_format;
    public $minimised;
}

?>