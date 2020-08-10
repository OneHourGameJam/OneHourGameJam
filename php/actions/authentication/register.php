<?php
include_once "authactions.php";

function PerformAction(&$loggedInUser){
    global $_POST;

    $username = (isset($_POST[FORM_REGISTER_USERNAME])) ? $_POST[FORM_REGISTER_USERNAME] : "";
    $password = (isset($_POST[FORM_REGISTER_PASSWORD])) ? $_POST[FORM_REGISTER_PASSWORD] : "";
    $loginChecked = false;

    $username = strtolower(trim($username));
    $password = trim($password);
    return TryLogin($username, $password, true);
}