<?php

if(IsAdmin()){
    $deletedTheme = $_POST["theme"];
    RemoveTheme($deletedTheme);
}

?>