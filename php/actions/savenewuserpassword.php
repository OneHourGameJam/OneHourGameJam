<?php

if(IsAdmin()){
    $username = $_POST["username"];
    $password1 = $_POST["password1"];
    $password2 = $_POST["password2"];
    
    EditUserPassword($username, $password1, $password2);
}
$page = "editusers";

?>