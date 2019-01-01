<?php

if(IsLoggedIn()){
    $passwordold = $_POST["passwordold"];
    $password1 = $_POST["password1"];
    $password2 = $_POST["password2"];
    
    ChangePassword($passwordold, $password1, $password2);
}
$page = "usersettings";

?>