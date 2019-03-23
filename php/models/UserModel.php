<?php

$userPreferenceSettings = Array(
	Array("PREFERENCE_KEY" => "DISABLE_THEMES_NOTIFICATION", "BIT_FLAG_EXPONENT" => 0)
);

class UserModel
{
    public $Id;
    public $Username;
    public $DisplayName;
    public $Twitter;
    public $TwitterTextOnly;
    public $Email;
    public $Salt;
    public $PasswordHash;
    public $PasswordIterations;
    public $Admin;
    public $UserPreferences;
    public $Preferences;
    public $DaysSinceLastLogin;
    public $DaysSinceLastAdminAction;
    public $IsSponsored;
    public $SponsoredBy;
}

class UserData{
    public $UserModels;
    public $UserPreferences;

    function __construct() {
        $this->UserModels = $this->LoadUsers();
    }

    function LoadUsers(){
        global $dbConn, $userPreferenceSettings;
        AddActionLog("LoadUsers");
        StartTimer("LoadUsers");
    
        $userModels = Array();
    
        $sql = "SELECT user_id, user_username, user_display_name, user_twitter, user_email,
                       user_password_salt, user_password_hash, user_password_iterations, user_role, user_preferences,
                       DATEDIFF(Now(), user_last_login_datetime) AS days_since_last_login,
                       DATEDIFF(Now(), log_max_datetime) AS days_since_last_admin_action
                FROM
                    user u LEFT JOIN
                    (
                        SELECT log_admin_username, max(log_datetime) AS log_max_datetime
                        FROM admin_log
                        GROUP BY log_admin_username
                    ) al ON u.user_username = al.log_admin_username";
        $data = mysqli_query($dbConn, $sql);
        $sql = "";
    
        while($info = mysqli_fetch_array($data)){
            $currentUser = Array();
            $username = $info["user_username"];
            
            $user = new UserModel();
            $user->Id = $info["user_id"];
            $user->Username = $username;
            $user->DisplayName = $info["user_display_name"];
            $user->Twitter = $info["user_twitter"];
            $user->TwitterTextOnly = str_replace("@", "", $info["user_twitter"]);
            $user->Email = $info["user_email"];
            $user->Salt = $info["user_password_salt"];
            $user->PasswordHash = $info["user_password_hash"];
            $user->PasswordIterations = intval($info["user_password_iterations"]);
            $user->Admin = intval($info["user_role"]);
            $user->UserPreferences = intval($info["user_preferences"]);
            $user->Preferences = Array();
            $user->DaysSinceLastLogin = 1000000;
            $user->DaysSinceLastAdminAction = 1000000;
            $user->IsSponsored = 0;
            $user->SponsoredBy = "";
    
            foreach($userPreferenceSettings as $i => $preferenceSetting){
                $preferenceFlag = pow(2, $preferenceSetting["BIT_FLAG_EXPONENT"]);
                $preferenceKey = $preferenceSetting["PREFERENCE_KEY"];
    
                $user->Preferences[$preferenceKey] = $user->UserPreferences & $preferenceFlag;
            }
    
            //This fixes an issue where user_last_login_datetime was not set properly in the database, which results in days_since_last_login being null for users who have not logged in since the fix was applied
            if($info["days_since_last_login"] == null){
                $info["days_since_last_login"] = 1000000;
            }
    
            //For cases where users have never performed an admin action
            if($info["days_since_last_admin_action"] == null){
                $info["days_since_last_admin_action"] = 1000000;
            }
    
            $user->DaysSinceLastLogin = intval($info["days_since_last_login"]);
            $user->DaysSinceLastAdminAction = intval($info["days_since_last_admin_action"]);
    
            $userModels[$username] = $user;
        }
    
        ksort($userModels);
    
        //Get list of sponsored users to be administration candidates, ensuring the voter is still an admin and the candidate hasn't become an admin since the vote was cast
        $sql = "
            SELECT v.vote_voter_username, v.vote_subject_username
            FROM admin_vote v, user u1, user u2
            WHERE v.vote_voter_username = u1.user_username
            AND u1.user_role = 1
            AND v.vote_subject_username = u2.user_username
            AND u2.user_role = 0
            AND v.vote_type = 'SPONSOR'
        ";
        $data = mysqli_query($dbConn, $sql);
        $sql = "";
    
        while($info = mysqli_fetch_array($data)){
            $voteVoterUsername = $info["vote_voter_username"];
            $voteSubjectUsername = $info["vote_subject_username"];
    
            $userModels[$voteSubjectUsername]->IsSponsored = 1;
            $userModels[$voteSubjectUsername]->SponsoredBy = $voteVoterUsername;
        }
    
        StopTimer("LoadUsers");
        return $userModels;
    }
}

?>