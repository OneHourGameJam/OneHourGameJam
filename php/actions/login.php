<?php
	$username = (isset($_POST["un"])) ? $_POST["un"] : "";
	$password = (isset($_POST["pw"])) ? $_POST["pw"] : "";
	$loginChecked = false;
	
	$username = strtolower(trim($username));
	$password = trim($password);
    LogInOrRegister($username, $password);
?>