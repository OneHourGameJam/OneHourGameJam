<?php
//Functions which have to do with reading/writing to the config file.

//Initializes configuration, stores it in the global $config variable.
function LoadConfig(){
	global $config, $dictionary, $uneditableConfigEntries;
	
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
			if(array_search($key, $uneditableConfigEntries) !== FALSE){
				$dictionary["CONFIG"][] = Array("KEY" => $key, "VALUE" => htmlentities($value), "DISABLED" => 1);
			}else{
				$dictionary["CONFIG"][] = Array("KEY" => $key, "VALUE" => htmlentities($value));
			}
			
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
	global $config, $uneditableConfigEntries;
	
	if(!IsAdmin()){
		return;	//Lacks permissions to make edits
	}
	
	if(array_search($key, $uneditableConfigEntries) !== FALSE){
		//Some configuration settings cannot be set via this interface for security reasons.
		return;
	}
	
	$newValue = str_replace("\n", "", $newValue);
	$newValue = str_replace("\r", "", $newValue);
	$newValue = str_replace("|", "", $newValue);
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
		if(count($linePair) == 2){
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