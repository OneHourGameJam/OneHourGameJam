<?php

define("CONFIG_DEFAULT_SATURATION", "DEFAULT_SATURATION");
define("CONFIG_DEFAULT_NUMBER_OF_COLORS", "DEFAULT_NUMBER_OF_COLORS");
define("CONFIG_DATABASE_VERSION", "DATABASE_VERSION");
define("CONFIG_DISPLAY_THEME_PROBABILITY", "DISPLAY_THEME_PROBABILITY");
define("CONFIG_TWITTER_ACCOUNT", "TWITTER_ACCOUNT");
define("CONFIG_TWITCH_ACCOUNT", "TWITCH_ACCOUNT");
define("CONFIG_IRC_ADDRESS", "IRC_ADDRESS");
define("CONFIG_IRC_CHANNEL", "IRC_CHANNEL");
define("CONFIG_IRC_CHAT_IN_BROWSER", "IRC_CHAT_IN_BROWSER");
define("CONFIG_DISCORD_INVITE_URL", "DISCORD_INVITE_URL");
define("CONFIG_TWITCH_API_STREAM_UPDATE_FREQUENCY", "TWITCH_API_STREAM_UPDATE_FREQUENCY");
define("CONFIG_THEME_DAYS_MARK_AS_OLD", "THEME_DAYS_MARK_AS_OLD");
define("CONFIG_THEME_MIN_VOTES_TO_SCORE", "THEME_MIN_VOTES_TO_SCORE");
define("CONFIG_THEME_NUMBER_TO_MARK_TOP", "THEME_NUMBER_TO_MARK_TOP");
define("CONFIG_THEME_NUMBER_TO_MARK_KEEP", "THEME_NUMBER_TO_MARK_KEEP");
define("CONFIG_JAMS_CONSIDERED_RECENT", "JAMS_CONSIDERED_RECENT");
define("CONFIG_SATISFACTION_RATINGS_TO_SHOW_SCORE", "SATISFACTION_RATINGS_TO_SHOW_SCORE");
define("CONFIG_PEPPER", "PEPPER");
define("CONFIG_SESSION_PASSWORD_ITERATIONS", "SESSION_PASSWORD_ITERATIONS");
define("CONFIG_STREAMER_TWITCH_NAME", "STREAMER_TWITCH_NAME");
define("CONFIG_TWITCH_CLIENT_ID", "TWITCH_CLIENT_ID");
define("CONFIG_RULES", "RULES");
define("CONFIG_NOTIFICATION_URL", "NOTIFICATION_URL");
define("CONFIG_NOTIFICATION_IMAGE", "NOTIFICATION_IMAGE");
define("CONFIG_NOTIFICATION", "NOTIFICATION");
define("CONFIG_JAM_TIME", "JAM_TIME");
define("CONFIG_JAM_DAY", "JAM_DAY");
define("CONFIG_JAMNAME", "JAMNAME");
define("CONFIG_JAMDESC", "JAMDESC");
define("CONFIG_DEFAULT_BRIGHTNESS", "DEFAULT_BRIGHTNESS");
define("CONFIG_DEFAULT_HUE_MIN", "DEFAULT_HUE_MIN");
define("CONFIG_DEFAULT_HUE_MAX", "DEFAULT_HUE_MAX");
define("CONFIG_ADMIN_ACTIVITY_JAMS_SINCE_LAST_PARTICIPATION_WARNING", "ADMIN_ACTIVITY_JAMS_SINCE_LAST_PARTICIPATION_WARNING");
define("CONFIG_ADMIN_SUGGESTION_TOTAL_PARTICIPATION", "ADMIN_SUGGESTION_TOTAL_PARTICIPATION");
define("CONFIG_ADMIN_SUGGESTION_RECENT_PARTICIPATION", "ADMIN_SUGGESTION_RECENT_PARTICIPATION");
define("CONFIG_ADMIN_ACTIVITY_DAYS_SINCE_LAST_ADMIN_ACTION_WARNING", "ADMIN_ACTIVITY_DAYS_SINCE_LAST_ADMIN_ACTION_WARNING");
define("CONFIG_ADMIN_ACTIVITY_DAYS_SINCE_LAST_LOGIN_GOOD", "ADMIN_ACTIVITY_DAYS_SINCE_LAST_LOGIN_GOOD");
define("CONFIG_ADMIN_ACTIVITY_JAMS_SINCE_LAST_PARTICIPATION_GOOD", "ADMIN_ACTIVITY_JAMS_SINCE_LAST_PARTICIPATION_GOOD");
define("CONFIG_ADMIN_ACTIVITY_DAYS_SINCE_LAST_LOGIN_WARNING", "ADMIN_ACTIVITY_DAYS_SINCE_LAST_LOGIN_WARNING");
define("CONFIG_ADMIN_ACTIVITY_DAYS_SINCE_LAST_ADMIN_ACTION_GOOD", "ADMIN_ACTIVITY_DAYS_SINCE_LAST_ADMIN_ACTION_GOOD");
define("CONFIG_REDIRECT_TO_HTTPS", "REDIRECT_TO_HTTPS");
define("CONFIG_JAM_DURATION", "JAM_DURATION");
define("CONFIG_MINIMUM_PASSWORD_LENGTH", "MINIMUM_PASSWORD_LENGTH");
define("CONFIG_MAXIMUM_PASSWORD_LENGTH", "MAXIMUM_PASSWORD_LENGTH");
define("CONFIG_MINIMUM_PASSWORD_HASH_ITERATIONS", "MINIMUM_PASSWORD_HASH_ITERATIONS");
define("CONFIG_MAXIMUM_PASSWORD_HASH_ITERATIONS", "MAXIMUM_PASSWORD_HASH_ITERATIONS");
define("CONFIG_MINIMUM_USERNAME_LENGTH", "MINIMUM_USERNAME_LENGTH");
define("CONFIG_MAXIMUM_USERNAME_LENGTH", "MAXIMUM_USERNAME_LENGTH");
define("CONFIG_DAYS_TO_KEEP_LOGGED_IN", "DAYS_TO_KEEP_LOGGED_IN");
define("CONFIG_MAX_COLORS_FOR_JAM", "MAX_COLORS_FOR_JAM");
define("CONFIG_MAX_ASSET_FILE_SIZE_IN_BYTES", "MAX_ASSET_FILE_SIZE_IN_BYTES");
define("CONFIG_MINIMUM_DISPLAY_NAME_LENGTH", "MINIMUM_DISPLAY_NAME_LENGTH");
define("CONFIG_MAXIMUM_DISPLAY_NAME_LENGTH", "MAXIMUM_DISPLAY_NAME_LENGTH");
define("CONFIG_MAX_SCREENSHOT_FILE_SIZE_IN_BYTES", "MAX_SCREENSHOT_FILE_SIZE_IN_BYTES");
define("CONFIG_JAM_AUTO_SCHEDULER_MINUTES_BEFORE_JAM", "JAM_AUTO_SCHEDULER_MINUTES_BEFORE_JAM");
define("CONFIG_JAM_AUTO_SCHEDULER_ENABLED", "JAM_AUTO_SCHEDULER_ENABLED");
define("CONFIG_JAMS_TO_LOAD", "JAMS_TO_LOAD");
define("CONFIG_THEMES_PER_USER", "THEMES_PER_USER");
define("CONFIG_JAM_THEMES_CONSIDERED_RECENT", "JAM_THEMES_CONSIDERED_RECENT");
define("CONFIG_THEME_PHRASE_PLURAL", "THEME_PHRASE_PLURAL");
define("CONFIG_THEME_PHRASE_SINGULAR", "THEME_PHRASE_SINGULAR");
define("CONFIG_SITE_FOOTER", "SITE_FOOTER");
define("CONFIG_GAME_PHRASE_SINGULAR", "GAME_PHRASE_SINGULAR");
define("CONFIG_GAME_PHRASE_PLURAL", "GAME_PHRASE_PLURAL");
define("CONFIG_JAM_PHRASE_SINGULAR", "JAM_PHRASE_SINGULAR");
define("CONFIG_JAM_PHRASE_PLURAL", "JAM_PHRASE_PLURAL");
define("CONFIG_DEVELOPER_PHRASE_SINGULAR", "DEVELOPER_PHRASE_SINGULAR");
define("CONFIG_DEVELOPER_PHRASE_PLURAL", "DEVELOPER_PHRASE_PLURAL");
define("CONFIG_OVERRIDE_BRAND_LOGO", "OVERRIDE_BRAND_LOGO");
define("CONFIG_OVERRIDE_MAIN_LOGO", "OVERRIDE_MAIN_LOGO");
define("CONFIG_CAN_SUBMIT_TO_PAST_JAMS", "CAN_SUBMIT_TO_PAST_JAMS");

