<?php

function PerformPendingSiteAction(&$configData, &$siteActionData, &$loggedInUser){
    global $_POST;
	AddActionLog("PerformPendingSiteAction");
	StartTimer("PerformPendingSiteAction");

    if(isset($_POST[FORM_POST_ACTION])){

        if (!checkCSRFToken()) {
            die("Potential CSRF Detected. If you made this request from the website, please contact the site admin.
                <br>
                If you are a developer, add {{> csrf_token}} to the form you just submitted!");
        }

        foreach($siteActionData->SiteActionModels as $i => $siteActionModel){
            $actionPostRequest = $siteActionModel->PostRequest;
            $actionPhpFile = $siteActionModel->PhpFile;
            $actionRedirectAfterExecution = $siteActionModel->RedirectAfterExecution;

            if($_POST[FORM_POST_ACTION] == $actionPostRequest){
                $actionResult = "PROCESSING";
                include_once($actionPhpFile);
                $actionResult = PerformAction($loggedInUser);
                
                if(isset($siteActionModel->ActionResult[$actionResult]->RedirectUrl)){
                    setcookie(COOKIE_ACTION_RESULT_ACTION, $actionPostRequest, time() + 30);
                    setcookie(COOKIE_ACTION_RESULT, $actionResult, time() + 30);
                    $redirectURL = $siteActionModel->ActionResult[$actionResult]->RedirectUrl;
                    header("Location: ".$redirectURL);
                    die("Redirecting to <a href='$actionRedirectAfterExecution'>$actionRedirectAfterExecution</a>...");
                }

                die("Unknown action result $actionResult for action $actionPostRequest. Please report this error to administrators.  <a href='?".GET_PAGE."=main'>back to index</a>...");
            }
        }
    }

	StopTimer("PerformPendingSiteAction");
}

?>