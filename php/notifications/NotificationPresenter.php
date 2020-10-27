<?php
namespace Plugins\Notification;

class NotificationPresenter{
	
	public static function RenderNotifications(&$notificationData){
		AddActionLog("RenderNotifications");
		StartTimer("RenderNotifications");
		$notificationsViewModel = new NotificationsViewModel();

		$closedNotifications = Array();
		if(isset($_COOKIE[COOKIE_CLOSED_NOTIFICATIONS])){
			$closedNotificationsJson = $_COOKIE[COOKIE_CLOSED_NOTIFICATIONS];
			$closedNotifications = json_decode($closedNotificationsJson, true);
		};

		foreach($notificationData->NotificationModels as $i => $notificationModel){
			$notificationId = $notificationModel->Id;
			$notificationsViewModel->notifications[] = NotificationPresenter::RenderNotification($notificationData, $notificationId, $closedNotifications);
		}

		StopTimer("RenderNotifications");
		return $notificationsViewModel;
	}
	
	public static function RenderNotification(&$notificationData, $notificationId, $closedNotifications){
		AddActionLog("RenderNotification");
		StartTimer("RenderNotification");

		$notificationModel = $notificationData->NotificationModels[$notificationId];

		$notificationViewModel = new NotificationViewModel();

		$startDatetime = strtotime($notificationModel->StartDateTime." UTC");
		$endDatetime = strtotime($notificationModel->EndDateTime." UTC");

		$notificationViewModel->id = $notificationId;
		$notificationViewModel->title = $notificationModel->Title;
		$notificationViewModel->text = $notificationModel->Text;
		$notificationViewModel->icon_image_url = $notificationModel->IconImageUrl;
		$notificationViewModel->icon_link_url = $notificationModel->IconLinkUrl;
		$notificationViewModel->start_datetime = gmdate("l j F Y G:i:s", $startDatetime) ." UTC";
		$notificationViewModel->end_datetime = gmdate("l j F Y G:i:s", $endDatetime) ." UTC";
		
		$notificationViewModel->start_date_html_format = gmdate("Y-m-d", $startDatetime);
		$notificationViewModel->start_time_html_format = gmdate("H:i", $startDatetime);
		$notificationViewModel->end_date_html_format = gmdate("Y-m-d", $endDatetime);
		$notificationViewModel->end_time_html_format = gmdate("H:i", $endDatetime);

		if(array_search($notificationId, $closedNotifications) === false){
			$notificationViewModel->minimised = 0;
		}else{
			$notificationViewModel->minimised = 1;
		}

		$notificationViewModel->visible = ( (time() >= $startDatetime) && (time() <= $endDatetime) ) ? 1 : 0;

		StopTimer("RenderNotification");
		return $notificationViewModel;
	}
}

?>