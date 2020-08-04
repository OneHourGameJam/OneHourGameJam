<?php
define("DB_TABLE_ADMINVOTE", "admin_vote");

define("DB_COLUMN_ADMINVOTE_ID", "vote_id");
define("DB_COLUMN_ADMINVOTE_DATETIME", "vote_datetime");
define("DB_COLUMN_ADMINVOTE_IP", "vote_ip");
define("DB_COLUMN_ADMINVOTE_USER_AGENT", "vote_user_agent");
define("DB_COLUMN_ADMINVOTE_VOTER_USER_ID", "vote_voter_user_id");
define("DB_COLUMN_ADMINVOTE_SUBJECT_USER_ID", "vote_subject_user_id");
define("DB_COLUMN_ADMINVOTE_TYPE", "vote_type");

class AdminVoteDbInterface{
    private $dbConnection;
    private $publicColumns = Array(DB_COLUMN_ADMINVOTE_ID, DB_COLUMN_ADMINVOTE_VOTER_USER_ID, DB_COLUMN_ADMINVOTE_SUBJECT_USER_ID);
    private $privateColumns = Array(DB_COLUMN_ADMINVOTE_DATETIME, DB_COLUMN_ADMINVOTE_IP, DB_COLUMN_ADMINVOTE_USER_AGENT, DB_COLUMN_ADMINVOTE_TYPE);

    function __construct(&$dbConn) {
        $this->dbConnection = $dbConn;
    }

    public function SelectCurrentlyActiveVotes(){
        AddActionLog("AdminVoteDbInterface_SelectCurrentlyActiveVotes");
        StartTimer("AdminVoteDbInterface_SelectCurrentlyActiveVotes");

        $sql = "
            SELECT v.".DB_COLUMN_ADMINVOTE_SUBJECT_USER_ID.", v.".DB_COLUMN_ADMINVOTE_TYPE."
            FROM ".DB_TABLE_ADMINVOTE." v, ".DB_TABLE_USER." u
            WHERE v.".DB_COLUMN_ADMINVOTE_VOTER_USER_ID." = u.".DB_COLUMN_USER_ID."
            AND u.".DB_COLUMN_USER_ROLE." = 1
        ";

        StopTimer("AdminVoteDbInterface_SelectCurrentlyActiveVotes");
        return mysqli_query($this->dbConnection, $sql);
    }

    public function SelectVotesByUser($voterUserId){
        AddActionLog("AdminVoteDbInterface_SelectVotesByUser");
        StartTimer("AdminVoteDbInterface_SelectVotesByUser");

        $escapedVoterId = mysqli_real_escape_string($this->dbConnection, $voterUserId);
        $sql = "
            SELECT ".DB_COLUMN_ADMINVOTE_SUBJECT_USER_ID.", ".DB_COLUMN_ADMINVOTE_TYPE."
            FROM ".DB_TABLE_ADMINVOTE."
            WHERE ".DB_COLUMN_ADMINVOTE_VOTER_USER_ID." = '$escapedVoterId';
        ";

        StopTimer("AdminVoteDbInterface_SelectVotesByUser");
        return mysqli_query($this->dbConnection, $sql);
    }

    public function SelectWhereVoterUserId($voterUserId){
        AddActionLog("AdminVoteDbInterface_SelectWhereVoterUserId");
        StartTimer("AdminVoteDbInterface_SelectWhereVoterUserId");

        $escapedVoterUserId = mysqli_real_escape_string($this->dbConnection, $voterUserId);
        $sql = "
            SELECT *
            FROM ".DB_TABLE_ADMINVOTE."
            WHERE ".DB_COLUMN_ADMINVOTE_VOTER_USER_ID." = '$escapedVoterUserId';";

        StopTimer("AdminVoteDbInterface_SelectWhereVoterUserId");
        return mysqli_query($this->dbConnection, $sql);
    }

    public function SelectWhereSubjectUserId($subjectUserId){
        AddActionLog("AdminVoteDbInterface_SelectWhereSubjectUserId");
        StartTimer("AdminVoteDbInterface_SelectWhereSubjectUserId");

        $escapedSubjectUserId = mysqli_real_escape_string($this->dbConnection, $subjectUserId);
        $sql = "
            SELECT ".DB_COLUMN_ADMINVOTE_ID.", ".DB_COLUMN_ADMINVOTE_DATETIME.", 'redacted' AS ".DB_COLUMN_ADMINVOTE_VOTER_USER_ID.", ".DB_COLUMN_ADMINVOTE_SUBJECT_USER_ID.", 'redacted' AS ".DB_COLUMN_ADMINVOTE_TYPE."
            FROM ".DB_TABLE_ADMINVOTE."
            WHERE ".DB_COLUMN_ADMINVOTE_SUBJECT_USER_ID." = '$escapedSubjectUserId';";
            
        StopTimer("AdminVoteDbInterface_SelectWhereSubjectUserId");
        return mysqli_query($this->dbConnection, $sql);
    }

