<?php

if(IsAdmin()){
    $username = $_POST["username"];
    $isAdmin = (isset($_POST["isadmin"])) ? intval($_POST["isadmin"]) : 0;
    if($isAdmin != 0 && $isAdmin != 1){
        die("invalid isadmin value");
    }
    
    EditUser($username, $isAdmin);
}
$page = "editusers";

?>