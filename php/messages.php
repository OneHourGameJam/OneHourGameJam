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

?>