class ConfigModel{
	public $Key;
	public $Value;
	public $Category;
	public $Description;
	public $Disabled;
	public $Editable;
	public $Required;
	public $Type;
	public $Options = Array();
	public $AddedToDictionary;
}

class SettingEnumOptionModel{
    public $Text;
    public $Value;
}

class ConfigData{
    public $ConfigModels;

    private $configDbInterface;

    function __construct(&$configDbInterface, &$adminLogData) {
        $this->configDbInterface = $configDbInterface;
        $this->ConfigModels = $this->LoadConfig();
        $this->VerifyConfig($adminLogData);
    }

//////////////////////// MODEL CONSTRUCTOR

    function LoadConfig(){
        AddActionLog("LoadConfig");
        StartTimer("LoadConfig");

        $data = $this->configDbInterface->SelectAll();

        $configModels = Array();
        while($configData = mysqli_fetch_array($data)) {
            $key = $configData[DB_COLUMN_CONFIG_KEY];
            $options = json_decode($configData[DB_COLUMN_CONFIG_OPTIONS], true);
            $editable = $configData[DB_COLUMN_CONFIG_EDITABLE];

            $configModel = new ConfigModel();
            $configModel->Key = $key;
            $configModel->Value = $configData[DB_COLUMN_CONFIG_VALUE];
            $configModel->Category = $configData[DB_COLUMN_CONFIG_CATEGORY];
            $configModel->Description = $configData[DB_COLUMN_CONFIG_DESCRIPTION];
            $configModel->Disabled = !$editable;
            $configModel->Editable = $editable;
            $configModel->Required = $configData[DB_COLUMN_CONFIG_REQUIRED];
            $configModel->Type = $configData[DB_COLUMN_CONFIG_TYPE];
            $configModel->AddedToDictionary = $configData[DB_COLUMN_CONFIG_ADDED_TO_DICTIONARY];

            $configModel->Options = Array();
            foreach($options as $i => $option){
                $settingEnumOptionModel = new SettingEnumOptionModel();
                $settingEnumOptionModel->Text = $option["TEXT"];
                $settingEnumOptionModel->Value = $option["VALUE"];
                $configModel->Options[] = $settingEnumOptionModel;
            }

            $configModels[$key] = $configModel;
        }

        StopTimer("LoadConfig");
        return $configModels;
    }

//////////////////////// END MODEL CONSTRUCTOR
    
//////////////////////// DATABASE ACTIONS (select, insert, update)

