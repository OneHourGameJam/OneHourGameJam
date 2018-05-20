<?php

function AddMessage($type, $title, $body, $bounceToIndex){
	global $warnings, $dictionary, $page;
	
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
	AddMessage("warning", $title, $body, $bounceToIndex);
}

function AddError($title, $body, $bounceToIndex){
	AddMessage("danger", $title, $body, $bounceToIndex);
}

function AddSuccess($title, $body, $bounceToIndex){
	AddMessage("success", $title, $body, $bounceToIndex);
}

//Message categories
function AddDataWarning($body, $bounceToIndex){
	AddWarning("Data Warning", $body, $bounceToIndex);
}

function AddAuthorizationWarning($body, $bounceToIndex){
	AddWarning("Authorization Warning", $body, $bounceToIndex);
}

function AddInternalDataError($body, $bounceToIndex){
	AddError("Internal Data Error", $body, $bounceToIndex);
}

function AddDataSuccess($body){
	AddSuccess("Data Added/Updated", $body, false);
}

//Common messages
function AddAdminAuthorizationWarning($bounceToIndex){
	AddAuthorizationWarning("Must be a site admin to upload assets.", $bounceToIndex);
}
?>