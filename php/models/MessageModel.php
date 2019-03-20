<?php

class MessageModel{
    public $Type;
    public $Title;
    public $Body;
}

class MessageData{
    public $MessageModels;

    function __construct(&$actions) {
        $this->MessageModels = $this->LoadMessages($actions);
    }

    function LoadMessages(&$actions){
        global $_COOKIE;
        AddActionLog("LoadMessages");
        StartTimer("LoadMessages");

        $messageModels = Array();

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
                        
                        $messageModel = new MessageModel();
                        $messageModel->Type = $messageType;
                        $messageModel->Title = $messageTitle;
                        $messageModel->Body = $messageText;
                        $messageModels[] = $messageModel;
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

        StopTimer("LoadMessages");
        return $messageModels;
    }
}

?>