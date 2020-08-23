<?php


function PerformAction(MessageService &$messageService, &$loggedInUser){
    
    if(IsAdmin($loggedInUser) !== false){
		$messageService->SendMessage(LogMessage::UserLogMessage(
			"DOWNLOAD_DB", 
			"Downloaded the Database", 
			$loggedInUser->Id)
		);
        //print GetJSONDataForAllTables();
        die();
    }
}

?>