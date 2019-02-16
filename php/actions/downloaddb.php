<?php

if(IsAdmin($loggedInUser) !== false){
    AddToAdminLog("DOWNLOAD_DB", "Downloaded the Database", "", $loggedInUser["username"]);
    print GetJSONDataForAllTables();
    die();
}

?>