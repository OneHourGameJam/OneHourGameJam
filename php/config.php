<?php
//Functions which have to do with reading/writing to the config file.

$configSettings = Array(
	"JAM_SETTINGS" => Array(
		"HEADER" => "General Jam Settings",
		"SETTINGS" => Array(
			"LANG_JAMNAME" => Array(
				"TAG" => "LANG_JAMNAME",
				"NAME" => "Jam name, displayed in the page header",
				"TYPE" => "TEXT",
				"EDITABLE" => TRUE,
				"REQUIRED" => TRUE
			),
			"LANG_JAMDESC" => Array(
				"TAG" => "LANG_JAMDESC",
				"NAME" => "Jam description, displayed in the page header",
				"TYPE" => "TEXT",
				"EDITABLE" => TRUE,
				"REQUIRED" => TRUE
			),
			"LANG_JAM_DAY" => Array(
				"TAG" => "LANG_JAM_DAY",
				"NAME" => "Jam start day of the week",
				"TYPE" => "ENUM",
				"ENUM_OPTIONS" => Array(
					Array("VALUE" => 0, "TEXT" => "Sunday"),
					Array("VALUE" => 1, "TEXT" => "Monday"),
					Array("VALUE" => 2, "TEXT" => "Tuesday"),
					Array("VALUE" => 3, "TEXT" => "Wednesday"),
					Array("VALUE" => 4, "TEXT" => "Thursday"),
					Array("VALUE" => 5, "TEXT" => "Friday"),
					Array("VALUE" => 6, "TEXT" => "Saturday")
				),
				"EDITABLE" => TRUE,
				"REQUIRED" => TRUE
			),
			"LANG_JAM_TIME" => Array(
				"TAG" => "LANG_JAM_DAY",
				"NAME" => "The hour the jam starts on",
				"TYPE" => "ENUM",
				"ENUM_OPTIONS" => Array(
					Array("VALUE" => 24, "TEXT" => "Midnight"),
					Array("VALUE" => 23, "TEXT" => "23:00"),
					Array("VALUE" => 22, "TEXT" => "22:00"),
					Array("VALUE" => 21, "TEXT" => "21:00"),
					Array("VALUE" => 20, "TEXT" => "20:00"),
					Array("VALUE" => 19, "TEXT" => "19:00"),
					Array("VALUE" => 18, "TEXT" => "18:00"),
					Array("VALUE" => 17, "TEXT" => "17:00"),
					Array("VALUE" => 16, "TEXT" => "16:00"),
					Array("VALUE" => 15, "TEXT" => "15:00"),
					Array("VALUE" => 14, "TEXT" => "14:00"),
					Array("VALUE" => 13, "TEXT" => "13:00"),
					Array("VALUE" => 12, "TEXT" => "12:00"),
					Array("VALUE" => 11, "TEXT" => "11:00"),
					Array("VALUE" => 10, "TEXT" => "10:00"),
					Array("VALUE" => 9, "TEXT" => "9:00"),
					Array("VALUE" => 8, "TEXT" => "8:00"),
					Array("VALUE" => 7, "TEXT" => "7:00"),
					Array("VALUE" => 6, "TEXT" => "6:00"),
					Array("VALUE" => 5, "TEXT" => "5:00"),
					Array("VALUE" => 4, "TEXT" => "4:00"),
					Array("VALUE" => 3, "TEXT" => "3:00"),
					Array("VALUE" => 2, "TEXT" => "2:00"),
					Array("VALUE" => 1, "TEXT" => "1:00"),
				),
				"EDITABLE" => TRUE,
				"REQUIRED" => TRUE
			)
		)
	),
	"STREAM" => Array(
		"HEADER" => "Stream",
		"SETTINGS" => Array(
			"STREAMER_TWITCH_NAME" => Array(
				"TAG" => "STREAMER_TWITCH_NAME",
				"NAME" => "Twitch name for the streamer for this jam",
				"TYPE" => "TEXT",
				"EDITABLE" => TRUE,
				"REQUIRED" => TRUE
			),
			"TWITCH_CLIENT_ID" => Array(
				"TAG" => "TWITCH_CLIENT_ID",
				"NAME" => "Twitch client id for the API",
				"TYPE" => "TEXT",
				"EDITABLE" => FALSE,
				"REQUIRED" => TRUE
			),
			"TWITCH_API_STREAM_UPDATE_FREQUENCY" => Array(
				"TAG" => "TWITCH_API_STREAM_UPDATE_FREQUENCY",
				"NAME" => "The minimum number of seconds that have to pass between subsequent checks as to whether the stream is online on Twitch or not.",
				"TYPE" => "NUMBER",
				"EDITABLE" => TRUE,
				"REQUIRED" => TRUE
			)
		)
	),
	"NOTIFICATION" => Array(
		"HEADER" => "Notification (optional)",
		"SETTINGS" => Array(
			"LANG_NOTIFICATION" => Array(
				"TAG" => "LANG_NOTIFICATION",
				"NAME" => "Notification text",
				"TYPE" => "TEXT",
				"EDITABLE" => TRUE,
				"REQUIRED" => FALSE
			),
			"LANG_NOTIFICATION_IMAGE" => Array(
				"TAG" => "LANG_NOTIFICATION_IMAGE",
				"NAME" => "Notification Image URL",
				"TYPE" => "TEXT",
				"EDITABLE" => TRUE,
				"REQUIRED" => FALSE
			),
			"LANG_NOTIFICATION_URL" => Array(
				"TAG" => "LANG_NOTIFICATION_URL",
				"NAME" => "Notification Link URL",
				"TYPE" => "TEXT",
				"EDITABLE" => TRUE,
				"REQUIRED" => FALSE
			)
		)
	),
	"RULES" => Array(
		"HEADER" => "Jam Rules",
		"SETTINGS" => Array(
			"LANG_RULES" => Array(
				"TAG" => "LANG_RULES",
				"NAME" => "Jam rules, displayed on the rules page",
				"TYPE" => "TEXTAREA",
				"EDITABLE" => TRUE,
				"REQUIRED" => TRUE
			),
		)
	),
	"THEME_SELECTION" => Array(
		"HEADER" => "Theme Selection",
		"SETTINGS" => Array(
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
			"JAMS_CONSIDERED_RECENT" => Array(
				"TAG" => "JAMS_CONSIDERED_RECENT",
				"NAME" => "Number of jams which are considered 'recent' when calculating recent jam participation.",
				"TYPE" => "NUMBER",
				"EDITABLE" => TRUE,
				"REQUIRED" => TRUE
			)
		)
	),
	"SATISFACTION" => Array(
		"HEADER" => "Satisfaction",
		"SETTINGS" => Array(
			"SATISFACTION_RATINGS_TO_SHOW_SCORE" => Array(
				"TAG" => "SATISFACTION_RATINGS_TO_SHOW_SCORE",
				"NAME" => "Total number of satisfaction ratings needed for them to become publicly visible.",
				"TYPE" => "NUMBER",
				"EDITABLE" => TRUE,
				"REQUIRED" => TRUE
			),
		)
	),
	"SECURITY" => Array(
		"HEADER" => "Security",
		"SETTINGS" => Array(
			"PEPPER" => Array(
				"TAG" => "PEPPER",
				"NAME" => "Sitewide Pepper (used in password hashing), for security reasons this can only be changed manually in the config file.",
				"TYPE" => "TEXT",
				"EDITABLE" => FALSE,
				"REQUIRED" => TRUE
			),
			"SESSION_PASSWORD_ITERATIONS" => Array(
				"TAG" => "SESSION_PASSWORD_ITERATIONS",
				"NAME" => "Number of hashing iterations for session IDs, for security reasons this can only be changed manually in the config file.",
				"TYPE" => "NUMBER",
				"EDITABLE" => FALSE,
				"REQUIRED" => TRUE
			)
		)
	),
	"SOCIAL_MEDIA" => Array(
		"HEADER" => "Social Media",
		"SETTINGS" => Array(
			"LANG_TWITTER_ACCOUNT" => Array(
				"TAG" => "LANG_TWITTER_ACCOUNT",
				"NAME" => "Game Jam's twitter account, appears in the left menu.",
				"TYPE" => "TEXT",
				"EDITABLE" => TRUE,
				"REQUIRED" => FALSE
			),
			"LANG_TWITCH_ACCOUNT" => Array(
				"TAG" => "LANG_TWITCH_ACCOUNT",
				"NAME" => "Game Jam's twitch account, appears in the left menu.",
				"TYPE" => "TEXT",
				"EDITABLE" => TRUE,
				"REQUIRED" => FALSE
			),
			"LANG_IRC_ADDRESS" => Array(
				"TAG" => "LANG_IRC_ADDRESS",
				"NAME" => "IRC address",
				"TYPE" => "TEXT",
				"EDITABLE" => TRUE,
				"REQUIRED" => FALSE
			),
			"LANG_IRC_CHANNEL" => Array(
				"TAG" => "LANG_IRC_CHANNEL",
				"NAME" => "IRC Channel",
				"TYPE" => "TEXT",
				"EDITABLE" => TRUE,
				"REQUIRED" => FALSE
			),
			"LANG_IRC_CHAT_IN_BROWSER" => Array(
				"TAG" => "LANG_IRC_CHAT_IN_BROWSER",
				"NAME" => "IRC Chat in browser URL",
				"TYPE" => "TEXT",
				"EDITABLE" => TRUE,
				"REQUIRED" => FALSE
			),
			"LANG_DISCORD_INVITE_URL" => Array(
				"TAG" => "LANG_DISCORD_INVITE_URL",
				"NAME" => "Discord invite URL",
				"TYPE" => "TEXT",
				"EDITABLE" => TRUE,
				"REQUIRED" => FALSE
			),
		)
	),
	"ANALYTICS" => Array(
		"HEADER" => "Analytics",
		"SETTINGS" => Array(
			"GOOGLE_ANALYTICS_CODE" => Array(
				"TAG" => "GOOGLE_ANALYTICS_CODE",
				"NAME" => "Google Analytics code for site. Will look something like this: UA-00000000-1. If blank, Google analytics code will not even be inserted.",
				"TYPE" => "TEXT",
				"EDITABLE" => FALSE,
				"REQUIRED" => FALSE
			),
		)
	),
	"NEW_JAM_DEFAULTS" => Array(
		"HEADER" => "New Jam Defaults",
		"SETTINGS" => Array(
			"LANG_DEFAULT_NUMBER_OF_COLORS" => Array(
				"TAG" => "LANG_DEFAULT_NUMBER_OF_COLORS",
				"NAME" => "Default number of colors (0..16)",
				"TYPE" => "NUMBER",
				"EDITABLE" => true,
				"REQUIRED" => true
			),
			"LANG_DEFAULT_SATURATION" => Array(
				"TAG" => "LANG_DEFAULT_SATURATION",
				"NAME" => "Default saturation (0..255)",
				"TYPE" => "NUMBER",
				"EDITABLE" => true,
				"REQUIRED" => true
			),
			"LANG_DEFAULT_BRIGHTNESS" => Array(
				"TAG" => "LANG_DEFAULT_BRIGHTNESS",
				"NAME" => "Default brightness (0..255)",
				"TYPE" => "NUMBER",
				"EDITABLE" => true,
				"REQUIRED" => true
			),
			"LANG_DEFAULT_HUE_MIN" => Array(
				"TAG" => "LANG_DEFAULT_HUE_MIN",
				"NAME" => "Default minimum hue (0..255)",
				"TYPE" => "NUMBER",
				"EDITABLE" => true,
				"REQUIRED" => true
			),
			"LANG_DEFAULT_HUE_MAX" => Array(
				"TAG" => "LANG_DEFAULT_HUE_MAX",
				"NAME" => "Default maximum hue (0..255)",
				"TYPE" => "NUMBER",
				"EDITABLE" => true,
				"REQUIRED" => true
			)
		)
	),
	"ADMIN_SUGGESTIONS" => Array(
		"HEADER" => "Admin Suggestions",
		"SETTINGS" => Array(
			"ADMIN_WARNING_WEEKS_SINCE_LAST_JAM" => Array(
				"TAG" => "ADMIN_WARNING_WEEKS_SINCE_LAST_JAM",
				"NAME" => "Inactive administrator warning: Number of jams since last participation",
				"TYPE" => "NUMBER",
				"EDITABLE" => true,
				"REQUIRED" => true
			),
			"ADMIN_SUGGESTION_TOTAL_PARTICIPATION" => Array(
				"TAG" => "ADMIN_SUGGESTION_TOTAL_PARTICIPATION",
				"NAME" => "New administrator suggestion: Minimum total participation to suggest",
				"TYPE" => "NUMBER",
				"EDITABLE" => true,
				"REQUIRED" => true
			),
			"ADMIN_SUGGESTION_RECENT_PARTICIPATION" => Array(
				"TAG" => "ADMIN_SUGGESTION_RECENT_PARTICIPATION",
				"NAME" => "New administrator suggestion: Minimum recent participation percentage to suggest (0 - 100)",
				"TYPE" => "NUMBER",
				"EDITABLE" => true,
				"REQUIRED" => true
			)
		)
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
			
			$configCategoryID = "";
			$configCategoryHeader = "";
			foreach($configSettings as $category => $entries){
				if(array_key_exists($key, $entries["SETTINGS"])){
					$configCategoryID = $category;
					$configCategoryHeader = $entries["HEADER"];
				}
			}
			
			//Store key-value pairs in the CONFIG part of the dictionary.
			$configEntry = Array(
				"KEY" => $key, 
				"VALUE" => htmlentities($value), 
				"NAME" => $configSettings[$configCategoryID]["SETTINGS"][$key]["NAME"]
			);
			if($configSettings[$configCategoryID]["SETTINGS"][$key]["EDITABLE"] == FALSE){
				$configEntry["DISABLED"] = 1;
			}
			if($configSettings[$configCategoryID]["SETTINGS"][$key]["REQUIRED"] == TRUE){
				$configEntry["REQUIRED"] = 1;
			}
			switch($configSettings[$configCategoryID]["SETTINGS"][$key]["TYPE"]){
				case "TEXT":
					$configEntry["TYPE_TEXT"] = 1;
				break;
				case "NUMBER":
					$configEntry["TYPE_NUMBER"] = 1;
				break;
				case "ENUM":
					$configEntry["TYPE_ENUM"] = 1;
					$configEntry["ENUM_OPTIONS"] = Array();
					foreach($configSettings[$configCategoryID]["SETTINGS"][$key]["ENUM_OPTIONS"] as $index => $enumOption){
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
				if($configDictionaryEntry["CATEGORY_ID"] == $configCategoryID){
					$i = $index;
				}
			}
			
			$dictionary["CONFIG"][$i]["CATEGORY_ID"] = $configCategoryID;
			$dictionary["CONFIG"][$i]["CATEGORY_HEADER"] = $configCategoryHeader;
			$dictionary["CONFIG"][$i]["ENTRIES"][] = $configEntry;
			
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
			
	$configCategoryID = "";
	foreach($configSettings as $category => $entries){
		if(array_key_exists($key, $entries["SETTINGS"])){
			$configCategoryID = $category;
		}
	}
	
	if($configSettings[$configCategoryID]["SETTINGS"][$key]["EDITABLE"] == FALSE){
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