    function VerifyConfig(&$adminLogData) {
        AddActionLog("VerifyConfig");
        StartTimer("VerifyConfig");
    
        if (!isset($this->ConfigModels[CONFIG_PEPPER]->Value) || strlen($this->ConfigModels[CONFIG_PEPPER]->Value) < 1) {
            $this->UpdateConfig(CONFIG_PEPPER, GenerateSalt(), OVERRIDE_AUTOMATIC_NUM, OVERRIDE_AUTOMATIC, $adminLogData);
        }
    
        if (!isset($this->ConfigModels[CONFIG_SESSION_PASSWORD_ITERATIONS]->Value) || strlen($this->ConfigModels[CONFIG_SESSION_PASSWORD_ITERATIONS]->Value) < 1) {
            $sessionPasswordIterations = GenerateUserHashIterations($this);
            $this->UpdateConfig(CONFIG_SESSION_PASSWORD_ITERATIONS, $sessionPasswordIterations, OVERRIDE_AUTOMATIC_NUM, OVERRIDE_AUTOMATIC, $adminLogData);
        }
    
        StopTimer("VerifyConfig");
    }

    // Saves config to database, does not authorize to ensure VerifyConfig() continues to work
    function UpdateConfig($key, $value, $userId, $userUsernameOverride, &$adminLogData) {
        AddActionLog("UpdateConfig");
        StartTimer("UpdateConfig");

        if($this->ConfigModels[$key]->Value != $value){
            $this->configDbInterface->Update($key, $value, $userId);
            $this->ConfigModels[$key]->Value = $value;
    
            $adminLogData->AddToAdminLog("CONFIG_UPDATED", "Config value edited: $key = '$value'", "NULL", ($userID > 0) ? $userID : "NULL", ($userID > 0) ? "" : $userUsernameOverride);
        }
    
        StopTimer("UpdateConfig");
    }

//////////////////////// END DATABASE ACTIONS

//////////////////////// PUBLIC DATA EXPORT

