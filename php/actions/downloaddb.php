<?php

if(IsAdmin()){
    print GetJSONDataForAllTables();
    die();
}

?>