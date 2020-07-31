<?php

define("DB_TABLE_USER", "user");
define("DB_TABLE_SESSION", "session");

define("DB_COLUMN_USER_ID",                     "user_id");
define("DB_COLUMN_USER_USERNAME",               "user_username");
define("DB_COLUMN_USER_DATETIME",               "user_datetime");
define("DB_COLUMN_USER_IP",                     "user_register_ip");
define("DB_COLUMN_USER_USER_AGENT",             "user_register_user_agent");
define("DB_COLUMN_USER_DISPLAY_NAME",           "user_display_name");
define("DB_COLUMN_USER_SALT",                   "user_password_salt");
define("DB_COLUMN_USER_PASSWORD_HASH",          "user_password_hash");
define("DB_COLUMN_USER_PASSWORD_ITERATIONS",    "user_password_iterations");
define("DB_COLUMN_USER_LAST_LOGIN_DATETIME",    "user_last_login_datetime");
define("DB_COLUMN_USER_LAST_IP",                "user_last_ip");
define("DB_COLUMN_USER_LAST_USER_AGENT",        "user_last_user_agent");
define("DB_COLUMN_USER_EMAIL",                  "user_email");
define("DB_COLUMN_USER_TWITTER",                "user_twitter");
define("DB_COLUMN_USER_BIO",                    "user_bio");
define("DB_COLUMN_USER_ROLE",                   "user_role");
define("DB_COLUMN_USER_PREFERENCES",            "user_preferences");

define("DB_COLUMN_SESSION_ID",                  "session_id");
define("DB_COLUMN_SESSION_USER_ID",             "session_user_id");
define("DB_COLUMN_SESSION_DATETIME_STARTED",    "session_datetime_started");
define("DB_COLUMN_SESSION_DATETIME_LAST_USED",  "session_datetime_last_used");

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
    public $SponsoredByUserId;
}

class UserData{
    public $UserModels;
    public $UsernameToId;

    private $dbConnection;
    private $publicColumnsUser = Array(DB_COLUMN_USER_ID, DB_COLUMN_USER_USERNAME, DB_COLUMN_USER_DISPLAY_NAME, DB_COLUMN_USER_TWITTER, DB_COLUMN_USER_BIO);
    private $privateColumnsUser = Array(DB_COLUMN_USER_DATETIME, DB_COLUMN_USER_IP, DB_COLUMN_USER_USER_AGENT, DB_COLUMN_USER_SALT, DB_COLUMN_USER_PASSWORD_HASH, DB_COLUMN_USER_PASSWORD_ITERATIONS, DB_COLUMN_USER_LAST_LOGIN_DATETIME, DB_COLUMN_USER_LAST_IP, DB_COLUMN_USER_LAST_USER_AGENT, DB_COLUMN_USER_EMAIL, DB_COLUMN_USER_ROLE, DB_COLUMN_USER_PREFERENCES);
    private $publicColumnsSession = Array(DB_COLUMN_SESSION_USER_ID);
    private $privateColumnsSession = Array(DB_COLUMN_SESSION_ID, DB_COLUMN_SESSION_DATETIME_STARTED, DB_COLUMN_SESSION_DATETIME_LAST_USED);

    function __construct(&$dbConn) {
        $this->dbConnection = $dbConn;
        $this->UserModels = $this->LoadUsers();
        $this->UsernameToId = $this->GenerateUsernameToId();
    }

//////////////////////// MODEL CONSTRUCTOR

