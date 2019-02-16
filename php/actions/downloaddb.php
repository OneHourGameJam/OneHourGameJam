<?php

if(IsAdmin($loggedInUser) !== false){
    print GetJSONDataForAllTables();
    die();
}

?>