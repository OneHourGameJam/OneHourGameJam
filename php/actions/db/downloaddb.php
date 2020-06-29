<?php


function PerformAction(&$loggedInUser){
    global $_POST, $adminLogData;
    
    if(IsAdmin($loggedInUser) !== false){
        $adminLogData->AddToAdminLog("DOWNLOAD_DB", "Downloaded the Database", "NULL", $loggedInUser->Id, "");
        print GetJSONDataForAllTables();
        die();
    }
}

?>