<?php

const CONFIG_DEFAULT_SATURATION = "DEFAULT_SATURATION";
const CONFIG_DEFAULT_NUMBER_OF_COLORS = "DEFAULT_NUMBER_OF_COLORS";
const CONFIG_DATABASE_VERSION = "DATABASE_VERSION";
const CONFIG_DISPLAY_THEME_PROBABILITY = "DISPLAY_THEME_PROBABILITY";
const CONFIG_TWITTER_ACCOUNT = "TWITTER_ACCOUNT";
const CONFIG_TWITCH_ACCOUNT = "TWITCH_ACCOUNT";
const CONFIG_IRC_ADDRESS = "IRC_ADDRESS";
const CONFIG_IRC_CHANNEL = "IRC_CHANNEL";
const CONFIG_IRC_CHAT_IN_BROWSER = "IRC_CHAT_IN_BROWSER";
const CONFIG_DISCORD_INVITE_URL = "DISCORD_INVITE_URL";
const CONFIG_TWITCH_API_STREAM_UPDATE_FREQUENCY = "TWITCH_API_STREAM_UPDATE_FREQUENCY";
const CONFIG_THEME_DAYS_MARK_AS_OLD = "THEME_DAYS_MARK_AS_OLD";
const CONFIG_THEME_MIN_VOTES_TO_SCORE = "THEME_MIN_VOTES_TO_SCORE";
const CONFIG_THEME_NUMBER_TO_MARK_TOP = "THEME_NUMBER_TO_MARK_TOP";
const CONFIG_THEME_NUMBER_TO_MARK_KEEP = "THEME_NUMBER_TO_MARK_KEEP";
const CONFIG_JAMS_CONSIDERED_RECENT = "JAMS_CONSIDERED_RECENT";
const CONFIG_SATISFACTION_RATINGS_TO_SHOW_SCORE = "SATISFACTION_RATINGS_TO_SHOW_SCORE";
const CONFIG_PEPPER = "PEPPER";
const CONFIG_SESSION_PASSWORD_ITERATIONS = "SESSION_PASSWORD_ITERATIONS";
const CONFIG_TWITCH_CLIENT_ID = "TWITCH_CLIENT_ID";
const CONFIG_TWITCH_CLIENT_SECRET = "TWITCH_CLIENT_SECRET";
const CONFIG_TWITCH_CHECK_STREAM_AFTER_JAM_END_MINUTES = "TWITCH_CHECK_STREAM_AFTER_JAM_END_MINUTES";
const CONFIG_RULES = "RULES";
const CONFIG_NOTIFICATION_URL = "NOTIFICATION_URL";
const CONFIG_NOTIFICATION_IMAGE = "NOTIFICATION_IMAGE";
const CONFIG_NOTIFICATION = "NOTIFICATION";
const CONFIG_JAM_TIME = "JAM_TIME";
const CONFIG_JAM_DAY = "JAM_DAY";
const CONFIG_JAMNAME = "JAMNAME";
const CONFIG_JAMDESC = "JAMDESC";
const CONFIG_DEFAULT_BRIGHTNESS = "DEFAULT_BRIGHTNESS";
const CONFIG_DEFAULT_HUE_MIN = "DEFAULT_HUE_MIN";
const CONFIG_DEFAULT_HUE_MAX = "DEFAULT_HUE_MAX";
const CONFIG_ADMIN_ACTIVITY_JAMS_SINCE_LAST_PARTICIPATION_WARNING = "ADMIN_ACTIVITY_JAMS_SINCE_LAST_PARTICIPATION_WARNING";
const CONFIG_ADMIN_SUGGESTION_TOTAL_PARTICIPATION = "ADMIN_SUGGESTION_TOTAL_PARTICIPATION";
const CONFIG_ADMIN_SUGGESTION_RECENT_PARTICIPATION = "ADMIN_SUGGESTION_RECENT_PARTICIPATION";
const CONFIG_ADMIN_ACTIVITY_DAYS_SINCE_LAST_ADMIN_ACTION_WARNING = "ADMIN_ACTIVITY_DAYS_SINCE_LAST_ADMIN_ACTION_WARNING";
const CONFIG_ADMIN_ACTIVITY_DAYS_SINCE_LAST_LOGIN_GOOD = "ADMIN_ACTIVITY_DAYS_SINCE_LAST_LOGIN_GOOD";
const CONFIG_ADMIN_ACTIVITY_JAMS_SINCE_LAST_PARTICIPATION_GOOD = "ADMIN_ACTIVITY_JAMS_SINCE_LAST_PARTICIPATION_GOOD";
const CONFIG_ADMIN_ACTIVITY_DAYS_SINCE_LAST_LOGIN_WARNING = "ADMIN_ACTIVITY_DAYS_SINCE_LAST_LOGIN_WARNING";
const CONFIG_ADMIN_ACTIVITY_DAYS_SINCE_LAST_ADMIN_ACTION_GOOD = "ADMIN_ACTIVITY_DAYS_SINCE_LAST_ADMIN_ACTION_GOOD";
const CONFIG_REDIRECT_TO_HTTPS = "REDIRECT_TO_HTTPS";
const CONFIG_JAM_DURATION = "JAM_DURATION";
const CONFIG_MINIMUM_PASSWORD_LENGTH = "MINIMUM_PASSWORD_LENGTH";
const CONFIG_MAXIMUM_PASSWORD_LENGTH = "MAXIMUM_PASSWORD_LENGTH";
const CONFIG_MINIMUM_PASSWORD_HASH_ITERATIONS = "MINIMUM_PASSWORD_HASH_ITERATIONS";
const CONFIG_MAXIMUM_PASSWORD_HASH_ITERATIONS = "MAXIMUM_PASSWORD_HASH_ITERATIONS";
const CONFIG_MINIMUM_USERNAME_LENGTH = "MINIMUM_USERNAME_LENGTH";
const CONFIG_MAXIMUM_USERNAME_LENGTH = "MAXIMUM_USERNAME_LENGTH";
const CONFIG_DAYS_TO_KEEP_LOGGED_IN = "DAYS_TO_KEEP_LOGGED_IN";
const CONFIG_MAX_COLORS_FOR_JAM = "MAX_COLORS_FOR_JAM";
const CONFIG_MAX_ASSET_FILE_SIZE_IN_BYTES = "MAX_ASSET_FILE_SIZE_IN_BYTES";
const CONFIG_MINIMUM_DISPLAY_NAME_LENGTH = "MINIMUM_DISPLAY_NAME_LENGTH";
const CONFIG_MAXIMUM_DISPLAY_NAME_LENGTH = "MAXIMUM_DISPLAY_NAME_LENGTH";
const CONFIG_MAX_SCREENSHOT_FILE_SIZE_IN_BYTES = "MAX_SCREENSHOT_FILE_SIZE_IN_BYTES";
const CONFIG_JAM_AUTO_SCHEDULER_MINUTES_BEFORE_JAM = "JAM_AUTO_SCHEDULER_MINUTES_BEFORE_JAM";
const CONFIG_JAM_AUTO_SCHEDULER_ENABLED = "JAM_AUTO_SCHEDULER_ENABLED";
const CONFIG_JAMS_TO_LOAD = "JAMS_TO_LOAD";
const CONFIG_THEMES_PER_USER = "THEMES_PER_USER";
const CONFIG_JAM_THEMES_CONSIDERED_RECENT = "JAM_THEMES_CONSIDERED_RECENT";
const CONFIG_THEME_PHRASE_PLURAL = "THEME_PHRASE_PLURAL";
const CONFIG_THEME_PHRASE_SINGULAR = "THEME_PHRASE_SINGULAR";
const CONFIG_SITE_FOOTER = "SITE_FOOTER";
const CONFIG_GAME_PHRASE_SINGULAR = "GAME_PHRASE_SINGULAR";
const CONFIG_GAME_PHRASE_PLURAL = "GAME_PHRASE_PLURAL";
const CONFIG_JAM_PHRASE_SINGULAR = "JAM_PHRASE_SINGULAR";
const CONFIG_JAM_PHRASE_PLURAL = "JAM_PHRASE_PLURAL";
const CONFIG_DEVELOPER_PHRASE_SINGULAR = "DEVELOPER_PHRASE_SINGULAR";
const CONFIG_DEVELOPER_PHRASE_PLURAL = "DEVELOPER_PHRASE_PLURAL";
const CONFIG_OVERRIDE_BRAND_LOGO = "OVERRIDE_BRAND_LOGO";
const CONFIG_OVERRIDE_MAIN_LOGO = "OVERRIDE_MAIN_LOGO";
const CONFIG_CAN_SUBMIT_TO_PAST_JAMS = "CAN_SUBMIT_TO_PAST_JAMS";
const CONFIG_DEFAULT_GAME_ICON_URL = "DEFAULT_GAME_ICON_URL";
const CONFIG_DEFAULT_JAM_EVENT_NAME = "DEFAULT_JAM_EVENT_NAME";
const CONFIG_DEFAULT_PERMISSIONS = "DEFAULT_PERMISSIONS";