    function LoadUsers(){
        global $userPreferenceSettings;
        AddActionLog("LoadUsers");
        StartTimer("LoadUsers");
    
        $data = $this->SelectAll();
    
        $userModels = Array();
        while($info = mysqli_fetch_array($data)){
            $currentUser = Array();
            
            $user = new UserModel();
            $user->Id = $info[DB_COLUMN_USER_ID];
            $user->Username = $info[DB_COLUMN_USER_USERNAME];
            $user->DisplayName = $info[DB_COLUMN_USER_DISPLAY_NAME];
            $user->Twitter = $info[DB_COLUMN_USER_TWITTER];
            $user->TwitterTextOnly = str_replace("@", "", $info[DB_COLUMN_USER_TWITTER]);
            $user->Email = $info[DB_COLUMN_USER_EMAIL];
            $user->Salt = $info[DB_COLUMN_USER_SALT];
            $user->PasswordHash = $info[DB_COLUMN_USER_PASSWORD_HASH];
            $user->PasswordIterations = intval($info[DB_COLUMN_USER_PASSWORD_ITERATIONS]);
            $user->Admin = intval($info[DB_COLUMN_USER_ROLE]);
            $user->UserPreferences = intval($info[DB_COLUMN_USER_PREFERENCES]);
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
    
            $userModels[$user->Id] = $user;
        }
    
        ksort($userModels);
    
        $data = $this->SelectAdminCandidates();
    
        while($info = mysqli_fetch_array($data)){
            $voteVoterUserId = $info[DB_COLUMN_ADMINVOTE_VOTER_USER_ID];
            $voteSubjectUserId = $info[DB_COLUMN_ADMINVOTE_SUBJECT_USER_ID];
    
            foreach($userModels as $i => $userModel){
                if($userModel->Id == $voteSubjectUserId){
                    $userModels[$i]->IsSponsored = 1;
                    $userModels[$i]->SponsoredByUserId = $voteVoterUserId;
                }
            }
        }
    
        StopTimer("LoadUsers");
        return $userModels;
    }

    function GenerateUsernameToId(){
        $usernamesToIds = Array();

        foreach($this->UserModels as $i => $userModel){
            $usernamesToIds[$userModel->Username] = $userModel->Id;
        }

        return $usernamesToIds;
    }

//////////////////////// END MODEL CONSTRUCTOR
    
//////////////////////// DATABASE ACTIONS (select, insert, update)

    function LoadBio($userId) {
        AddActionLog("LoadBio");
        StartTimer("LoadBio");
    
        $data = $this->SelectBioOfUser($userId);

        $info = mysqli_fetch_array($data);
        $bio = $info[DB_COLUMN_USER_BIO];
    
        StopTimer("LoadBio");
        return $bio;
    }

    function GetUsersOfUserFormatted($userId){
        AddActionLog("GetUsersOfUserFormatted");
        StartTimer("GetUsersOfUserFormatted");
    
        $data = $this->SelectUsersOfUser($userId);
    
        StopTimer("GetUsersOfUserFormatted");
        return ArrayToHTML(MySQLDataToArray($data));
    }
    
    function GetSessionsOfUserFormatted($userId){
        AddActionLog("GetSessionsOfUserFormatted");
        StartTimer("GetSessionsOfUserFormatted");
    
        $data = $this->SelectSessionsOfUser($userId);
    
        StopTimer("GetSessionsOfUserFormatted");
        return ArrayToHTML(MySQLDataToArray($data));
    }

//////////////////////// END DATABASE ACTIONS
    
//////////////////////// FUCNTION SQL

    private function SelectAll(){
        AddActionLog("UserModel_SelectAll");
        StartTimer("UserModel_SelectAll");
    
        $sql = "SELECT ".DB_COLUMN_USER_ID.", ".DB_COLUMN_USER_USERNAME.", ".DB_COLUMN_USER_DISPLAY_NAME.", ".DB_COLUMN_USER_TWITTER.", ".DB_COLUMN_USER_EMAIL.",
                       ".DB_COLUMN_USER_SALT.", ".DB_COLUMN_USER_PASSWORD_HASH.", ".DB_COLUMN_USER_PASSWORD_ITERATIONS.", ".DB_COLUMN_USER_ROLE.", ".DB_COLUMN_USER_PREFERENCES.",
                       DATEDIFF(Now(), ".DB_COLUMN_USER_LAST_LOGIN_DATETIME.") AS days_since_last_login,
                       DATEDIFF(Now(), log_max_datetime) AS days_since_last_admin_action
                FROM
                    ".DB_TABLE_USER." u LEFT JOIN
                    (
                        SELECT ".DB_COLUMN_ADMIN_LOG_ADMIN_USER_ID.", max(".DB_COLUMN_ADMIN_LOG_DATETIME.") AS log_max_datetime
                        FROM ".DB_TABLE_ADMIN_LOG."
                        GROUP BY ".DB_COLUMN_ADMIN_LOG_ADMIN_USER_ID."
                    ) al ON u.".DB_COLUMN_USER_ID." = al.".DB_COLUMN_ADMIN_LOG_ADMIN_USER_ID."";
        
        StopTimer("UserModel_SelectAll");
        return mysqli_query($this->dbConnection, $sql);
    }

