<?php


function PerformAction(&$loggedInUser){
    global $adminLogData;
    
    if(IsAdmin($loggedInUser) !== false){
        $adminLogData->AddToAdminLog("DOWNLOAD_DB", "Downloaded the Database", "NULL", $loggedInUser->Id, "");
        //print GetJSONDataForAllTables();
        die();
    }
}

?>