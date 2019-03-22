<?php

function PerformPendingSiteAction(&$configData, &$actions, &$loggedInUser){
    global $_POST;
	AddActionLog("PerformPendingSiteAction");
	StartTimer("PerformPendingSiteAction");

    if(isset($_POST["action"])){
        foreach($actions as $i => $siteActionModel){
            $actionPostRequest = $siteActionModel->PostRequest;
            $actionPhpFile = $siteActionModel->PhpFile;
            $actionRedirectAfterExecution = $siteActionModel->RedirectAfterExecution;

            if($_POST["action"] == $actionPostRequest){
                $actionResult = "PROCESSING";
                include_once($actionPhpFile);
                $actionResult = PerformAction($loggedInUser);
                
                if(isset($siteActionModel->ActionResult[$actionResult]->RedirectUrl)){
                    setcookie("actionResultAction", $actionPostRequest, time() + 30);
                    setcookie("actionResult", $actionResult, time() + 30);
                    $redirectURL = $siteActionModel->ActionResult[$actionResult]->RedirectUrl;
                    header("Location: ".$redirectURL);
                    die("Redirecting to <a href='$actionRedirectAfterExecution'>$actionRedirectAfterExecution</a>...");
                }

                die("Unknown action result $actionResult for action $actionPostRequest. Please report this error to administrators.  <a href='?page=main'>back to index</a>...");
            }
        }
    }

	StopTimer("PerformPendingSiteAction");
}

?>