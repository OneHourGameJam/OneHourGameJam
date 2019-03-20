<?php

class AdminVoteModel{
	public $SubjectUsername;
	public $VoteType;
}

class AdminVoteData{
    public $AdminVoteModels;
    public $LoggedInUserAdminVotes;

    function __construct(&$loggedInUser) {
        $this->AdminVoteModels = $this->LoadAdminVotes();
        $this->LoggedInUserAdminVotes = $this->LoadLoggedInUsersAdminVotes($loggedInUser);
    }

    function LoadAdminVotes(){
        global $dbConn;
        AddActionLog("LoadAdminVotes");
        StartTimer("LoadAdminVotes");

        $adminVoteModels = Array();

        $sql = "
            SELECT v.vote_subject_username, v.vote_type
            FROM admin_vote v, user u
            WHERE v.vote_voter_username = u.user_username
            AND u.user_role = 1
        ";
        $data = mysqli_query($dbConn, $sql);
        $sql = "";

        while($info = mysqli_fetch_array($data)){
            $adminVote = new AdminVoteModel();

            $adminVote->SubjectUsername = $info["vote_subject_username"];
            $adminVote->VoteType = $info["vote_type"];

            $adminVoteModels[] = $adminVote;
        }

        StopTimer("LoadAdminVotes");
        return $adminVoteModels;
    }

    function LoadLoggedInUsersAdminVotes(&$loggedInUser){
        global $dbConn;
        AddActionLog("LoadLoggedInUsersAdminVotes");
        StartTimer("LoadLoggedInUsersAdminVotes");

        $loggedInUserAdminVotes = Array();

        if($loggedInUser == false){
            return $loggedInUserAdminVotes;
        }

        $escapedVoterUsername = mysqli_real_escape_string($dbConn, $loggedInUser->Username);

        $sql = "
            SELECT vote_subject_username, vote_type
            FROM admin_vote
            WHERE vote_voter_username = '$escapedVoterUsername';
        ";
        $data = mysqli_query($dbConn, $sql);
        $sql = "";

        while($info = mysqli_fetch_array($data)){
            $adminVoteData = Array();

            $adminVoteData["subject_username"] = $info["vote_subject_username"];
            $adminVoteData["vote_type"] = $info["vote_type"];

            $loggedInUserAdminVotes[] = $adminVoteData;
        }

        StopTimer("LoadLoggedInUsersAdminVotes");
        return $loggedInUserAdminVotes;
    }
}

?>