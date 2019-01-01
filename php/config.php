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
);

//Initializes configuration, stores it in the global $config variable.

function LoadConfig(){
	global $config, $dictionary, $configCategorySettings, $dbConn;

	$config = Array();	//Clear any existing configuration.
	$dictionary["CONFIG"] = Array();	//Clear any config entries in the dictionary

	//Fill list of themes - will return same row multiple times (once for each valid themevote_type)
	$sql = " SELECT * FROM config ORDER BY config_id; ";
	$data = mysqli_query($dbConn, $sql);
	$sql = "";

	//Fill dictionary with non-banned themes
	while($configEntry = mysqli_fetch_array($data)) {
		$baseKey = $configEntry["config_key"];
		$value = $configEntry["config_value"];
		$category = $configEntry["config_category"];
		$description = $configEntry["config_description"];
		$type = $configEntry["config_type"];
		$options = json_decode($configEntry["config_options"], true);
		$editable = $configEntry["config_editable"];
		$required = $configEntry["config_required"];
		$addedToDictionary = $configEntry["config_added_to_dictionary"];

		$key = "CONFIG_" . $baseKey;

		$config[$baseKey] = $value;

		if ($addedToDictionary) {
			$dictionary[$key] = $value;
		}

		$configCategoryHeader = $configCategorySettings[$category];

		$configEntry = Array(
			"KEY" => $key,
			"VALUE" => htmlentities($value),
			"NAME" => $description,
			"DISABLED" => !$editable,
			"EDITABLE" => $editable,
			"REQUIRED" => $required,
			"TYPE" => $type,
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

		$i = count($dictionary["CONFIG"]);
		foreach($dictionary["CONFIG"] as $index => $configDictionaryEntry){
			if($configDictionaryEntry["CATEGORY_ID"] == $category){
				$i = $index;
			}
		}

		$dictionary["CONFIG"][$i]["CATEGORY_ID"] = $category;
		$dictionary["CONFIG"][$i]["CATEGORY_HEADER"] = $configCategoryHeader;
		$dictionary["CONFIG"][$i]["ENTRIES"][] = $configEntry;
	}

	// print_r($config);
	// print_r($dictionary);

    VerifyConfig();
    RedirectToHttpsIfRequired();
}

function VerifyConfig() {
	global $config;

	if (!isset($config["PEPPER"]) || strlen($config["PEPPER"]) < 1) {
		UpdateConfig("PEPPER", GenerateSalt(), -1);
	}

	if (!isset($config["SESSION_PASSWORD_ITERATIONS"]) || strlen($config["SESSION_PASSWORD_ITERATIONS"]) < 1) {
		UpdateConfig("SESSION_PASSWORD_ITERATIONS", rand(10000, 20000), -1);
	}
}


// Actually updates the config. Doesn't check auth.
function UpdateConfig($key, $value, $userID) {
	global $config, $dbConn;

	if(!IsAdmin()){
		return; //Lacks permissions to make edits
	}

	$keyClean = mysqli_real_escape_string($dbConn, $key);
	$valueClean = mysqli_real_escape_string($dbConn, $value);

	$config[$key] = $value;
	$sql = "
		UPDATE config
		SET config_value = '$valueClean',
		config_lastedited = Now(),
		config_lasteditedby = '$userID'
		WHERE config_key = '$keyClean';
	";
	mysqli_query($dbConn, $sql);
    $sql = "";
    
    AddToAdminLog("CONFIG_UPDATED", "Config value edited: $key = '$value'", "");
}


function RedirectToHttpsIfRequired(){
    global $config;

    if($config["REDIRECT_TO_HTTPS"]){
        if(!isset($_SERVER['HTTPS'])){
        	//Redirect to https
            $url = "https://". $_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"];
            header("HTTP/1.1 301 Moved Permanently"); 
            header("Location: $url"); 
            die();
        }
    }
}








?>