    private function SelectAdminCandidates(){
        AddActionLog("UserModel_SelectAdminCandidates");
        StartTimer("UserModel_SelectAdminCandidates");

        //Get list of sponsored users to be administration candidates, ensuring the voter is still an admin and the candidate hasn't become an admin since the vote was cast
        $sql = "
            SELECT v.".DB_COLUMN_ADMINVOTE_VOTER_USER_ID.", v.".DB_COLUMN_ADMINVOTE_SUBJECT_USER_ID."
            FROM ".DB_TABLE_ADMINVOTE." v, ".DB_TABLE_USER." u1, ".DB_TABLE_USER." u2
            WHERE v.".DB_COLUMN_ADMINVOTE_VOTER_USER_ID." = u1.".DB_COLUMN_USER_ID."
            AND u1.".DB_COLUMN_USER_ROLE." = 1
            AND v.".DB_COLUMN_ADMINVOTE_SUBJECT_USER_ID." = u2.".DB_COLUMN_USER_ID."
            AND u2.".DB_COLUMN_USER_ROLE." = 0
            AND v.".DB_COLUMN_ADMINVOTE_TYPE." = 'SPONSOR'
        ";
        
        StopTimer("UserModel_SelectAdminCandidates");
        return mysqli_query($this->dbConnection, $sql);
    }

    private function SelectBioOfUser($userId){
        AddActionLog("UserModel_SelectBioOfUser");
        StartTimer("UserModel_SelectBioOfUser");
    
        $escapedUserId = mysqli_real_escape_string($this->dbConnection, $userId);
        $sql = "
            SELECT ".DB_COLUMN_USER_BIO." 
            FROM ".DB_TABLE_USER." 
            WHERE ".DB_COLUMN_USER_ID." = $escapedUserId";
        
        StopTimer("UserModel_SelectBioOfUser");
        return mysqli_query($this->dbConnection, $sql);
    }

    private function SelectUsersOfUser($userId){
        AddActionLog("UserModel_SelectUsersOfUser");
        StartTimer("UserModel_SelectUsersOfUser");
    
        $escapedUserId = mysqli_real_escape_string($this->dbConnection, $userId);
        $sql = "
            SELECT *
            FROM ".DB_TABLE_USER."
            WHERE ".DB_COLUMN_USER_ID." = $escapedUserId;
        ";
        
        StopTimer("UserModel_SelectUsersOfUser");
        return mysqli_query($this->dbConnection, $sql);
    }

