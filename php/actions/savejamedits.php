<?php

if(IsAdmin()){
    $jamNumber = intval($_POST["jam_number"]);
    $theme = $_POST["theme"];
    $date = $_POST["date"];
    $time = $_POST["time"];
    $jamcolors = $_POST["jamcolors"];
    
    EditJam($jamNumber, $theme, $date, $time, $jamcolors);
}
$page = "main"; 

?>