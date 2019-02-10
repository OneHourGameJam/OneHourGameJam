<?php
//Functions which have to do with reading/writing to the config file.

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

//Initializes configuration, stores it in the global $config variable.

function LoadConfig(){
	global $dbConn;

	AddActionLog("LoadConfig");
	StartTimer("LoadConfig");
	$config = Array();

	$sql = " SELECT * FROM config ORDER BY config_id; ";
	$data = mysqli_query($dbConn, $sql);
	$sql = "";

	while($configEntry = mysqli_fetch_array($data)) {
		$key = $configEntry["config_key"];
		$value = $configEntry["config_value"];
		$category = $configEntry["config_category"];
		$description = $configEntry["config_description"];
		$type = $configEntry["config_type"];
		$options = json_decode($configEntry["config_options"], true);
		$editable = $configEntry["config_editable"];
		$required = $configEntry["config_required"];
		$addedToDictionary = $configEntry["config_added_to_dictionary"];

		$configEntry = Array(
			"KEY" => $key,
			"VALUE" => $value,
			"VALUE_HTML_ENCODED" => htmlentities($value),
			"CATEGORY" => $category,
			"DESCRIPTION" => $description,
			"DISABLED" => !$editable,
			"EDITABLE" => $editable,
			"REQUIRED" => $required,
			"TYPE" => $type,
			"ADDED_TO_DICTIONARY" => $addedToDictionary,
		);

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
				foreach($options as $index => $enumOption){
					$configEnumOption = Array(
						"TEXT" => $enumOption["TEXT"],
						"VALUE" => $enumOption["VALUE"]
					);
					if($value == $enumOption["VALUE"]){
						$configEnumOption["ENUM_SELECTED"] = 1;
					}

					$configEntry["ENUM_OPTIONS"][] = $configEnumOption;
				}

			break;
			case "TEXTAREA":
				$configEntry["TYPE_TEXTAREA"] = 1;
			break;
		}

		$config[$key] = $configEntry;
	}

	$config = VerifyConfig($config);

	StopTimer("LoadConfig");
	return $config;
}

function RenderConfig($config){
	global $configCategorySettings;

	AddActionLog("RenderConfig");
	StartTimer("RenderConfig");
	$render = Array("LIST" => Array(), "VALUES" => Array());

	foreach($config as $i => $configEntry){
		$configKey = $configEntry["KEY"];
		$configValue = $configEntry["VALUE"];
		$category = $configEntry["CATEGORY"];
		$configCategoryHeader = $configCategorySettings[$category];

		$render["VALUES"][$configKey] = $configValue;

		$categoryIndex = count($render["LIST"]);
		foreach($render["LIST"] as $index => $configDictionaryEntry){
			if($configDictionaryEntry["CATEGORY_ID"] == $category){
				$categoryIndex = $index;
			}
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
	if (!isset($config["PEPPER"]["VALUE"]) || strlen($config["PEPPER"]["VALUE"]) < 1) {
		$config = UpdateConfig($config, "PEPPER", GenerateSalt(), -1);
	}

	if (!isset($config["SESSION_PASSWORD_ITERATIONS"]["VALUE"]) || strlen($config["SESSION_PASSWORD_ITERATIONS"]["VALUE"]) < 1) {
		$sessionPasswordIterations = GenerateUserHashIterations($config);
		$config = UpdateConfig($config, "SESSION_PASSWORD_ITERATIONS", $sessionPasswordIterations, -1);
	}

	StopTimer("VerifyConfig");
	return $config;
}


// Saves config to database, does not authorize to ensure VerifyConfig() continues to work
function UpdateConfig($config, $key, $value, $userID) {
	global $dbConn;

	AddActionLog("UpdateConfig");
	StartTimer("UpdateConfig");

	if($config[$key]["VALUE"] != $value){
		$userIDClean = mysqli_real_escape_string($dbConn, $userID);
		$keyClean = mysqli_real_escape_string($dbConn, $key);
		$valueClean = mysqli_real_escape_string($dbConn, $value);

		$config[$key]["VALUE"] = $value;
		$sql = "
			UPDATE config
			SET config_value = '$valueClean',
			config_lastedited = Now(),
			config_lasteditedby = '$userIDClean'
			WHERE config_key = '$keyClean';
		";
		mysqli_query($dbConn, $sql);
    	$sql = "";

		AddToAdminLog("CONFIG_UPDATED", "Config value edited: $key = '$value'", "");
	}

	StopTimer("UpdateConfig");
	return $config;
}


function RedirectToHttpsIfRequired($config){
	AddActionLog("RedirectToHttpsIfRequired");
	StartTimer("RedirectToHttpsIfRequired");
    if($config["REDIRECT_TO_HTTPS"]["VALUE"]){
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
