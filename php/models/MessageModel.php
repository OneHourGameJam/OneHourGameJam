<?php

define("MESSAGE_SUCCESS", "success");
define("MESSAGE_WARNING", "warning");
define("MESSAGE_ERROR", "error");
define("MESSAGE_NONE", "none");

class MessageModel{
    public $Type;
    public $Title;
    public $Body;
}

class MessageData{
    public $MessageModels;

    function __construct(&$siteActionData) {
        $this->MessageModels = $this->LoadMessages($siteActionData);
    }

    function LoadMessages(&$siteActionData){
        global $_COOKIE;
        AddActionLog("LoadMessages");
        StartTimer("LoadMessages");

        $messageModels = Array();

        //Messages and warnings from cookies, clear them as soon as they are loaded
        if(isset($_COOKIE[COOKIE_ACTION_RESULT]) && isset($_COOKIE[COOKIE_ACTION_RESULT_ACTION])){
            $messageActionResult = $_COOKIE[COOKIE_ACTION_RESULT];
            $messageActionResultAction = $_COOKIE[COOKIE_ACTION_RESULT_ACTION];
        
            $actionFound = false;
            foreach($siteActionData->SiteActionModels as $i => $siteActionModel){
                if($messageActionResultAction == $siteActionModel->PostRequest){
                    $actionFound = true;
                    if(isset($siteActionModel->ActionResult[$messageActionResult])){
                        $actionResultData = $siteActionModel->ActionResult[$messageActionResult];
        
                        $messageType = $actionResultData->MessageType;
                        $messageText = $actionResultData->MessageText;
        
                        switch($messageType){
                            case MESSAGE_SUCCESS:
                                $messageTitle = "Success";
                                break;
                            case MESSAGE_WARNING:
                                $messageTitle = "Warning";
                                break;
                            case MESSAGE_ERROR:
                                $messageTitle = "Error";
                                break;
                            case MESSAGE_NONE:
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
        
            setcookie(COOKIE_ACTION_RESULT, "", 0);
            setcookie(COOKIE_ACTION_RESULT_ACTION, "", 0);
        }

        StopTimer("LoadMessages");
        return $messageModels;
    }
}

?>