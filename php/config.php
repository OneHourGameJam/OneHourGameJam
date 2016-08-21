<?php
//Functions which have to do with reading/writing to the config file.

$configSettings = Array(
	"LANG_JAMNAME" => Array(
		"TAG" => "LANG_JAMNAME",
		"NAME" => "Jam name, displayed in the page header",
		"TYPE" => "TEXT",
		"EDITABLE" => TRUE,
		"REQUIRED" => TRUE
	),
	"LANG_JAMDESC2" => Array(
		"TAG" => "LANG_JAMDESC2",
		"NAME" => "Jam description, line 2, displayed in the page header",
		"TYPE" => "TEXT",
		"EDITABLE" => TRUE,
		"REQUIRED" => TRUE
	),
	"LANG_JAMDESC3" => Array(
		"TAG" => "LANG_JAMDESC3",
		"NAME" => "Jam description, line 3, displayed in the page header",
		"TYPE" => "TEXT",
		"EDITABLE" => TRUE,
		"REQUIRED" => TRUE
	),
	"LANG_NOTIFICATION" => Array(
		"TAG" => "LANG_NOTIFICATION",
		"NAME" => "An optional notification area, displayed at the top of the page if set.",
		"TYPE" => "TEXT",
		"EDITABLE" => TRUE,
		"REQUIRED" => FALSE
	),
	"LANG_RULES" => Array(
		"TAG" => "LANG_RULES",
		"NAME" => "Jam rules, displayed on the rules page",
		"TYPE" => "TEXTAREA",
		"EDITABLE" => TRUE,
		"REQUIRED" => TRUE
	),
	"THEME_DAYS_MARK_AS_OLD" => Array(
		"TAG" => "THEME_DAYS_MARK_AS_OLD",
		"NAME" => "How many days a theme can be on the list before it is marked as old.",
		"TYPE" => "NUMBER",
		"EDITABLE" => TRUE,
		"REQUIRED" => TRUE
	),
	"THEME_MIN_VOTES_TO_SCORE" => Array(
		"TAG" => "THEME_MIN_VOTES_TO_SCORE",
		"NAME" => "Minimum number of votes a theme must receive for it to be considered rated.",
		"TYPE" => "NUMBER",
		"EDITABLE" => TRUE,
		"REQUIRED" => TRUE
	),
	"THEME_NUMBER_TO_MARK_TOP" => Array(
		"TAG" => "THEME_NUMBER_TO_MARK_TOP",
		"NAME" => "Number of best voted themes to mark as \"top\".",
		"TYPE" => "NUMBER",
		"EDITABLE" => TRUE,
		"REQUIRED" => TRUE
	),
	"THEME_NUMBER_TO_MARK_KEEP" => Array(
		"TAG" => "THEME_NUMBER_TO_MARK_KEEP",
		"NAME" => "Number of best voted themes to keep for the next jam.",
		"TYPE" => "NUMBER",
		"EDITABLE" => TRUE,
		"REQUIRED" => TRUE
	),
	"PEPPER" => Array(
		"TAG" => "PEPPER",
		"NAME" => "Sitewide Pepper (used in password hashing), for security reasons this can only be changed manually in the config file.",
		"TYPE" => "TEXT",
		"EDITABLE" => FALSE,
		"REQUIRED" => TRUE
	),
	"SESSION_PASSWORD_ITERATIONS" => Array(
		"TAG" => "SESSION_PASSWORD_ITERATIONS",
		"NAME" => "Number of hashing iteraations for session IDs, for security reasons this can only be changed manually in the config file.",
		"TYPE" => "NUMBER",
		"EDITABLE" => FALSE,
		"REQUIRED" => TRUE
	)
);

