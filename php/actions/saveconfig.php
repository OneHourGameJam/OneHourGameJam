<?php

if(IsAdmin()){
    foreach($_POST as $key => $value){
        SaveConfig($key, $value);
    }
    LoadConfig(); //reload config
}

?>