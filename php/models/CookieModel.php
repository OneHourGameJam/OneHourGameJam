<?php

define("COOKIE_DARKMODE", "darkmode");
define("COOKIE_STREAMING", "streaming");
define("COOKIE_COOKIENOTICE", "cookienotice");
define("COOKIE_SESSION_ID", "sessionID");
define("COOKIE_ACTION_RESULT", "actionResult");
define("COOKIE_ACTION_RESULT_ACTION", "actionResultAction");
define("COOKIE_CLOSED_NOTIFICATIONS", "closednotifications");

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
        $cookieModel->DarkMode = (isset($_COOKIE[COOKIE_DARKMODE])) ? $_COOKIE[COOKIE_DARKMODE] : 0;

        //Determine whether the person is in streaming mode
        $cookieModel->IsStreamer = (isset($_COOKIE[COOKIE_STREAMING])) ? $_COOKIE[COOKIE_STREAMING] : 0;

        //Determine whether the user has seen or dismissed the cookie notice
        $cookieModel->CookieNotice = (isset($_COOKIE[COOKIE_COOKIENOTICE])) ? $_COOKIE[COOKIE_COOKIENOTICE] : -1;

        StopTimer("LoadCookies");
        return $cookieModel;
    }
}

?>