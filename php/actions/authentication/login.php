<?php
include_once "authactions.php";

function PerformAction(&$loggedInUser){
	global $_POST;

	$username = (isset($_POST[FORM_LOGIN_USERNAME])) ? $_POST[FORM_LOGIN_USERNAME] : "";
	$password = (isset($_POST[FORM_LOGIN_PASSWORD])) ? $_POST[FORM_LOGIN_PASSWORD] : "";
	$loginChecked = false;

	$username = strtolower(trim($username));
	$password = trim($password);
	return TryLogin($username, $password, false);
}

?>