<?php
//Functions which have to do with reading/writing to the config file.

class Config{
	public $Key;
	public $Value;
	public $Category;
	public $Description;
	public $Disabled;
	public $Editable;
	public $Required;
	public $Type;
	public $Options;
	public $AddedToDictionary;
}

$configCategorySettings = Array(
	"JAM_SETTINGS" => "General Jam Settings",
	"STREAM" => "Stream",
	"NOTIFICATION" => "Notification (optional)",
	"RULES" => "Jam Rules",
	"THEME_SELECTION" => "Theme Selection",
	"SATISFACTION" => "Satisfaction",
	"SECURITY" => "Security",
	"SOCIAL_MEDIA" => "Social Media",
	"ANALYTICS" => "Analytics",
	"NEW_JAM_DEFAULTS" => "New Jam Defaults",
	"ADMIN_SUGGESTIONS" => "Admin Suggestions",
    "USERS" => "Users",
    "SYSTEM" => "System",
    "ASSETS" => "Assets",
);

$configPrettyPrintFunctions = Array(
	"MAX_SCREENSHOT_FILE_SIZE_IN_BYTES" => function($value){ return bytesToString($value); },
	"MAX_ASSET_FILE_SIZE_IN_BYTES" => function($value){ return bytesToString($value); },
);

//Initializes configuration, stores it in the global $config variable.

function LoadConfig(){
	global $dbConn;
	AddActionLog("LoadConfig");
	StartTimer("LoadConfig");

	$config = Array();

	$sql = " SELECT * FROM config ORDER BY config_id; ";
	$data = mysqli_query($dbConn, $sql);
	$sql = "";

	while($configData = mysqli_fetch_array($data)) {
		$key = $configData["config_key"];
		$value = $configData["config_value"];
		$category = $configData["config_category"];
		$description = $configData["config_description"];
		$type = $configData["config_type"];
		$options = json_decode($configData["config_options"], true);
		$editable = $configData["config_editable"];
		$required = $configData["config_required"];
		$addedToDictionary = $configData["config_added_to_dictionary"];

		$configEntry = new Config();
		$configEntry->Key = $key;
		$configEntry->Value = $value;
		$configEntry->Category = $category;
		$configEntry->Description = $description;
		$configEntry->Disabled = !$editable;
		$configEntry->Editable = $editable;
		$configEntry->Required = $required;
		$configEntry->Type = $type;
		$configEntry->Options = $options;
		$configEntry->AddedToDictionary = $addedToDictionary;

		$config[$key] = $configEntry;
	}

	$config = VerifyConfig($config);

	StopTimer("LoadConfig");
	return $config;
}