    public function SelectSingleVoteIdByVoterAndSubjectUserId($voterUserId, $subjectUserId){
        AddActionLog("AdminVoteDbInterface_SelectSingleVoteIdByVoterAndSubjectUserId");
        StartTimer("AdminVoteDbInterface_SelectSingleVoteIdByVoterAndSubjectUserId");

        $escapedVoterUserId = mysqli_real_escape_string($this->dbConnection, $voterUserId);
        $escapedSubjectUserId = mysqli_real_escape_string($this->dbConnection, $subjectUserId);
        
        $sql = "
            SELECT vote_id
            FROM admin_vote
            WHERE vote_voter_user_id = $escapedVoterUserId
              AND vote_subject_user_id = $escapedSubjectUserId;
        ";

        StopTimer("AdminVoteDbInterface_SelectSingleVoteIdByVoterAndSubjectUserId");
        return mysqli_query($this->dbConnection, $sql);
    }

    public function SelectSingleVoteIdByVoterUserIdAndVoteType($voterUserId, $voteType){
        AddActionLog("AdminVoteDbInterface_SelectSingleVoteIdByVoterUserIdAndVoteType");
        StartTimer("AdminVoteDbInterface_SelectSingleVoteIdByVoterUserIdAndVoteType");

        $escapedVoterUserId = mysqli_real_escape_string($this->dbConnection, $voterUserId);
        $escapedVoteType = mysqli_real_escape_string($this->dbConnection, $voteType);
        
		$sql = "
            SELECT vote_id
            FROM admin_vote
            WHERE vote_voter_user_id = $escapedVoterUserId
              AND vote_type = '$escapedVoteType'
        ";

        StopTimer("AdminVoteDbInterface_SelectSingleVoteIdByVoterUserIdAndVoteType");
        return mysqli_query($this->dbConnection, $sql);
    }

    public function Insert($ip, $userAgent, $voterUserId, $subjectUserId, $voteType){
        AddActionLog("AdminVoteDbInterface_Insert");
        StartTimer("AdminVoteDbInterface_Insert");
        
        $escapedIp = mysqli_real_escape_string($this->dbConnection, $ip);
        $escapedUserAgent = mysqli_real_escape_string($this->dbConnection, $userAgent);
        $escapedVoterUserId = mysqli_real_escape_string($this->dbConnection, $voterUserId);
        $escapedSubjectUserId = mysqli_real_escape_string($this->dbConnection, $subjectUserId);
        $escapedVoteType = mysqli_real_escape_string($this->dbConnection, $voteType);

        $sql = "
            INSERT INTO admin_vote
            (vote_id, vote_datetime, vote_ip, vote_user_agent, vote_voter_user_id, vote_subject_user_id, vote_type)
            VALUES
            (null,
            Now(),
            '$escapedIp',
            '$escapedUserAgent',
            $escapedVoterUserId,
            $escapedSubjectUserId,
            '$escapedVoteType');
        ";
        mysqli_query($this->dbConnection, $sql);
            
        StopTimer("AdminVoteDbInterface_Insert");
    }

    public function Delete($voteId){
        AddActionLog("AdminVoteDbInterface_Delete");
        StartTimer("AdminVoteDbInterface_Delete");
        
        $escapedVoteId = mysqli_real_escape_string($this->dbConnection, $voteId);

        $sql = "
            DELETE FROM admin_vote
            WHERE vote_id = $escapedVoteId
        ";
        mysqli_query($this->dbConnection, $sql);
            
        StopTimer("AdminVoteDbInterface_Delete");
    }

    public function SelectPublicData(){
        AddActionLog("AdminVoteDbInterface_SelectPublicData");
        StartTimer("AdminVoteDbInterface_SelectPublicData");

        $sql = "
            SELECT ".implode(",", $this->publicColumns)."
            FROM ".DB_TABLE_ADMINVOTE.";
        ";

        StopTimer("AdminVoteDbInterface_SelectPublicData");
        return mysqli_query($this->dbConnection, $sql);
    }
}

?>