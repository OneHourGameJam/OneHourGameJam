<?php
namespace Plugins\Notification;

class NotificationPresenter{
	
	public static function RenderNotifications(&$notificationData){
		AddActionLog("RenderNotifications");
		StartTimer("RenderNotifications");
		$notificationsViewModel = new NotificationsViewModel();

		foreach($notificationData->NotificationModels as $i => $notificationModel){
			$notificationId = $notificationModel->Id;
			$notificationsViewModel->notifications[] = NotificationPresenter::RenderNotification($notificationData, $notificationId);
		}

		StopTimer("RenderNotifications");
		return $notificationsViewModel;
	}
	
	public static function RenderNotification(&$notificationData, $notificationId){
		AddActionLog("RenderNotification");
		StartTimer("RenderNotification");

		$notificationModel = $notificationData->NotificationModels[$notificationId];

		$notificationViewModel = new NotificationViewModel();

		$startDatetime = strtotime($notificationModel->StartDateTime." UTC");
		$endDatetime = strtotime($notificationModel->EndDateTime." UTC");

		$notificationViewModel->id = $notificationModel->Id;
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

		$notificationViewModel->visible = ( (time() >= $startDatetime) && (time() <= $endDatetime) ) ? 1 : 0;

		StopTimer("RenderNotification");
		return $notificationViewModel;
	}
}

?>