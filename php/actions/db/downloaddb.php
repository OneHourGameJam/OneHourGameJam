<?php


function PerformAction(&$loggedInUser){
    global $_POST, $adminLogData;
    
    if(IsAdmin($loggedInUser) !== false){
        $adminLogData->AddToAdminLog("DOWNLOAD_DB", "Downloaded the Database", "", $loggedInUser->Username);
        print GetJSONDataForAllTables();
        die();
    }
}

?>