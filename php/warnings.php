<?php

function LoadMessages($actions){
	global $_COOKIE;

	$messages = Array();

	//Messages and warnings from cookies, clear them as soon as they are loaded
	if(isset($_COOKIE["actionResult"]) && isset($_COOKIE["actionResultAction"])){
		$messageActionResult = $_COOKIE["actionResult"];
		$messageActionResultAction = $_COOKIE["actionResultAction"];
	
		$actionFound = false;
		foreach($actions as $i => $action){
			if($messageActionResultAction == $action["POST_REQUEST"]){
				$actionFound = true;
				if(isset($action["ACTION_RESULT"][$messageActionResult])){
					$actionResultData = $action["ACTION_RESULT"][$messageActionResult];
	
					$messageType = $actionResultData["MESSAGE_TYPE"];
					$messageText = $actionResultData["MESSAGE_TEXT"];
	
					switch($messageType){
						case "success":
							$messageTitle = "Success";
							break;
						case "warning":
							$messageTitle = "Warning";
							break;
						case "error":
							$messageTitle = "Error";
							break;
						case "none":
							break;
						default:
							die("Unknown message type $messageType");
							break;
					}
					
					$message = Array();
					$message["message_type"] = $messageType;
					$message["message_title"] = $messageTitle;
					$message["message_body"] = $messageText;
					$messages[] = $message;
				}else{
					die("Action result $messageActionResult for $messageActionResultAction not found in actions list");
				}
			}
		}
		
		if(!$actionFound){
			die("Action $messageActionResultAction not found in actions list");
		}
	
		setcookie("actionResult", "", 0);
		setcookie("actionResultAction", "", 0);
	}

	return $messages;
}

function RenderMessages($messages){
	$render = Array();

	foreach($messages as $i => $messageData){
		$message = Array();
		
		$message["message_type"] = $messageData["message_type"];
		$message["message_title"] = $messageData["message_title"];
		$message["message_body"] = $messageData["message_body"];

		$render[] = $message;
	}

	return $messages;
}

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