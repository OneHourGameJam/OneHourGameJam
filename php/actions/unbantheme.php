<?php

if(IsAdmin()){
    $unbannedTheme = $_POST["theme"];
    UnbanTheme($unbannedTheme);
}

?>