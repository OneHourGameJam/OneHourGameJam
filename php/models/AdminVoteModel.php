<?php

class AdminVoteModel{
	public $SubjectUserId;
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
            SELECT v.vote_subject_user_id, v.vote_type
            FROM admin_vote v, user u
            WHERE v.vote_voter_user_id = u.user_id
            AND u.user_role = 1
        ";
        $data = mysqli_query($dbConn, $sql);
        $sql = "";

        while($info = mysqli_fetch_array($data)){
            $adminVote = new AdminVoteModel();

            $adminVote->SubjectUserId = $info["vote_subject_user_id"];
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

        $escapedVoterId = mysqli_real_escape_string($dbConn, $loggedInUser->Id);

        $sql = "
            SELECT vote_subject_user_id, vote_type
            FROM admin_vote
            WHERE vote_voter_user_id = '$escapedVoterId';
        ";
        $data = mysqli_query($dbConn, $sql);
        $sql = "";

        while($info = mysqli_fetch_array($data)){
            $adminVoteData = Array();

            $adminVoteData["subject_user_id"] = $info["vote_subject_user_id"];
            $adminVoteData["vote_type"] = $info["vote_type"];

            $loggedInUserAdminVotes[] = $adminVoteData;
        }

        StopTimer("LoadLoggedInUsersAdminVotes");
        return $loggedInUserAdminVotes;
    }

    function GetAdminVotesCastByUserFormatted($userId){
        global $dbConn;
        AddActionLog("GetAdminVotesCastByUserFormatted");
        StartTimer("GetAdminVotesCastByUserFormatted");
    
        $escapedUserId = mysqli_real_escape_string($dbConn, $userId);
        $sql = "
            SELECT *
            FROM admin_vote
            WHERE vote_voter_user_id = $escapedUserId;
        ";
        $data = mysqli_query($dbConn, $sql);
        $sql = "";
    
        StopTimer("GetAdminVotesCastByUserFormatted");
        return ArrayToHTML(MySQLDataToArray($data));
    }
    
    function GetAdminVotesForSubjectUserFormatted($userId){
        global $dbConn;
        AddActionLog("GetAdminVotesForSubjectUserFormatted");
        StartTimer("GetAdminVotesForSubjectUserFormatted");
    
        $escapedUserId = mysqli_real_escape_string($dbConn, $userId);
        $sql = "
            SELECT vote_id, vote_datetime, 'redacted' AS vote_voter_user_id, vote_subject_user_id, 'redacted' AS vote_type
            FROM admin_vote
            WHERE vote_subject_user_id = $escapedUserId;
        ";
        $data = mysqli_query($dbConn, $sql);
        $sql = "";
    
        StopTimer("GetAdminVotesForSubjectUserFormatted");
        return ArrayToHTML(MySQLDataToArray($data));
    }
}

?>