<?php

include_once("php/adminvote/AdminVoteDbInterface.php");
include_once("php/adminvote/AdminVoteModel.php");
include_once("php/adminvote/AdminVotePresenter.php");
include_once("php/adminvote/AdminVoteViewModel.php");

define("RENDER_ADMIN_VOTES", "RenderAdminVotes");

class AdminVotePlugin extends \AbstractPlugin{
    public $NameInTemplate = "admin_votes";

    private $AdminVoteDbInterface;
    private $AdminVoteData;

    public function ReceiveMessage(\AbstractMessage &$message){

    }

    public function PageSettings(){
        return Array();
    }

    public function EstablishDatabaseConnection(){
        $database = new \Database();
	    $this->AdminVoteDbInterface = new AdminVoteDbInterface($database);
    }

    public function RetrieveData(){
        $this->AdminVoteData = new AdminVoteData($this->AdminVoteDbInterface, $this->LoggedInUser);
    }

    public function GetUserData($userId){
        $userData = Array();
        $userData["Admin Votes (when voter)"] = $this->AdminVoteData->GetAdminVotesCastByUserFormatted($userId);
        $userData["Admin Votes (when subject)"] = $this->AdminVoteData->GetAdminVotesForSubjectUserFormatted($userId);
        return  $userData;
    }

    public function ShouldBeRendered(&$dependencies){
        if(FindDependency(RENDER_ADMIN_VOTES, $dependencies) !== false){
            return true;
        }
        return false;
    }

    public function Render(){
        $render = Array();
        //$render["adminlog"] = AdminLogPresenter::RenderAdminLog($this->AdminLogData, $userData);
        return $render;
    }
}

?>