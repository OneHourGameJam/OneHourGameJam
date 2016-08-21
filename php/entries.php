<?php

function LoadEntries(){
	global $dictionary, $jams, $authors, $entries, $dbConn;
	
	//Clear public lists which get updated by this function
	$dictionary["jams"] = Array();
	$dictionary["authors"] = Array();
	$jams = Array();
	$authors = Array();
	$entries = Array();
	
	//Create lists of jams and jam entries
	$filesToParse = GetSortedJamFileList();
	//$filesToParse = array_reverse($filesToParse);
	$authorList = Array();
	$firstJam = true;
	$jamFromStart = 1;
	$totalEntries = 0;
	$largest_jam_number = -1;
	
	$sql = "SELECT * FROM jam WHERE jam_deleted = 0 ORDER BY jam_jam_number DESC";
	$data = mysqli_query($dbConn, $sql);
	$sql = "";
	
	while($info = mysqli_fetch_array($data)){
		
		//Read data about the jam
		$newData = Array();
		$newData["jam_number"] = intval($info["jam_jam_number"]);
		$newData["start_time"] = $info["jam_start_datetime"];
		$newData["jam_id"] = intval($info["jam_id"]);
		$newData["jam_number_ordinal"] = ordinal(intval($info["jam_jam_number"]));
		$newData["theme"] = $info["jam_theme"];
		$newData["theme_visible"] = $info["jam_theme"]; //Used for administration
		$newData["date"] = date("d M Y", strtotime($info["jam_start_datetime"]));
		$newData["time"] = date("G:i", strtotime($info["jam_start_datetime"]));
		$newData["entries"] = Array();
		$newData["first_jam"] = $firstJam;
		$newData["entries_visible"] = $jamFromStart <= 2;
		if($firstJam){
			$firstJam = false;
		}
		
		$sql = "SELECT * FROM entry WHERE entry_deleted = 0 AND entry_jam_id = ".$newData["jam_id"]." ORDER BY entry_id ASC";
		$data2 = mysqli_query($dbConn, $sql);
		$sql = "";
		
		$i = 0;
		while($info2 = mysqli_fetch_array($data2)){
			$newData["entries"][$i]["title"] = $info2["entry_title"];
			$newData["entries"][$i]["title_url_encoded"] = urlencode($info2["entry_title"]);
			$newData["entries"][$i]["description"] = $info2["entry_description"];
			$author = $info2["entry_author"];
			$entries[] = Array("title" => $info2["entry_title"], "author" => $info2["entry_author"], "url" => $info2["entry_url"],"screenshot_url" => $info2["entry_screenshot_url"],"description" => $info2["entry_description"]);
			
			if(isset($authorList[$author])){
				$entryCount = $authorList[$author]["entry_count"];
				$entryCount = $entryCount + 1;
				$authorList[$author]["entry_count"] = $entryCount;
				if(intval($newData["jam_number"]) < intval($authorList[$author]["first_jam_number"])){
					$authorList[$author]["first_jam_number"] = $newData["jam_number"];
				}
				if(intval($newData["jam_number"]) > intval($authorList[$author]["last_jam_number"])){
					$authorList[$author]["last_jam_number"] = $newData["jam_number"];
				}
			}else{
				$authorList[$author] = Array("entry_count" => 1, "username" => $author, "first_jam_number" => $newData["jam_number"], "last_jam_number" => $newData["jam_number"]);
			}
			
			$newData["entries"][$i]["author"] = $author;
			$newData["entries"][$i]["author_url_encoded"] = urlencode($author);
			
			$newData["entries"][$i]["url"] = str_replace("'", "\\'", $info2["entry_url"]);
			$newData["entries"][$i]["screenshot_url"] = str_replace("'", "\\'", $info2["entry_screenshot_url"]);
			
			$i++;
		}
		
		$totalEntries += count($newData["entries"]);
		$newData["entries_count"] = count($newData["entries"]);
		
		//Hide theme of not-yet-started jams
		
		$now = new DateTime();
		$datetime = new DateTime($newData["start_time"]);
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
		if($largest_jam_number < intval($newData["jam_number"])){
			$largest_jam_number = intval($newData["jam_number"]);
			$dictionary["current_jam"] = $newData;
		}
		
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
				$dictionary["users"][$i]["first_jam_number"] = $authorData["first_jam_number"];
				$dictionary["users"][$i]["last_jam_number"] = $authorData["last_jam_number"];
			}
		}
		//Update admins list with entry count for each
		foreach($dictionary["admins"] as $i => $dictUserInfo){
			if($dictUserInfo["username"] == $k){
				$dictionary["admins"][$i]["entry_count"] = $authorData["entry_count"];
				$dictionary["admins"][$i]["first_jam_number"] = $authorData["first_jam_number"];
				$dictionary["admins"][$i]["last_jam_number"] = $authorData["last_jam_number"];
			}
		}
		//Update registered users list with entry count for each
		foreach($dictionary["registered_users"] as $i => $dictUserInfo){
			if($dictUserInfo["username"] == $k){
				$dictionary["registered_users"][$i]["entry_count"] = $authorData["entry_count"];
				$dictionary["registered_users"][$i]["first_jam_number"] = $authorData["first_jam_number"];
				$dictionary["registered_users"][$i]["last_jam_number"] = $authorData["last_jam_number"];
			}
		}
		$authors[] = $authorData;
	}
	
	$dictionary["all_authors_count"] = count($authors);
	$dictionary["all_jams_count"] = count($jams);
	GetNextJamDateAndTime();
}




