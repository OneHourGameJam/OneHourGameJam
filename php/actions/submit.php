<?php

if(IsAdmin()){
    $gameName = (isset($_POST["gamename"])) ? $_POST["gamename"] : "";
    $gameURL = (isset($_POST["gameurl"])) ? $_POST["gameurl"] : "";
    $gameURLWeb = (isset($_POST["gameurlweb"])) ? $_POST["gameurlweb"] : "";
    $gameURLWin = (isset($_POST["gameurlwin"])) ? $_POST["gameurlwin"] : "";
    $gameURLMac = (isset($_POST["gameurlmac"])) ? $_POST["gameurlmac"] : "";
    $gameURLLinux = (isset($_POST["gameurllinux"])) ? $_POST["gameurllinux"] : "";
    $gameURLiOS = (isset($_POST["gameurlios"])) ? $_POST["gameurlios"] : "";
    $gameURLAndroid = (isset($_POST["gameurlandroid"])) ? $_POST["gameurlandroid"] : "";
    $gameURLSource = (isset($_POST["gameurlsource"])) ? $_POST["gameurlsource"] : "";
    $screenshotURL = (isset($_POST["screenshoturl"])) ? $_POST["screenshoturl"] : "";
    $description = (isset($_POST["description"])) ? $_POST["description"] : "";
    $jamNumber = (isset($_POST["jam_number"])) ? intval($_POST["jam_number"]) : -1;
    $jamColorNumber = (isset($_POST["colorNumber"])) ? intval($_POST["colorNumber"]) : 0;
    
    SubmitEntry($jamNumber, $gameName, $gameURL, $gameURLWeb, $gameURLWin, $gameURLMac, $gameURLLinux, $gameURLiOS, $gameURLAndroid, $gameURLSource, $screenshotURL, $description, $jamColorNumber);
    
    $satisfaction = (isset($_POST["satisfaction"])) ? intval($_POST["satisfaction"]) : 0;
    if($satisfaction != 0){
        SubmitSatisfaction("JAM_$jamNumber", $satisfaction);
    }
}

?>