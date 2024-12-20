<?php

//Edits an existing user's password, user is identified by the username.
function DeleteUser(MessageService &$messageService, $userId): string{
    global $userData, $configData, $loggedInUser, $userDbInterface;

    //Authorize user (is admin)
    if(IsAdmin($loggedInUser) === false){
        return "NOT_AUTHORIZED";
    }

    //Check that the user exists
    if(!isset($userData->UserModels[$userId])){
        return "USER_DOES_NOT_EXIST";
    }

    $userDbInterface->DeleteUser($userId);

    $username = $userData->UserModels[$userId]->Username;
    $messageService->SendMessage(LogMessage::UserLogMessage(
        "USER_DELETED",
        "User $username was deleted",
        $loggedInUser->Id,
        $userId)
    );
    $userData->LogAdminAction($loggedInUser->Id);

    return "SUCCESS";
}

function PerformAction(MessageService &$messageService, &$loggedInUser): string{
    global $_POST;

    if(IsAdmin($loggedInUser) !== false){
        $userId = $_POST[FORM_SAVENEWUSERPASSWORD_USER_ID];
        $password1 = $_POST[FORM_SAVENEWUSERPASSWORD_PASSWORD_1];
        $password2 = $_POST[FORM_SAVENEWUSERPASSWORD_PASSWORD_2];

        return EditUserPassword($messageService, $userId, $password1, $password2);
    }
}

?>