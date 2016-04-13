<?php

function LoadEntries(){
	global $dictionary, $jams, $authors, $entries;
	
	//Clear public lists which get updated by this function
	$dictionary["jams"] = Array();
	$dictionary["authors"] = Array();
	$jams = Array();
	$authors = Array();
	$entries = Array();
	
	//Create lists of jams and jam entries
	$filesToParse = GetSortedJamFileList();
	$authorList = Array();
	$firstJam = true;
	$jamFromStart = 1;
	$totalEntries = 0;
	foreach ($filesToParse as $fileLoc) {
		//Read data about the jam
		$data = json_decode(file_get_contents($fileLoc), true);
		$newData = Array();
		$newData["jam_number"] = $data["jam_number"];
		$newData["jam_number_ordinal"] = ordinal($data["jam_number"]);
		$newData["theme"] = $data["theme"];
		$newData["theme_visible"] = $data["theme"]; //Used for administration
		$newData["date"] = $data["date"];
		$newData["time"] = $data["time"];
		$newData["entries"] = Array();
		$newData["first_jam"] = $firstJam;
		$newData["entries_visible"] = $jamFromStart <= 2;
		if($firstJam){
			$firstJam = false;
		}
		
		foreach ($data["entries"] as $i => $entry){
			$newData["entries"][$i]["title"] = $entry["title"];
			$author = $entry["author"];
			$entries[] = $entry;
			
			if(isset($authorList[$author])){
				$entryCount = $authorList[$author]["entry_count"];
				$entryCount = $entryCount + 1;
				$authorList[$author]["entry_count"] = $entryCount;
			}else{
				$authorList[$author] = Array("entry_count" => 1, "username" => $author);
			}
			
			$newData["entries"][$i]["author"] = $author;
			$newData["entries"][$i]["url"] = str_replace("'", "\\'", $entry["url"]);
			$newData["entries"][$i]["screenshot_url"] = str_replace("'", "\\'", $entry["screenshot_url"]);
		}
		
		$totalEntries += count($newData["entries"]);
		$newData["entries_count"] = count($newData["entries"]);
		
		
		//Hide theme of not-yet-started jams
		
		$now = new DateTime();
		$datetime = new DateTime($data["start_time"]);
		$timeUntilJam = date_diff($datetime, $now);
		
		if($datetime > $now){
			$newData["theme"] = "Not yet announced";
			$newData["jam_started"] = false;
			if($timeUntilJam->days > 0){
				$newData["time_left"] = $timeUntilJam->format("%a days %H:%I:%S");
			}else if($timeUntilJam->h > 0){
				$newData["time_left"] = $timeUntilJam->format("%H:%I:%S");
			}else  if($timeUntilJam->i > 0){
				$newData["time_left"] = $timeUntilJam->format("%I:%S");
			}else if($timeUntilJam->s > 0){
				$newData["time_left"] = $timeUntilJam->format("%S seconds");
			}else{
				$newData["time_left"] = "Now!";
			}
		}else{
			$newData["jam_started"] = true;
		}
		
		//Insert into dictionary array
		$dictionary["jams"][] = $newData;
		$jams[] = $newData;
		$jamFromStart++;
	}
	
	$dictionary["all_entries_count"] = $totalEntries;

	//Insert authors into dictionary
	foreach($authorList as $k => $authorData){
		$dictionary["authors"][] = $authorData;
		
		//Update users list with entry count for each
		foreach($dictionary["users"] as $i => $dictUserInfo){
			if($dictUserInfo["username"] == $k){
				$dictionary["users"][$i]["entry_count"] = $authorData["entry_count"];
			}
		}
		//Update admins list with entry count for each
		foreach($dictionary["admins"] as $i => $dictUserInfo){
			if($dictUserInfo["username"] == $k){
				$dictionary["admins"][$i]["entry_count"] = $authorData["entry_count"];
			}
		}
		//Update registered users list with entry count for each
		foreach($dictionary["registered_users"] as $i => $dictUserInfo){
			if($dictUserInfo["username"] == $k){
				$dictionary["registered_users"][$i]["entry_count"] = $authorData["entry_count"];
			}
		}
		$authors[] = $authorData;
	}
}




//Creates a new jam with the provided theme, which starts at the given date
//and time. All three are non-blank strings. $date and $time should be
//parsable by PHP's date(...) function. Function also authorizes the user
//(checks whether or not they are an admin).
//TODO: Replace die() with in-page warning
function CreateJam($theme, $date, $time){
	
	$jamNumber = intval(GetNextJamNumber());
	$theme = trim($theme);
	$date = trim($date);
	$time = trim($time);
	
	//Authorize user (logged in)
	if(IsLoggedIn() === false){
		die("Not logged in.");
	}
	
	//Authorize user (is admin)
	if(IsAdmin() === false){
		die("Only admins can create jams.");
	}
	
	//Validate jam number
	if($jamNumber <= 0){
		die("Invalid jam number");
	}
	
	//Validate theme
	if(strlen($theme) <= 0){
		die("Invalid theme");
	}
	
	//Validate date and time and create datetime object
	if(strlen($date) <= 0){
		die("Invalid date");
	}else if(strlen($time) <= 0){
		die("Invalid time");
	}else{
		$datetime = strtotime($date." ".$time." UTC");
	}
	
	$newJam = Array();
	$newJam["jam_number"] = $jamNumber;
	$newJam["theme"] = $theme;
	$newJam["date"] = gmdate("d M Y", $datetime);
	$newJam["time"] = gmdate("H:i", $datetime);
	$newJam["start_time"] = gmdate("c", $datetime);
	$newJam["entries"] = Array();
	file_put_contents("data/jams/jam_$jamNumber.json", json_encode($newJam));
}