class ConfigModel{
	public string $Key;
	public string $Value;
	public string $Category;
	public string $Description;
	public bool $Disabled;
	public bool $Editable;
	public bool $Required;
	public string $Type;
	public array $Options = Array();
    public bool $AddedToDictionary;
    public int $RequiredPermissionsRead;
    public int $RequiredPermissionsWrite;
}

class SettingEnumOptionModel{
    public string $Text;
    public string $Value;
}

class ConfigData{
    public array $ConfigModels;

    private ConfigDbInterface $configDbInterface;

    function __construct(ConfigDbInterface &$configDbInterface, MessageService &$messageService) {
        $this->configDbInterface = $configDbInterface;
        $this->ConfigModels = $this->LoadConfig();
        $this->VerifyConfig($messageService);
    }

//////////////////////// MODEL CONSTRUCTOR

    function LoadConfig(): array
    {
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
            $configModel->RequiredPermissionsRead = $configData[DB_COLUMN_CONFIG_REQUIRED_PERMISSION_READ];
            $configModel->RequiredPermissionsWrite = $configData[DB_COLUMN_CONFIG_REQUIRED_PERMISSION_WRITE];

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

    function VerifyConfig(MessageService &$messageService): void
    {
        AddActionLog("VerifyConfig");
        StartTimer("VerifyConfig");
    
        if (!isset($this->ConfigModels[CONFIG_PEPPER]->Value) || strlen($this->ConfigModels[CONFIG_PEPPER]->Value) < 1) {
            $pepper = GenerateSalt();
            $this->UpdateConfig(CONFIG_PEPPER, $pepper, OVERRIDE_AUTOMATIC_NUM);

			$messageService->SendMessage(LogMessage::SystemLogMessage(
				"CONFIG_UPDATED", 
				"Config value edited: ".CONFIG_PEPPER." = '$pepper'", 
				OVERRIDE_AUTOMATIC)
			);
        }
    
        if (!isset($this->ConfigModels[CONFIG_SESSION_PASSWORD_ITERATIONS]->Value) || strlen($this->ConfigModels[CONFIG_SESSION_PASSWORD_ITERATIONS]->Value) < 1) {
            $sessionPasswordIterations = GenerateUserHashIterations($this);
            $this->UpdateConfig(CONFIG_SESSION_PASSWORD_ITERATIONS, $sessionPasswordIterations, OVERRIDE_AUTOMATIC_NUM);

			$messageService->SendMessage(LogMessage::SystemLogMessage(
				"CONFIG_UPDATED", 
				"Config value edited: ".CONFIG_SESSION_PASSWORD_ITERATIONS." = '$sessionPasswordIterations'", 
				OVERRIDE_AUTOMATIC)
			);
        }
    
        StopTimer("VerifyConfig");
    }

    // Saves config to database, does not authorize to ensure VerifyConfig() continues to work
    function UpdateConfig($key, $value, $userId): void
    {
        AddActionLog("UpdateConfig");
        StartTimer("UpdateConfig");

        if($this->ConfigModels[$key]->Value != $value){
            $this->configDbInterface->Update($key, $value, $userId);
            $this->ConfigModels[$key]->Value = $value;
        }
    
        StopTimer("UpdateConfig");
    }

//////////////////////// END DATABASE ACTIONS

//////////////////////// PUBLIC DATA EXPORT

    function GetAllPublicData(): array
    {
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
                case CONFIG_TWITCH_CLIENT_ID: $dataFromDatabase[$i][DB_COLUMN_CONFIG_VALUE] = ""; break;
                case CONFIG_TWITCH_CLIENT_SECRET: $dataFromDatabase[$i][DB_COLUMN_CONFIG_VALUE] = ""; break;
            }
        }

        StopTimer("ConfigData_GetAllPublicData");
        return $dataFromDatabase;
    }

//////////////////////// END PUBLIC DATA EXPORT
}

?>