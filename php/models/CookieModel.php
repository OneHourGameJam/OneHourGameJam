<?php

class CookieModel{
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

        $cookieModel = new CookieModel();

        $cookieModel->IsStreamer = 0;
        $cookieModel->DarkMode = 0;
        $cookieModel->CookieNotice = -1;

        //Determine whether the person is in dark mode
        $cookieModel->DarkMode = (isset($_COOKIE["darkmode"])) ? $_COOKIE["darkmode"] : 0;

        //Determine whether the person is in streaming mode
        $cookieModel->IsStreamer = (isset($_COOKIE["streaming"])) ? $_COOKIE["streaming"] : 0;

        //Determine whether the user has seen or dismissed the cookie notice
        $cookieModel->CookieNotice = (isset($_COOKIE["cookienotice"])) ? $_COOKIE["cookienotice"] : -1;

        StopTimer("LoadCookies");
        return $cookieModel;
    }
}

?>