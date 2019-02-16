<?php

function AddMessage($type, $title, $body, $bounceToIndex){
	global $warnings, $dictionary, $page;
	AddActionLog("AddMessage");

	$newWarning = Array();

	$newWarning["warning_type"] = $type;
	$newWarning["warning_title"] = $title;
	$newWarning["warning_body"] = $body;

	$warnings[] = $newWarning;
	$dictionary["warnings"][] = $newWarning;

	if($bounceToIndex){
		$page = "main";
	}
}

//Message types
function AddWarning($title, $body, $bounceToIndex){
	AddActionLog("AddWarning");

	AddMessage("warning", $title, $body, $bounceToIndex);
}

function AddError($title, $body, $bounceToIndex){
	AddActionLog("AddError");
	
	AddMessage("danger", $title, $body, $bounceToIndex);
}

function AddSuccess($title, $body, $bounceToIndex){
	AddActionLog("AddSuccess");

	AddMessage("success", $title, $body, $bounceToIndex);
}

//Message categories
function AddDataWarning($body, $bounceToIndex){
	AddActionLog("AddDataWarning");

	AddWarning("Data Warning", $body, $bounceToIndex);
}

function AddAuthorizationWarning($body, $bounceToIndex){
	AddActionLog("AddAuthorizationWarning");

	AddWarning("Authorization Warning", $body, $bounceToIndex);
}

function AddInternalDataError($body, $bounceToIndex){
	AddActionLog("AddInternalDataError");

	AddError("Internal Data Error", $body, $bounceToIndex);
}

function AddDataSuccess($body){
	AddActionLog("AddDataSuccess");

	AddSuccess("Data Added/Updated", $body, false);
}

//Common messages
function AddAdminAuthorizationWarning($bounceToIndex){
	AddActionLog("AddAdminAuthorizationWarning");
	
	AddAuthorizationWarning("Must be a site admin to upload assets.", $bounceToIndex);
}
?>