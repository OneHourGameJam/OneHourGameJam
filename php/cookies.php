<?php

function UpdateCookies(){
    global $_COOKIE, $_GET;
	AddActionLog("UpdateCookies");
	StartTimer("UpdateCookies");

    //TODO: This section should be removed after 31 March 2019 while as it's just transition code to get from the cookie being named nightmode to it being named darkmode
    if(isset($_COOKIE["nightmode"])){
        setcookie("darkmode", 1, time() + (60 * 60 * 24 * 365));
        setcookie("nightmode", null, -1);
        $_COOKIE["darkmode"] = 1;
    }
    //TODO: End removal here
    
    //Determine if the user is in dark mode
    if(isset($_GET["darkmode"])){
        if($_GET["darkmode"]){
            setcookie("darkmode", 1, time() + (60 * 60 * 24 * 365));
            $_COOKIE["darkmode"] = 1;
        }else{
            setcookie("darkmode", null, -1);
            unset($_COOKIE["darkmode"]);
        }
    }

    //Update streaming cookie
    if(isset($_GET["streaming"])){
        if($_GET["streaming"] == 1){
            setcookie("streaming", 1, time() + (60 * 60 * 3));	//Streamer mode lasts for 3 hours
            $_COOKIE["streaming"] = 1;
        }else{
            setcookie("streaming", null, -1);
            unset($_COOKIE["streaming"]);
        }
    }

	StopTimer("UpdateCookies");
}

function LoadCookies(){
    global $_COOKIE;
	AddActionLog("LoadCookies");
	StartTimer("LoadCookies");

    $cookies = Array();

    $cookies["is_streamer"] = 0;
    $cookies["darkmode"] = 0;

    //Determine whether the person is in dark mode
    $cookies["darkmode"] = (isset($_COOKIE["darkmode"])) ? $_COOKIE["darkmode"] : 0;

    //Determine whether the person is in streaming mode
    $cookies["is_streamer"] = (isset($_COOKIE["streaming"])) ? $_COOKIE["streaming"] : 0;

	StopTimer("LoadCookies");
    return $cookies;
}

function RenderCookies(&$cookies){
	AddActionLog("RenderCookies");
    StartTimer("RenderCookies");
    
    $render = Array();

    $render["is_streamer"] = $cookies["is_streamer"];
    $render["darkmode"] = $cookies["darkmode"];

	StopTimer("RenderCookies");
    return $render;
}

?>