//Creates a new jam with the provided theme, which starts at the given date
//and time. All three are non-blank strings. $date and $time should be
//parsable by PHP's date(...) function. Function also authorizes the user
//(checks whether or not they are an admin).
//TODO: Replace die() with in-page warning
function CreateJam($theme, $date, $time){
	global $dbConn, $ip, $userAgent;
	
	$currentJamData = GetCurrentJamNumberAndID();
	$jamNumber = intval($currentJamData["NUMBER"] + 1);
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
	
	$escapedIP = mysqli_real_escape_string($dbConn, $ip);
	$escapedUserAgent = mysqli_real_escape_string($dbConn, $userAgent);
	$escapedJamNumber = mysqli_real_escape_string($dbConn, $newJam["jam_number"]);
	$escapedTheme = mysqli_real_escape_string($dbConn, $newJam["theme"]);
	$escapedStartTime = mysqli_real_escape_string($dbConn, "".gmdate("Y-m-d H:i", $datetime));
	
	$sql = "
		INSERT INTO jam
		(jam_id,
		jam_datetime,
		jam_ip,
		jam_user_agent,
		jam_jam_number,
		jam_theme,
		jam_start_datetime,
		jam_deleted)
		VALUES
		(null,
		Now(),
		'$escapedIP',
		'$escapedUserAgent',
		'$escapedJamNumber',
		'$escapedTheme',
		'$escapedStartTime',
		0);";
	
	$data = mysqli_query($dbConn, $sql);
	$sql = "";
}

//Edits an existing jam, identified by the jam number.
//Only changes the theme, date and time, does NOT change the jam number.
function EditJam($jamNumber, $theme, $date, $time){
	global $jams, $dbConn;
	
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
	
	$escapedTheme = mysqli_real_escape_string($dbConn, $theme);
	$escapedStartTime = mysqli_real_escape_string($dbConn, "".gmdate("Y-m-d H:i", $datetime));
	$escapedJamNumber = mysqli_real_escape_string($dbConn, "$jamNumber");
	
	$sql = "
		UPDATE jam
		SET jam_theme = '$escapedTheme', 
		    jam_start_datetime = '$escapedStartTime'
		WHERE jam_jam_number = $escapedJamNumber
		  AND jam_deleted = 0";
	$data = mysqli_query($dbConn, $sql);
	$sql = "";
}



//Deletes an existing jam, identified by the jam number.
function DeleteJam($jamID){
	global $jams, $dbConn;
	
	//Authorize user (is admin)
	if(IsAdmin() === false){
		die("Only admins can delete jams.");
	}
	
	//Validate values
	$jamID = intval($jamID);
	if($jamID <= 0){
		die("invalid jam ID");
		return;
	}
	
	if(count($jams) == 0){
		return; //No jams exist
	}
	
	$escapedJamID = mysqli_real_escape_string($dbConn, "$jamID");
	
	$sql = "
		UPDATE jam
		SET jam_deleted = 1
		WHERE jam_id = $escapedJamID
		  AND jam_deleted = 0";
	$data = mysqli_query($dbConn, $sql);
	$sql = "";
}