//Edits an existing jam, identified by the jam number.
//Only changes the theme, date and time, does NOT change the jam number.
function EditJam($jamNumber, $theme, $date, $time){
	global $jams;
	
	//Authorize user (is admin)
	if(IsAdmin() === false){
		die("Only admins can edit jams.");
	}
	
	$theme = trim($theme);
	$date = trim($date);
	$time = trim($time);
	
	//Validate values
	$jamNumber = intval($jamNumber);
	if($jamNumber <= 0){
		die("invalid jam number");
		return;
	}
	
	if(strlen($theme) <= 0){
		die("invalid theme");
		return;
	}
	
	//Validate date and time and create datetime object
	if(strlen($date) <= 0){
		die("Invalid date");
	}else if(strlen($time) <= 0){
		die("Invalid time");
	}else{
		$datetime = strtotime($date." ".$time." UTC");
	}
	
	if(count($jams) == 0){
		return; //No jams exist
	}
	
	foreach($jams as $i => $jam){
		if(intval($jam["jam_number"]) == $jamNumber){
			$jam["theme"] = $theme;
			$jam["date"] = gmdate("d M Y", $datetime);
			$jam["time"] = gmdate("H:i", $datetime);
			$jam["start_time"] = gmdate("c", $datetime);
			file_put_contents("data/jams/jam_$jamNumber.json", json_encode($jam));
			break;
		}
	}
}

//Submits a new entry to the last jam. All parameters are strings, $gameName
//and $gameURL must be non-blank, $gameURL must be a valid URL, $screenshotURL
//can either be blank or a valid URL. If blank, a default image is used instead.
//Function also authorizes the user (must be logged in)
//TODO: Replace die() with in-page warning
function SubmitEntry($gameName, $gameURL, $screenshotURL){
	global $loggedInUser;
	
	$gameName = trim($gameName);
	$gameURL = trim($gameURL);
	$screenshotURL = trim($screenshotURL);
	
	//Authorize user
	if(IsLoggedIn() === false){
		die("Not logged in.");
	}
	
	//Validate game name
	if(strlen($gameName) < 1){
		die("Game name not provided");
	}
	
	//Validate Game URL
	if(SanitizeURL($gameURL) === false){
		die("Invalid game URL");
	}
	
	//Validate Screenshot URL
	if($screenshotURL == ""){
		$screenshotURL = "logo.png";
	}else if(SanitizeURL($screenshotURL) === false){
		die("Invalid screenshot URL. Leave blank for default.");
	}
	
	$filesToParse = GetSortedJamFileList();
	if(count($filesToParse) < 1){
		die("No jam to submit your entry to");
	}
	
	//First on the list is the current jam.
	$currentJamFile = $filesToParse[count($filesToParse) - 1];
		print $loggedInUser["username"];
	
	$currentJam = json_decode(file_get_contents($currentJamFile), true);
	if(isset($currentJam["entries"])){
		$entryUpdated = false;
		foreach($currentJam["entries"] as $i => $entry){
			if($entry["author"] == $loggedInUser["username"]){
				//Updating existing entry
				$currentJam["entries"][$i] = Array("title" => "$gameName", "author" => "".$loggedInUser["username"], "url" => "$gameURL", "screenshot_url" => "$screenshotURL");
				file_put_contents($currentJamFile, json_encode($currentJam));
				$entryUpdated = true;
			}
		}
		if(!$entryUpdated){
			//Submitting new entry
			$currentJam["entries"][] = Array("title" => "$gameName", "author" => "".$loggedInUser["username"], "url" => "$gameURL", "screenshot_url" => "$screenshotURL");
			file_put_contents($currentJamFile, json_encode($currentJam));
		}
	}
}

//Edits an existing entry, identified by the jam number and author.
//Only changes the title, game url and screenshot url, does NOT change the jam number or author.
function EditEntry($jamNumber, $author, $title, $gameURL, $screenshotURL){
	global $jams;
	
	//Authorize user (is admin)
	if(IsAdmin() === false){
		die("Only admins can edit entries.");
	}
	
	$author = trim($author);
	$title = trim($title);
	$gameURL = trim($gameURL);
	$screenshotURL = trim($screenshotURL);
	
	//Validate values
	$jamNumber = intval($jamNumber);
	if($jamNumber <= 0){
		die("invalid jam number");
		return;
	}
	
	//Validate title
	if(strlen($title) <= 0){
		die("invalid title");
		return;
	}
	
	//Validate Game URL
	if(SanitizeURL($gameURL) === false){
		die("Invalid game URL");
	}
	
	//Validate Screenshot URL
	if($screenshotURL == ""){
		$screenshotURL = "logo.png";
	}else if(SanitizeURL($screenshotURL) === false){
		die("Invalid screenshot URL. Leave blank for default.");
	}
	
	if(count($jams) == 0){
		return; //No jams exist
	}
	
	foreach($jams as $i => $jam){
		if(intval($jam["jam_number"]) == $jamNumber){
			foreach($jam["entries"] as $j => $entry){
				if($entry["author"] == $author){
					$jam["entries"][$j]["title"] = $title;
					$jam["entries"][$j]["url"] = $gameURL;
					$jam["entries"][$j]["screenshot_url"] = $screenshotURL;
					file_put_contents("data/jams/jam_$jamNumber.json", json_encode($jam));
					break;
				}
			}
			break;
		}
	}
}

function GetNextJamDateAndTime(){
	global $dictionary;
	
	$today = date('w'); 
	$days = 6 - $today; 
	$saturday = strtotime("+$days days"); 
	
	$dictionary["next_jam_suggested_date"] = date("Y-m-d", $saturday);
	$dictionary["next_jam_suggested_time"] = "20:00";
}

?>