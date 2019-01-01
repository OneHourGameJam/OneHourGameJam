<?php

if(IsAdmin()){
    $bannedTheme = $_POST["theme"];
    BanTheme($bannedTheme);
}

?>