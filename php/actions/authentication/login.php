<?php
include_once "authactions.php";

function PerformAction(&$loggedInUser){
	global $_POST;

	$username = (isset($_POST["un"])) ? $_POST["un"] : "";
	$password = (isset($_POST["pw"])) ? $_POST["pw"] : "";
	$loginChecked = false;

	$username = strtolower(trim($username));
	$password = trim($password);
	return TryLogin($username, $password, false);
}

?>