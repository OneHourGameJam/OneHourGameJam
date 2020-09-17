<?php

abstract class AbstractPlugin implements MessageSubscriber{
    public $NameInTemplate;
    protected $LoggedInUser;

    function __construct(&$messageService, &$loggedInUser) {
        $messageService->Subscribe($this);
        $this->LoggedInUser = $loggedInUser;
    }

    public abstract function ReceiveMessage(AbstractMessage &$message);
    public abstract function PageSettings();
    public abstract function EstablishDatabaseConnection();
    public abstract function RetrieveData();
    public abstract function GetUserData($userId);
    public abstract function ShouldBeRendered(&$dependencies);
}

?>