//Returns true / false based on whether or not the specified jam can be deleted
function CanDeleteJam($jamID){
	global $jams, $dbConn;
	
	//Authorize user (is admin)
	if(IsAdmin() === false){
		return FALSE;
	}
	
	//Validate values
	$jamID = intval($jamID);
	if($jamID <= 0){
		return FALSE;
	}
	
	if(!JamExists($jamID)){
		return FALSE;
	}
	
	$escapedJamID = mysqli_real_escape_string($dbConn, "$jamID");
	
	$sql = "
		SELECT 1
		FROM entry
		WHERE entry_jam_id = $escapedJamID
		AND entry_deleted = 0;
		";
	$data = mysqli_query($dbConn, $sql);
	$sql = "";
	
	if(mysqli_fetch_array($data)){
		return false;
	}else{
		return true;
	}
}

//Returns true / false based on whether or not the specified jam exists (and has not been deleted)
function JamExists($jamID){
	global $dbConn;
	
	//Validate values
	$jamID = intval($jamID);
	if($jamID <= 0){
		return FALSE;
	}
	
	$escapedJamID = mysqli_real_escape_string($dbConn, "$jamID");
	
	$sql = "
		SELECT 1
		FROM jam
		WHERE jam_id = $escapedJamID
		AND jam_deleted = 0;
		";
	$data = mysqli_query($dbConn, $sql);
	$sql = "";
	
	if(mysqli_fetch_array($data)){
		return true;
	}else{
		return false;
	}
}