    function GetAllPublicData(){
        AddActionLog("ConfigData_GetAllPublicData");
        StartTimer("ConfigData_GetAllPublicData");
        
        $dataFromDatabase = MySQLDataToArray($this->configDbInterface->SelectPublicData());
        
        foreach($dataFromDatabase as $i => $row){
            $dataFromDatabase[$i][DB_COLUMN_CONFIG_LASTEDITED] = gmdate("Y-m-d H:i:s", time());
            $dataFromDatabase[$i][DB_COLUMN_CONFIG_LASTEDITEDBY] = OVERRIDE_MIGRATION;
        }
        
        foreach($dataFromDatabase as $i => $row){
            switch($row[DB_COLUMN_CONFIG_KEY]){
                case CONFIG_TWITTER_ACCOUNT: $dataFromDatabase[$i][DB_COLUMN_CONFIG_VALUE] = ""; break;
                case CONFIG_TWITCH_ACCOUNT: $dataFromDatabase[$i][DB_COLUMN_CONFIG_VALUE] = ""; break;
                case CONFIG_IRC_ADDRESS: $dataFromDatabase[$i][DB_COLUMN_CONFIG_VALUE] = ""; break;
                case CONFIG_IRC_CHANNEL: $dataFromDatabase[$i][DB_COLUMN_CONFIG_VALUE] = ""; break;
                case CONFIG_IRC_CHAT_IN_BROWSER: $dataFromDatabase[$i][DB_COLUMN_CONFIG_VALUE] = ""; break;
                case CONFIG_DISCORD_INVITE_URL: $dataFromDatabase[$i][DB_COLUMN_CONFIG_VALUE] = ""; break;
                case CONFIG_PEPPER: $dataFromDatabase[$i][DB_COLUMN_CONFIG_VALUE] = GenerateSalt(); break;
                case CONFIG_SESSION_PASSWORD_ITERATIONS: $dataFromDatabase[$i][DB_COLUMN_CONFIG_VALUE] = GenerateUserHashIterations($this); break;
                case CONFIG_STREAMER_TWITCH_NAME: $dataFromDatabase[$i][DB_COLUMN_CONFIG_VALUE] = ""; break;
                case CONFIG_TWITCH_CLIENT_ID: $dataFromDatabase[$i][DB_COLUMN_CONFIG_VALUE] = ""; break;
            }
        }

        StopTimer("ConfigData_GetAllPublicData");
        return $dataFromDatabase;
    }

//////////////////////// END PUBLIC DATA EXPORT
}

?>