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
	CONFIG_MAX_SCREENSHOT_FILE_SIZE_IN_BYTES => function($value){ return bytesToString($value); },
	CONFIG_MAX_ASSET_FILE_SIZE_IN_BYTES => function($value){ return bytesToString($value); },
);

class ConfigurationPresenter{
	
	public static function RenderConfig($configData){
		global $configCategorySettings, $configPrettyPrintFunctions;
		AddActionLog("RenderConfig");
		StartTimer("RenderConfig");

		$configurationViewModel = new ConfigurationViewModel();

		foreach($configData->ConfigModels as $i => $configModel){
			$addedToDictionary = $configModel->AddedToDictionary;
			
			$configKey = $configModel->Key;
			$configValue = $configModel->Value;
			$category = $configModel->Category;
			$type = $configModel->Type;
			$configCategoryHeader = $configCategorySettings[$category];

			//Raw value
			$configurationViewModel->VALUES[$configKey] = $configValue;

			if(!$addedToDictionary){
				//Pretty Print
				$configPrettyPrint = $configValue;
				if(isset($configPrettyPrintFunctions[$configKey])){
					$configPrettyPrint = $configPrettyPrintFunctions[$configKey]($configValue);
				}

				$configurationViewModel->PRETTY_PRINT[$configKey] = $configPrettyPrint;
			}

			$categoryIndex = count($configurationViewModel->LIST);
			foreach($configurationViewModel->LIST as $index => $configDictionaryEntry){
				if($configDictionaryEntry->CATEGORY_ID == $category){
					$categoryIndex = $index;
				}
			}

			$settingViewModel = new SettingViewModel();
			$settingViewModel->KEY = $configModel->Key;
			$settingViewModel->VALUE = $configModel->Value;
			$settingViewModel->VALUE_HTML_ENCODED = htmlentities($configModel->Value);
			$settingViewModel->CATEGORY = $category;
			$settingViewModel->DESCRIPTION = $configModel->Description;
			$settingViewModel->DISABLED = $configModel->Disabled;
			$settingViewModel->EDITABLE = $configModel->Editable;
			$settingViewModel->REQUIRED = $configModel->Required;
			$settingViewModel->TYPE = $type;

			switch($type) {
				case "TEXT":
					$settingViewModel->TYPE_TEXT = 1;
				break;
				case "NUMBER":
					$settingViewModel->TYPE_NUMBER = 1;
				break;
				case "ENUM":
					$settingViewModel->TYPE_ENUM = 1;
					$settingViewModel->ENUM_OPTIONS = Array();
					foreach($configModel->Options as $index => $settingEnumOptionModel){
						$settingEnumOptionViewModel = new SettingEnumOptionViewModel();
						$settingEnumOptionViewModel->TEXT = $settingEnumOptionModel->Text;
						$settingEnumOptionViewModel->VALUE = $settingEnumOptionModel->Value;

						if($configValue == $settingEnumOptionViewModel->VALUE){
							$settingEnumOptionViewModel->ENUM_SELECTED = 1;
						}

						$settingViewModel->ENUM_OPTIONS[] = $settingEnumOptionViewModel;
					}

				break;
				case "TEXTAREA":
					$settingViewModel->TYPE_TEXTAREA = 1;
				break;
			}

			if(!isset($configurationViewModel->LIST[$categoryIndex])){
				$configurationViewModel->LIST[$categoryIndex] = new SettingGroupViewModel();
				$configurationViewModel->LIST[$categoryIndex]->CATEGORY_ID = $category;
				$configurationViewModel->LIST[$categoryIndex]->CATEGORY_HEADER = $configCategoryHeader;
			}

			$configurationViewModel->LIST[$categoryIndex]->ENTRIES[] = $settingViewModel;
		}

		if($configurationViewModel->VALUES[CONFIG_OVERRIDE_BRAND_LOGO] != ""){
			$configurationViewModel->has_custom_brand_logo = 1;
		}
		if($configurationViewModel->VALUES[CONFIG_OVERRIDE_MAIN_LOGO] != ""){
			$configurationViewModel->has_custom_main_logo = 1;
		}

		StopTimer("RenderConfig");
		return $configurationViewModel;
	}

}

?>