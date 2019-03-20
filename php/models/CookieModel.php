<?php

class CookiesModel{
    public $IsStreamer;
    public $DarkMode;
    public $CookieNotice;
}

class CookieData{
    public $CookieModel;

    function __construct() {
        $this->CookieModel = $this->LoadCookies();
    }

    function LoadCookies(){
        global $_COOKIE;
        AddActionLog("LoadCookies");
        StartTimer("LoadCookies");

        $cookies = new CookiesModel();

        $cookies->IsStreamer = 0;
        $cookies->DarkMode = 0;
        $cookies->CookieNotice = -1;

        //Determine whether the person is in dark mode
        $cookies->DarkMode = (isset($_COOKIE["darkmode"])) ? $_COOKIE["darkmode"] : 0;

        //Determine whether the person is in streaming mode
        $cookies->IsStreamer = (isset($_COOKIE["streaming"])) ? $_COOKIE["streaming"] : 0;

        //Determine whether the user has seen or dismissed the cookie notice
        $cookies->CookieNotice = (isset($_COOKIE["cookienotice"])) ? $_COOKIE["cookienotice"] : -1;

        StopTimer("LoadCookies");
        return $cookies;
    }
}

?>