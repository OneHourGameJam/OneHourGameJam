<?php

interface AbstractMessage{
    public function ToString();
}

class LogMessage implements AbstractMessage{
    public $LogType;
    public $Text;
    public $OriginatorDescriptor;
    public $OriginatorUserId;
    public $SubjectUserId;

    function __construct($logType, $text, $originatorDescriptor = "", $originatorUserId = 0, $subjectUserId = 0) {
        $this->LogType = $logType;
        $this->Text = $text;
        $this->OriginatorDescriptor = $originatorDescriptor;
        $this->OriginatorUserId = $originatorUserId;
        $this->SubjectUserId = $subjectUserId;
    }

    public static function UserLogMessage($logType, $text, $originatorUserId, $subjectUserId = 0){
        return new LogMessage($logType, $text, "", $originatorUserId, $subjectUserId);
    }

    public static function SystemLogMessage($logType, $text, $originatorDescriptor, $subjectUserId = 0){
        return new LogMessage($logType, $text, $originatorDescriptor, 0, $subjectUserId);
    }

    public function ToString(){
        $sourceDescriptor = ($this->OriginatorDescriptor != "") ? $this->OriginatorDescriptor : ( ($this->SubjectUserId != 0) ? $this->OriginatorUserId." => ".$this->SubjectUserId : $this->OriginatorUserId );
        return "LOG (".$this->LogType."): ".$this->Text." (Source: ".$sourceDescriptor.")";
    }
}

interface MessageSubscriber{
    public function ReceiveMessage(AbstractMessage &$message);
}

class MessageService{
    private $subscribers = Array();

    public function Subscribe(MessageSubscriber &$subject){
        if($this->IsSubscribed($subject)){
            throw new Exception("subject is already subscribed to message service.");
            return;
        }
        $this->subscribers[] = $subject;
    }

    public function Unsubscribe(MessageSubscriber &$subject){
        $elementId = array_search($subject, $this->subscribers);
        if($elementId === false){
            throw new Exception("subject is not subscribed to message service.");
            return;
        }
        unset($this->subscribers[$elementId]);
    }

    public function IsSubscribed(&$subject){
        return array_search($subject, $this->subscribers) !== false;
    }

    public function SendMessage(AbstractMessage &$message){
        foreach($this->subscribers as $subscriber){
            $subscriber->ReceiveMessage($message);
        }
    }
}


?>