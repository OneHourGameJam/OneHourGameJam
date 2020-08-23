<?php

abstract class AbstractPlugin implements MessageSubscriber{
    public $NameInTemplate;

    function __construct(&$messageService) {
        $messageService->Subscribe($this);
    }

    public abstract function ReceiveMessage(AbstractMessage &$message);
    public abstract function PageSettings();
    public abstract function EstablishDatabaseConnection();
    public abstract function RetrieveData();
    public abstract function GetUserData($userId);
    public abstract function ShouldBeRendered(&$dependencies);
}

?>