function RenderConfig($config){
	global $configCategorySettings, $configPrettyPrintFunctions;
	AddActionLog("RenderConfig");
	StartTimer("RenderConfig");

	$render = Array("LIST" => Array(), "VALUES" => Array(), "PRETTY_PRINT" => Array());

	foreach($config as $i => $configData){
		$addedToDictionary = $configData->AddedToDictionary;

		if(!$addedToDictionary){
			continue;
		}

		$configKey = $configData->Key;
		$configValue = $configData->Value;
		$category = $configData->Category;
		$type = $configData->Type;
		$configCategoryHeader = $configCategorySettings[$category];

		//Raw value
		$render["VALUES"][$configKey] = $configValue;

		//Pretty Print
		$configPrettyPrint = $configValue;
		if(isset($configPrettyPrintFunctions[$configKey])){
			$configPrettyPrint = $configPrettyPrintFunctions[$configKey]($configValue);
		}
		$render["PRETTY_PRINT"][$configKey] = $configPrettyPrint;

		$categoryIndex = count($render["LIST"]);
		foreach($render["LIST"] as $index => $configDictionaryEntry){
			if($configDictionaryEntry["CATEGORY_ID"] == $category){
				$categoryIndex = $index;
			}
		}

		$configEntry = Array();
		$configEntry["KEY"] = $configData->Key;
		$configEntry["VALUE"] = $configData->Value;
		$configEntry["VALUE_HTML_ENCODED"] = htmlentities($configData->Value);
		$configEntry["CATEGORY"] = $category;
		$configEntry["DESCRIPTION"] = $configData->Description;
		$configEntry["DISABLED"] = $configData->Disabled;
		$configEntry["EDITABLE"] = $configData->Editable;
		$configEntry["REQUIRED"] = $configData->Required;
		$configEntry["TYPE"] = $type;

		switch($type) {
			case "TEXT":
				$configEntry["TYPE_TEXT"] = 1;
			break;
			case "NUMBER":
				$configEntry["TYPE_NUMBER"] = 1;
			break;
			case "ENUM":
				$configEntry["TYPE_ENUM"] = 1;
				$configEntry["ENUM_OPTIONS"] = Array();
				foreach($configData->Options as $index => $enumOption){
					$configEnumOption = Array(
						"TEXT" => $enumOption["TEXT"],
						"VALUE" => $enumOption["VALUE"]
					);
					if($configValue == $enumOption["VALUE"]){
						$configEnumOption["ENUM_SELECTED"] = 1;
					}

					$configEntry["ENUM_OPTIONS"][] = $configEnumOption;
				}

			break;
			case "TEXTAREA":
				$configEntry["TYPE_TEXTAREA"] = 1;
			break;
		}

		$render["LIST"][$categoryIndex]["CATEGORY_ID"] = $category;
		$render["LIST"][$categoryIndex]["CATEGORY_HEADER"] = $configCategoryHeader;
		$render["LIST"][$categoryIndex]["ENTRIES"][] = $configEntry;
	}

	StopTimer("RenderConfig");
	return $render;
}


function VerifyConfig($config) {
	AddActionLog("VerifyConfig");
	StartTimer("VerifyConfig");

	if (!isset($config["PEPPER"]->Value) || strlen($config["PEPPER"]->Value) < 1) {
		$config = UpdateConfig($config, "PEPPER", GenerateSalt(), -1, "AUTOMATIC");
	}

	if (!isset($config["SESSION_PASSWORD_ITERATIONS"]->Value) || strlen($config["SESSION_PASSWORD_ITERATIONS"]->Value) < 1) {
		$sessionPasswordIterations = GenerateUserHashIterations($config);
		$config = UpdateConfig($config, "SESSION_PASSWORD_ITERATIONS", $sessionPasswordIterations, -1, "AUTOMATIC");
	}

	StopTimer("VerifyConfig");
	return $config;
}


// Saves config to database, does not authorize to ensure VerifyConfig() continues to work
function UpdateConfig($config, $key, $value, $userID, $userUsername) {
	global $dbConn;
	AddActionLog("UpdateConfig");
	StartTimer("UpdateConfig");

	if($config[$key]->Value != $value){
		$userIDClean = mysqli_real_escape_string($dbConn, $userID);
		$keyClean = mysqli_real_escape_string($dbConn, $key);
		$valueClean = mysqli_real_escape_string($dbConn, $value);

		$config[$key]->Value = $value;
		$sql = "
			UPDATE config
			SET config_value = '$valueClean',
			config_lastedited = Now(),
			config_lasteditedby = '$userIDClean'
			WHERE config_key = '$keyClean';
		";
		mysqli_query($dbConn, $sql);
    	$sql = "";

		AddToAdminLog("CONFIG_UPDATED", "Config value edited: $key = '$value'", "", $userUsername);
	}

	StopTimer("UpdateConfig");
	return $config;
}


function RedirectToHttpsIfRequired($config){
	AddActionLog("RedirectToHttpsIfRequired");
	StartTimer("RedirectToHttpsIfRequired");

    if($config["REDIRECT_TO_HTTPS"]->Value){
        if(!isset($_SERVER['HTTPS'])){
        	//Redirect to https
            $url = "https://". $_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"];
            header("HTTP/1.1 301 Moved Permanently");
            header("Location: $url");
            die();
        }
    }
	StopTimer("RedirectToHttpsIfRequired");
}








?>