    private function SelectSessionsOfUser($userId){
        AddActionLog("UserModel_SelectSessionsOfUser");
        StartTimer("UserModel_SelectSessionsOfUser");

        $escapedUserId = mysqli_real_escape_string($this->dbConnection, $userId);
        $sql = "
            SELECT *
            FROM ".DB_TABLE_SESSION."
            WHERE ".DB_COLUMN_SESSION_USER_ID." = $escapedUserId;
        ";
        
        StopTimer("UserModel_SelectSessionsOfUser");
        return mysqli_query($this->dbConnection, $sql);
    }

//////////////////////// END FUCNTION SQL

//////////////////////// PUBLIC DATA EXPORT
    function GenerateRandomString($length) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    function GetAllPublicUserData(){
        global $configData;
        AddActionLog("UserData_GetAllPublicUserData");
        StartTimer("UserData_GetAllPublicUserData");
        
        $dataFromDatabase = MySQLDataToArray($this->SelectPublicUserData());

        foreach($dataFromDatabase as $i => $row){
            $password = $this->GenerateRandomString(30);
            $passwordHashIterations = GenerateUserHashIterations($configData);
            $salt = GenerateSalt();
            $hashedPassword = HashPassword($password, $salt, $passwordHashIterations, $configData);

            $dataFromDatabase[$i][DB_COLUMN_USER_DATETIME] = gmdate("Y-m-d H:i:s", time());
            $dataFromDatabase[$i][DB_COLUMN_USER_IP] = "MIGRATION";
            $dataFromDatabase[$i][DB_COLUMN_USER_USER_AGENT] = "MIGRATION";
            $dataFromDatabase[$i][DB_COLUMN_USER_SALT] = $salt;
            $dataFromDatabase[$i][DB_COLUMN_USER_PASSWORD_HASH] = $hashedPassword;
            $dataFromDatabase[$i][DB_COLUMN_USER_PASSWORD_ITERATIONS] = $passwordHashIterations;
            $dataFromDatabase[$i][DB_COLUMN_USER_LAST_LOGIN_DATETIME] = gmdate("Y-m-d H:i:s", time());
            $dataFromDatabase[$i][DB_COLUMN_USER_LAST_IP] = "MIGRATION";
            $dataFromDatabase[$i][DB_COLUMN_USER_LAST_USER_AGENT] = "MIGRATION";
            $dataFromDatabase[$i][DB_COLUMN_USER_EMAIL] = "";
            $dataFromDatabase[$i][DB_COLUMN_USER_ROLE] = 0;
            $dataFromDatabase[$i][DB_COLUMN_USER_PREFERENCES] = rand(0, 1);

            $password = "";
            $passwordHashIterations = "";
            $salt = "";
            $hashedPassword = "";
        }

        StopTimer("UserData_GetAllPublicUserData");
        return $dataFromDatabase;
    }

    private function SelectPublicUserData(){
        AddActionLog("UserData_SelectPublicUserData");
        StartTimer("UserData_SelectPublicUserData");

        $sql = "
            SELECT ".implode(",", $this->publicColumnsUser)."
            FROM ".DB_TABLE_USER.";
        ";

        StopTimer("UserData_SelectPublicUserData");
        return mysqli_query($this->dbConnection, $sql);
    }

    function GetAllPublicSessionData($sessionHashIterations, $pepper){
        global $configData;
        AddActionLog("UserData_GetAllPublicSessionData");
        StartTimer("UserData_GetAllPublicSessionData");
        
        $dataFromDatabase = MySQLDataToArray($this->SelectPublicSessionData());

        foreach($dataFromDatabase as $i => $row){
            $sessionId = GenerateSalt();
            $hashedSessionId = HashPassword($sessionId, $pepper, $sessionHashIterations, $configData);

            $dataFromDatabase[$i][DB_COLUMN_SESSION_ID] = $hashedSessionId;
            $dataFromDatabase[$i][DB_COLUMN_SESSION_DATETIME_STARTED] = gmdate("Y-m-d H:i:s", time());
            $dataFromDatabase[$i][DB_COLUMN_SESSION_DATETIME_LAST_USED] = gmdate("Y-m-d H:i:s", time());
            
            $sessionId = "";
            $hashedSessionId = "";
        }

        StopTimer("UserData_GetAllPublicSessionData");
        return $dataFromDatabase;
    }

    private function SelectPublicSessionData(){
        AddActionLog("UserData_SelectPublicSessionData");
        StartTimer("UserData_SelectPublicSessionData");

        $sql = "
            SELECT ".implode(",", $this->publicColumnsSession)."
            FROM ".DB_TABLE_SESSION.";
        ";

        StopTimer("UserData_SelectPublicSessionData");
        return mysqli_query($this->dbConnection, $sql);
    }

//////////////////////// END PUBLIC DATA EXPORT
}

?>