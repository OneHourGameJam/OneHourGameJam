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
    private $database;
    private $publicColumns = Array(DB_COLUMN_ADMINVOTE_ID, DB_COLUMN_ADMINVOTE_VOTER_USER_ID, DB_COLUMN_ADMINVOTE_SUBJECT_USER_ID);
    private $privateColumns = Array(DB_COLUMN_ADMINVOTE_DATETIME, DB_COLUMN_ADMINVOTE_IP, DB_COLUMN_ADMINVOTE_USER_AGENT, DB_COLUMN_ADMINVOTE_TYPE);

    function __construct(&$database) {
        $this->database = $database;
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
        return $this->database->Execute($sql);;
    }

    public function SelectVotesByUser($voterUserId){
        AddActionLog("AdminVoteDbInterface_SelectVotesByUser");
        StartTimer("AdminVoteDbInterface_SelectVotesByUser");

        $escapedVoterId = $this->database->EscapeString($voterUserId);
        $sql = "
            SELECT ".DB_COLUMN_ADMINVOTE_SUBJECT_USER_ID.", ".DB_COLUMN_ADMINVOTE_TYPE."
            FROM ".DB_TABLE_ADMINVOTE."
            WHERE ".DB_COLUMN_ADMINVOTE_VOTER_USER_ID." = '$escapedVoterId';
        ";

        StopTimer("AdminVoteDbInterface_SelectVotesByUser");
        return $this->database->Execute($sql);;
    }

    public function SelectWhereVoterUserId($voterUserId){
        AddActionLog("AdminVoteDbInterface_SelectWhereVoterUserId");
        StartTimer("AdminVoteDbInterface_SelectWhereVoterUserId");

        $escapedVoterUserId = $this->database->EscapeString($voterUserId);
        $sql = "
            SELECT *
            FROM ".DB_TABLE_ADMINVOTE."
            WHERE ".DB_COLUMN_ADMINVOTE_VOTER_USER_ID." = '$escapedVoterUserId';";

        StopTimer("AdminVoteDbInterface_SelectWhereVoterUserId");
        return $this->database->Execute($sql);;
    }

    public function SelectWhereSubjectUserId($subjectUserId){
        AddActionLog("AdminVoteDbInterface_SelectWhereSubjectUserId");
        StartTimer("AdminVoteDbInterface_SelectWhereSubjectUserId");

        $escapedSubjectUserId = $this->database->EscapeString($subjectUserId);
        $sql = "
            SELECT ".DB_COLUMN_ADMINVOTE_ID.", ".DB_COLUMN_ADMINVOTE_DATETIME.", 'redacted' AS ".DB_COLUMN_ADMINVOTE_VOTER_USER_ID.", ".DB_COLUMN_ADMINVOTE_SUBJECT_USER_ID.", 'redacted' AS ".DB_COLUMN_ADMINVOTE_TYPE."
            FROM ".DB_TABLE_ADMINVOTE."
            WHERE ".DB_COLUMN_ADMINVOTE_SUBJECT_USER_ID." = '$escapedSubjectUserId';";
            
        StopTimer("AdminVoteDbInterface_SelectWhereSubjectUserId");
        return $this->database->Execute($sql);;
    }

    public function SelectSingleVoteIdByVoterAndSubjectUserId($voterUserId, $subjectUserId){
        AddActionLog("AdminVoteDbInterface_SelectSingleVoteIdByVoterAndSubjectUserId");
        StartTimer("AdminVoteDbInterface_SelectSingleVoteIdByVoterAndSubjectUserId");

        $escapedVoterUserId = $this->database->EscapeString($voterUserId);
        $escapedSubjectUserId = $this->database->EscapeString($subjectUserId);
        
        $sql = "
            SELECT ".DB_COLUMN_ADMINVOTE_ID."
            FROM ".DB_TABLE_ADMINVOTE."
            WHERE ".DB_COLUMN_ADMINVOTE_VOTER_USER_ID." = $escapedVoterUserId
              AND ".DB_COLUMN_ADMINVOTE_SUBJECT_USER_ID." = $escapedSubjectUserId;
        ";

        StopTimer("AdminVoteDbInterface_SelectSingleVoteIdByVoterAndSubjectUserId");
        return $this->database->Execute($sql);;
    }

    public function SelectSingleVoteIdByVoterUserIdAndVoteType($voterUserId, $voteType){
        AddActionLog("AdminVoteDbInterface_SelectSingleVoteIdByVoterUserIdAndVoteType");
        StartTimer("AdminVoteDbInterface_SelectSingleVoteIdByVoterUserIdAndVoteType");

        $escapedVoterUserId = $this->database->EscapeString($voterUserId);
        $escapedVoteType = $this->database->EscapeString($voteType);
        
		$sql = "
            SELECT ".DB_COLUMN_ADMINVOTE_ID."
            FROM ".DB_TABLE_ADMINVOTE."
            WHERE ".DB_COLUMN_ADMINVOTE_VOTER_USER_ID." = $escapedVoterUserId
              AND ".DB_COLUMN_ADMINVOTE_TYPE." = '$escapedVoteType'
        ";

        StopTimer("AdminVoteDbInterface_SelectSingleVoteIdByVoterUserIdAndVoteType");
        return $this->database->Execute($sql);;
    }

    public function Insert($ip, $userAgent, $voterUserId, $subjectUserId, $voteType){
        AddActionLog("AdminVoteDbInterface_Insert");
        StartTimer("AdminVoteDbInterface_Insert");
        
        $escapedIp = $this->database->EscapeString($ip);
        $escapedUserAgent = $this->database->EscapeString($userAgent);
        $escapedVoterUserId = $this->database->EscapeString($voterUserId);
        $escapedSubjectUserId = $this->database->EscapeString($subjectUserId);
        $escapedVoteType = $this->database->EscapeString($voteType);

        $sql = "
            INSERT INTO ".DB_TABLE_ADMINVOTE."
            (".DB_COLUMN_ADMINVOTE_ID.", ".DB_COLUMN_ADMINVOTE_DATETIME.", ".DB_COLUMN_ADMINVOTE_IP.", ".DB_COLUMN_ADMINVOTE_USER_AGENT.", ".DB_COLUMN_ADMINVOTE_VOTER_USER_ID.", ".DB_COLUMN_ADMINVOTE_SUBJECT_USER_ID.", ".DB_COLUMN_ADMINVOTE_TYPE.")
            VALUES
            (null,
            Now(),
            '$escapedIp',
            '$escapedUserAgent',
            $escapedVoterUserId,
            $escapedSubjectUserId,
            '$escapedVoteType');
        ";
        $this->database->Execute($sql);;
            
        StopTimer("AdminVoteDbInterface_Insert");
    }

    public function Delete($voteId){
        AddActionLog("AdminVoteDbInterface_Delete");
        StartTimer("AdminVoteDbInterface_Delete");
        
        $escapedVoteId = $this->database->EscapeString($voteId);

        $sql = "
            DELETE FROM ".DB_TABLE_ADMINVOTE."
            WHERE ".DB_COLUMN_ADMINVOTE_ID." = $escapedVoteId
        ";
        $this->database->Execute($sql);;
            
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
        return $this->database->Execute($sql);;
    }
}

?>