<?php

define("DB_TABLE_USER", "user");

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

class UserDbInterface{
    private $dbConnection;
    private $publicColumnsUser = Array(DB_COLUMN_USER_ID, DB_COLUMN_USER_USERNAME, DB_COLUMN_USER_DISPLAY_NAME, DB_COLUMN_USER_TWITTER, DB_COLUMN_USER_BIO);
    private $privateColumnsUser = Array(DB_COLUMN_USER_DATETIME, DB_COLUMN_USER_IP, DB_COLUMN_USER_USER_AGENT, DB_COLUMN_USER_SALT, DB_COLUMN_USER_PASSWORD_HASH, DB_COLUMN_USER_PASSWORD_ITERATIONS, DB_COLUMN_USER_LAST_LOGIN_DATETIME, DB_COLUMN_USER_LAST_IP, DB_COLUMN_USER_LAST_USER_AGENT, DB_COLUMN_USER_EMAIL, DB_COLUMN_USER_ROLE, DB_COLUMN_USER_PREFERENCES);

    function __construct(&$dbConn) {
        $this->dbConnection = $dbConn;
    }

    public function SelectAll(){
        AddActionLog("UserDbInterface_SelectAll");
        StartTimer("UserDbInterface_SelectAll");
    
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
        
        StopTimer("UserDbInterface_SelectAll");
        return mysqli_query($this->dbConnection, $sql);
    }

    public function SelectAdminCandidates(){
        AddActionLog("UserDbInterface_SelectAdminCandidates");
        StartTimer("UserDbInterface_SelectAdminCandidates");

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
        
        StopTimer("UserDbInterface_SelectAdminCandidates");
        return mysqli_query($this->dbConnection, $sql);
    }

    public function SelectBioOfUser($userId){
        AddActionLog("UserDbInterface_SelectBioOfUser");
        StartTimer("UserDbInterface_SelectBioOfUser");
    
        $escapedUserId = mysqli_real_escape_string($this->dbConnection, $userId);
        $sql = "
            SELECT ".DB_COLUMN_USER_BIO." 
            FROM ".DB_TABLE_USER." 
            WHERE ".DB_COLUMN_USER_ID." = $escapedUserId";
        
        StopTimer("UserDbInterface_SelectBioOfUser");
        return mysqli_query($this->dbConnection, $sql);
    }

    public function SelectUsersOfUser($userId){
        AddActionLog("UserDbInterface_SelectUsersOfUser");
        StartTimer("UserDbInterface_SelectUsersOfUser");
    
        $escapedUserId = mysqli_real_escape_string($this->dbConnection, $userId);
        $sql = "
            SELECT *
            FROM ".DB_TABLE_USER."
            WHERE ".DB_COLUMN_USER_ID." = $escapedUserId;
        ";
        
        StopTimer("UserDbInterface_SelectUsersOfUser");
        return mysqli_query($this->dbConnection, $sql);
    }

    public function SelectSessionsOfUser($userId){
        AddActionLog("UserDbInterface_SelectSessionsOfUser");
        StartTimer("UserDbInterface_SelectSessionsOfUser");

        $escapedUserId = mysqli_real_escape_string($this->dbConnection, $userId);
        $sql = "
            SELECT *
            FROM ".DB_TABLE_SESSION."
            WHERE ".DB_COLUMN_SESSION_USER_ID." = $escapedUserId;
        ";
        
        StopTimer("UserDbInterface_SelectSessionsOfUser");
        return mysqli_query($this->dbConnection, $sql);
    }

    public function SelectPublicData(){
        AddActionLog("UserData_SelectPublicUserData");
        StartTimer("UserData_SelectPublicUserData");

        $sql = "
            SELECT ".implode(",", $this->publicColumnsUser)."
            FROM ".DB_TABLE_USER.";
        ";

        StopTimer("UserData_SelectPublicUserData");
        return mysqli_query($this->dbConnection, $sql);
    }

}

?>