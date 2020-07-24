<?php

class MessagePresenter{
	public static function RenderMessages(&$messageData){
		$messagesViewModel = new MessagesViewModel();
		AddActionLog("RenderMessages");
		StartTimer("RenderMessages");

		$messagesViewModel->LIST = Array();
		foreach($messageData->MessageModels as $i => $messageModel){
			$messageViewModel = new MessageViewModel();
			
			$messageViewModel->message_type = trim($messageModel->Type);
			$messageViewModel->message_title = trim($messageModel->Title);
			$messageViewModel->message_body = trim($messageModel->Body);

			$messagesViewModel->LIST[] = $messageViewModel;
		}

		StopTimer("RenderMessages");
		return $messagesViewModel;
	}
}

?>