//Submits a new entry to the last jam. All parameters are strings, $gameName
//and $gameURL must be non-blank, $gameURL must be a valid URL, $screenshotURL
//can either be blank or a valid URL. If blank, a default image is used instead.
//description must be non-blank
//Function also authorizes the user (must be logged in)
//TODO: Replace die() with in-page warning
function SubmitEntry($gameName, $gameURL, $screenshotURL, $description){
	global $loggedInUser, $_FILES, $dbConn, $ip, $userAgent;
	
	$gameName = trim($gameName);
	$gameURL = trim($gameURL);
	$screenshotURL = trim($screenshotURL);
	$description = trim($description);
	
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
	
	$filesToParse = GetSortedJamFileList();
	if(count($filesToParse) < 1){
		die("No jam to submit your entry to");
	}
	
	//Validate description
	if(strlen($description) <= 0){
		die("Invalid description");
	}
	
	//First on the list is the current jam.
	$currentJamFile = $filesToParse[count($filesToParse) - 1];
	$jam_folder = str_replace(".json", "", $currentJamFile);
	//print $loggedInUser["username"];
	
	if(isset($_FILES["screenshotfile"]) && $_FILES["screenshotfile"] != null && $_FILES["screenshotfile"]["size"] != 0){
		$uploadPass = 0;
		$imageFileType = strtolower(pathinfo($_FILES["screenshotfile"]["name"], PATHINFO_EXTENSION));
		$target_file = $jam_folder . "/".$loggedInUser["username"]."." . $imageFileType;
		$check = getimagesize($_FILES["screenshotfile"]["tmp_name"]);
		
		if($check !== false) {
			$uploadPass = 1;
		} else {
			die("Uploaded screenshot is not an image");
			$uploadPass = 0;
		}
		
		if ($_FILES["screenshotfile"]["size"] > 5000000) {
			die("Uploaded screenshot is too big (max 5MB)");
			$uploadPass = 0;
		}
		
		if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
		&& $imageFileType != "gif" ) {
			die("Uploaded screenshot is not jpeg, png or gif");
			$uploadPass = 0;
		}
		
		if($uploadPass == 1){
			if(!file_exists($jam_folder)){
				mkdir($jam_folder);
				file_put_contents($jam_folder."/.htaccess", "Order allow,deny\nAllow from all");
			}
			move_uploaded_file($_FILES["screenshotfile"]["tmp_name"], $target_file);
			$screenshotURL = $target_file;
		}
	}
	
	//Validate Screenshot URL
	if($screenshotURL == ""){
		$screenshotURL = "logo.png";
	}
	
	$currentJam = json_decode(file_get_contents($currentJamFile), true);
	if(isset($currentJam["entries"])){
		$entryUpdated = false;
		foreach($currentJam["entries"] as $i => $entry){
			if($entry["author"] == $loggedInUser["username"]){
				//Updating existing entry
				$existingScreenshot = $currentJam["entries"][$i]["screenshot_url"];
				if($screenshotURL == "logo.png"){
					if($existingScreenshot != "" && $existingScreenshot != "logo.png"){
						$screenshotURL = $existingScreenshot;
					}
				}
				
				$currentJam["entries"][$i] = Array("title" => "$gameName", "title_url_encoded" => urlencode("$gameName"), "author_url_encoded" => urlencode("".$loggedInUser["username"]), "author" => "".$loggedInUser["username"], "url" => "$gameURL", "screenshot_url" => "$screenshotURL", "description" => "$description");
				file_put_contents($currentJamFile, json_encode($currentJam));
				
				$escapedGameName = mysqli_real_escape_string($dbConn, $gameName);
				$escapedGameURL = mysqli_real_escape_string($dbConn, $gameURL);
				$escapedScreenshotURL = mysqli_real_escape_string($dbConn, $screenshotURL);
				$escapedDescription = mysqli_real_escape_string($dbConn, $description);
				$escapedAuthorName = mysqli_real_escape_string($dbConn, $entry["author"]);
				$escaped_jamNumber = mysqli_real_escape_string($dbConn, $currentJam["jam_number"]);
				
				$sql = "
				UPDATE entry
				SET
					entry_title = '$escapedGameName',
					entry_url = '$escapedGameURL',
					entry_screenshot_url = '$escapedScreenshotURL',
					entry_description = '$escapedDescription'
				WHERE 
					entry_author = '$escapedAuthorName'
				AND entry_jam_number = $escaped_jamNumber;

				";
				$data = mysqli_query($dbConn, $sql);
				$sql = "";
				
				$entryUpdated = true;
			}
		}
		if(!$entryUpdated){
			//Submitting new entry
			$currentJam["entries"][] = Array("title" => "$gameName", "author" => "".$loggedInUser["username"], "title_url_encoded" => urlencode("$gameName"), "author_url_encoded" => urlencode("".$loggedInUser["username"]), "url" => "$gameURL", "screenshot_url" => "$screenshotURL", "description" => "$description");
			file_put_contents($currentJamFile, json_encode($currentJam));
			
			

			$jamData = GetCurrentJamNumberAndID();
			
			$escaped_ip = mysqli_real_escape_string($dbConn, $ip);
			$escaped_userAgent = mysqli_real_escape_string($dbConn, $userAgent);
			$escaped_jamId = mysqli_real_escape_string($dbConn, $jamData["ID"]);
			$escaped_jamNumber = mysqli_real_escape_string($dbConn, $jamData["NUMBER"]);
			$escaped_gameName = mysqli_real_escape_string($dbConn, $gameName);
			$escaped_description = mysqli_real_escape_string($dbConn, $description);
			$escaped_aurhor = mysqli_real_escape_string($dbConn, $loggedInUser["username"]);
			$escaped_gameURL = mysqli_real_escape_string($dbConn, $gameURL);
			$escaped_ssURL = mysqli_real_escape_string($dbConn, $screenshotURL);
			
			$sql = "
				INSERT INTO entry
				(entry_id,
				entry_datetime,
				entry_ip,
				entry_user_agent,
				entry_jam_id,
				entry_jam_number,
				entry_title,
				entry_description,
				entry_author,
				entry_url,
				entry_screenshot_url)
				VALUES
				(null,
				Now(),
				'$escaped_ip',
				'$escaped_userAgent',
				$escaped_jamId,
				$escaped_jamNumber,
				'$escaped_gameName',
				'$escaped_description',
				'$escaped_aurhor',
				'$escaped_gameURL',
				'$escaped_ssURL');
			";
			$data = mysqli_query($dbConn, $sql);
			$sql = "";
		}
	}
	
	LoadEntries();
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
	
	$saturday = strtotime("saturday +20 hours");
	$dictionary["next_jam_suggested_date"] = date("Y-m-d", $saturday);
	$dictionary["next_jam_suggested_time"] = date("H:i", $saturday);
	$now = time();
	$interval = $saturday - $now;
	$dictionary["seconds_until_jam_suggested_time"] = $interval;
	return $saturday;
	return $saturday;
}

function GetCurrentJamNumberAndID(){
	global $dbConn;
	
	$sql = "
		SELECT jam_id, jam_jam_number FROM jam WHERE jam_jam_number = (SELECT MAX(jam_jam_number) FROM jam WHERE jam_deleted = 0) AND jam_deleted = 0
	";
	$data = mysqli_query($dbConn, $sql);
	$sql = "";
	
	if($info = mysqli_fetch_array($data)){
		return Array("NUMBER" => intval($info["jam_jam_number"]), "ID" => intval($info["jam_id"]));
	}else{
		return Array("NUMBER" => 0, "ID" => 0);
	}
}

?>