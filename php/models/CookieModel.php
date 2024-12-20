<?php

const COOKIE_DARKMODE = "darkmode";
const COOKIE_STREAMING = "streaming";
const COOKIE_COOKIENOTICE = "cookienotice";
const COOKIE_SESSION_ID = "sessionID";
const COOKIE_ACTION_RESULT = "actionResult";
const COOKIE_ACTION_RESULT_ACTION = "actionResultAction";
const COOKIE_CLOSED_NOTIFICATIONS = "closednotifications";

class CookieModel{
    public bool $IsStreamer;
    public bool $DarkMode;
    public int $CookieNotice;
}

class CookieData{
    public CookieModel $CookieModel;

    function __construct() {
        $this->CookieModel = $this->LoadCookies();
    }

    function LoadCookies(): CookieModel
    {
        global $_COOKIE;
        AddActionLog("LoadCookies");
        StartTimer("LoadCookies");

        $cookieModel = new CookieModel();

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