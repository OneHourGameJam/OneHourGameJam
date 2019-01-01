<?php

if(IsLoggedIn()){
    $newTheme = $_POST["theme"];
    AddTheme($newTheme, false);
}

?>