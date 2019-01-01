<?php

if(IsLoggedIn()){
    $displayName = $_POST["displayname"];
    $twitterHandle = $_POST["twitterhandle"];
    $emailAddress = $_POST["emailaddress"];
    $bio = $_POST["bio"];
    
    ChangeUserData($displayName, $twitterHandle, $emailAddress, $bio);
}
$page = "usersettings";

?>