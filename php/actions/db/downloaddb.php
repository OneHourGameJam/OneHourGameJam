<?php


function PerformAction(MessageService &$messageService, &$loggedInUser){
	global $userData;
	
    if(IsAdmin($loggedInUser) !== false){
		$messageService->SendMessage(LogMessage::UserLogMessage(
			"DOWNLOAD_DB", 
			"Downloaded the Database", 
			$loggedInUser->Id)
		);
		$userData->LogAdminAction($loggedInUser->Id);
        //print GetJSONDataForAllTables();
        die();
    }
}

?>