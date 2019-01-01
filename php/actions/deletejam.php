<?php

if(IsAdmin()){
    $jamID = (isset($_POST["jamID"])) ? $_POST["jamID"] : "";
    if($jamID != ""){
        DeleteJam(intval($jamID));
        $page = "editcontent";
    }
}

?>