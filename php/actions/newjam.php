<?php

if(IsAdmin()){
    $theme = (isset($_POST["theme"])) ? $_POST["theme"] : "";
    $date = (isset($_POST["date"])) ? $_POST["date"] : "";
    $time = (isset($_POST["time"])) ? $_POST["time"] : "";
    $jamColors = Array();
    for($colorIndex = 0; $colorIndex < 16; $colorIndex++){
        if(isset($_POST["jamcolor".$colorIndex])){
            $jamColors[] = $_POST["jamcolor".$colorIndex];
        }
    }
    if(count($jamColors) == 0){
        $jamColors = Array("FFFFFF");
    }
    
    CreateJam($theme, $date, $time, $jamColors);
}

?>