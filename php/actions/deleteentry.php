<?php

if(IsAdmin()){
    $entryID = (isset($_POST["entryID"])) ? $_POST["entryID"] : "";
    if($entryID != ""){
        DeleteEntry(intval($entryID));
        $page = "editcontent";
    }
}

?>