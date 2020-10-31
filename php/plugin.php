<?php

abstract class AbstractPlugin implements MessageSubscriber{
    public $NameInTemplate;

    function __construct(&$messageService) {
        $messageService->Subscribe($this);
    }

    public abstract function ReceiveMessage(AbstractMessage &$message);
    public abstract function PageSettings();
    public abstract function FormSettings();
    public abstract function CommonDependencies();
    public abstract function EstablishDatabaseConnection();
    public abstract function RetrieveData();
    public abstract function GetUserDataExport($userId);
    public abstract function ShouldBeRendered(&$dependencies);
    public abstract function Render($page, \IUserDisplay &$userData);
    public abstract function GetSiteActionSettings();

    public abstract function ShouldBeLocalized($page);
    public abstract function GetLocalizationFile();
    public function GetLocalization($preferredLanguage){
        $localization = json_decode(file_get_contents($this->GetLocalizationFile()), true);
        $return = Array();

        if(isset($localization[$preferredLanguage])){
            $return = $localization[$preferredLanguage];
        }
        
        foreach($localization["EN"] as $localizationKey => $localizationTemplate){
            if(!isset($return[$localizationKey])){
                $return[$localizationKey] = $localizationTemplate;
            }
        }

        return $return;
    }
}

?>