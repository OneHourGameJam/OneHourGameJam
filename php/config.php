<?php

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

function RenderConfig($configData){
	global $configCategorySettings, $configPrettyPrintFunctions;
	AddActionLog("RenderConfig");
	StartTimer("RenderConfig");

	$render = Array("LIST" => Array(), "VALUES" => Array(), "PRETTY_PRINT" => Array());

	foreach($configData->ConfigModels as $i => $configModel){
		$addedToDictionary = $configModel->AddedToDictionary;

		if(!$addedToDictionary){
			continue;
		}

		$configKey = $configModel->Key;
		$configValue = $configModel->Value;
		$category = $configModel->Category;
		$type = $configModel->Type;
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
		$configEntry["KEY"] = $configModel->Key;
		$configEntry["VALUE"] = $configModel->Value;
		$configEntry["VALUE_HTML_ENCODED"] = htmlentities($configModel->Value);
		$configEntry["CATEGORY"] = $category;
		$configEntry["DESCRIPTION"] = $configModel->Description;
		$configEntry["DISABLED"] = $configModel->Disabled;
		$configEntry["EDITABLE"] = $configModel->Editable;
		$configEntry["REQUIRED"] = $configModel->Required;
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
				foreach($configModel->Options as $index => $enumOption){
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


function RedirectToHttpsIfRequired($configData){
	AddActionLog("RedirectToHttpsIfRequired");
	StartTimer("RedirectToHttpsIfRequired");

    if($configData->ConfigModels["REDIRECT_TO_HTTPS"]->Value){
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