//Initializes configuration, stores it in the global $config variable.
function LoadConfig(){
	global $config, $dictionary, $configSettings;
	
	$config = Array();	//Clear any existing configuration.
	$dictionary["CONFIG"] = Array();	//Clear any config entries in the dictionary
	
	$configTxt = file_get_contents("config/config.txt");
	$lines = explode("\n", $configTxt);
	$linesUpdated = Array();
	foreach($lines as $i => $line){
		$line = trim($line);
		if(StartsWith($line, "#")){
			//Comment
			continue;
		}
		$linePair = explode("|", $line);
		
		if(count($linePair) > 2){
			//Value includes | delimiter, merge value together into a single string.
			$key = $linePair[0];
			$properValue = $linePair[1];
			for($j = 2; $j < count($linePair); $j++){
				$properValue .= "|" . $linePair[$j];
			}
			$linePair = Array();
			$linePair[] = $key;
			$linePair[] = $properValue;
		}
		
		if(count($linePair) == 2){
			//key-value pair
			$key = trim($linePair[0]);
			$value = trim($linePair[1]);
			$config[$key] = $value;
			
			//Store marked config entries into the site dictionary for use in templates.
			if(StartsWith($key, "LANG_")){
				$dictKey = str_replace("LANG_", "CONFIG_", $key);
				$dictionary[$dictKey] = $value;
			}
			
			//Store key-value pairs in the CONFIG part of the dictionary.
			$configEntry = Array("KEY" => $key, "VALUE" => htmlentities($value), "NAME" => $configSettings[$key]["NAME"]);
			if($configSettings[$key]["EDITABLE"] == FALSE){
				$configEntry["DISABLED"] = 1;
			}
			if($configSettings[$key]["REQUIRED"] == TRUE){
				$configEntry["REQUIRED"] = 1;
			}
			switch($configSettings[$key]["TYPE"]){
				case "TEXT":
					$configEntry["TYPE_TEXT"] = 1;
				break;
				case "NUMBER":
					$configEntry["TYPE_NUMBER"] = 1;
				break;
				case "TEXTAREA":
					$configEntry["TYPE_TEXTAREA"] = 1;
				break;
			}
			
			$dictionary["CONFIG"][] = $configEntry;
			
			//Validate line
			switch($key){
				case "PEPPER":
					if(strlen($value) < 1){
						//Generate pepper if none exists (first time site launch).
						$config[$key] = GenerateSalt();
						$lines[$i] = "$key | ".$config[$key];
						file_put_contents("config/config.txt", implode("\n", $lines));
					}
				break;
				case "SESSION_PASSWORD_ITERATIONS":
					if(strlen($value) < 1){
						//Generate pepper if none exists (first time site launch).
						$config[$key] = rand(10000, 20000);
						$lines[$i] = "$key | ".$config[$key];
						file_put_contents("config/config.txt", implode("\n", $lines));
					}else{
						$config[$key] = intval($value);
					}
				break;
				default:
					$linesUpdated[] = $line;
				break;
			}
		}
	}
}

//Updates a given key's entry in the config file if it differs from the current one.
//Disallowed characters in the new value: vertical line (|), \n and \r
function SaveConfig($key, $newValue){
	global $config, $configSettings;
	
	if(!IsAdmin()){
		return;	//Lacks permissions to make edits
	}
	
	if($configSettings[$key]["EDITABLE"] == FALSE){
		//Some configuration settings cannot be set via this interface for security reasons.
		return;
	}
	
	$newValue = str_replace("\n", "", $newValue);
	$newValue = str_replace("\r", "", $newValue);
	$newValue = trim($newValue);
	
	$configTxt = file_get_contents("config/config.txt");
	$lines = explode("\n", $configTxt);
	$linesUpdated = Array();
	foreach($lines as $i => $line){
		$line = trim($line);
		if(StartsWith($line, "#")){
			//Comment
			continue;
		}
		$linePair = explode("|", $line);
		if(count($linePair) >= 2){
			//key-value pair
			$currentKey = trim($linePair[0]);
			$value = trim($linePair[1]);
			
			if($key == $currentKey){
				if($value != $newValue){
					$lines[$i] = $key." | ".$newValue;
					file_put_contents("config/config.txt", implode("\n", $lines));
				}
				return;
			}
		}
	}
}










?>