<?php

BeforeInit();	//Plugin hook
Init();
AfterInit();	//Plugin hook


//Initializes the site.
function Init(){
	function LoadConfig(){
		global $config, $dictionary;
		
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
	
	LoadConfig();
	LoadUsers();
}

?>