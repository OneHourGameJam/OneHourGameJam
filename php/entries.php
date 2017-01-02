<?php

function LoadEntries(){
	global $dictionary, $jams, $authors, $entries, $users, $dbConn;
	
	//Clear public lists which get updated by this function
	$dictionary["jams"] = Array();
	$dictionary["authors"] = Array();
	$jams = Array();
	$authors = Array();
	$entries = Array();
	
	//Create lists of jams and jam entries
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
		$newData["minutes_to_jam"] = floor((strtotime($info["jam_start_datetime"] ." UTC") - time()) / 60);
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
			$entry = Array();
			$entry["title"] = $info2["entry_title"];
			$entry["title_url_encoded"] = urlencode($info2["entry_title"]);
			$entry["description"] = $info2["entry_description"];
			$author_username = $info2["entry_author"];
			$author = $author_username;
			$author_display = $author_username;
			if(isset($users[$author_username]["display_name"])){
				$author_display = $users[$author_username]["display_name"];
			}
			
			$entry["author_display"] = $author_display;
			$entry["author"] = $author;
			$entry["author_url_encoded"] = urlencode($author);
			
			$entry["url"] = str_replace("'", "\\'", $info2["entry_url"]);
			$entry["url_web"] = str_replace("'", "\\'", $info2["entry_url_web"]);
			$entry["url_windows"] = str_replace("'", "\\'", $info2["entry_url_windows"]);
			$entry["url_mac"] = str_replace("'", "\\'", $info2["entry_url_mac"]);
			$entry["url_linux"] = str_replace("'", "\\'", $info2["entry_url_linux"]);
			$entry["url_ios"] = str_replace("'", "\\'", $info2["entry_url_ios"]);
			$entry["url_android"] = str_replace("'", "\\'", $info2["entry_url_android"]);
			$entry["screenshot_url"] = str_replace("'", "\\'", $info2["entry_screenshot_url"]);
			
			if($entry["url"] != ""){$entry["has_url"] = 1;}
			if($entry["url_web"] != ""){$entry["has_url_web"] = 1;}
			if($entry["url_windows"] != ""){$entry["has_url_windows"] = 1;}
			if($entry["url_mac"] != ""){$entry["has_url_mac"] = 1;}
			if($entry["url_linux"] != ""){$entry["has_url_linux"] = 1;}
			if($entry["url_ios"] != ""){$entry["has_url_ios"] = 1;}
			if($entry["url_android"] != ""){$entry["has_url_android"] = 1;}
			
			$entry["jam_number"] = $newData["jam_number"];
			$entry["jam_theme"] = $newData["theme"];
			
			$hasTitle = false;
			$hasDesc = false;
			$hasSS = false;
			
			if($entry["screenshot_url"] != "logo.png" &&
			   $entry["screenshot_url"] != ""){
				$entry["has_screenshot"] = 1;
				$hasSS = true;
			}
			
			if(trim($entry["title"]) != ""){
				$entry["has_title"] = 1;
				$hasTitle = true;
			}
			
			if(trim($entry["description"]) != ""){
				$entry["has_description"] = 1;
				$hasDesc = true;
			}
			
			if(isset($authorList[$author])){
				$authorList[$author]["entry_count"] += 1;
				if(intval($newData["jam_number"]) < intval($authorList[$author]["first_jam_number"])){
					$authorList[$author]["first_jam_number"] = $newData["jam_number"];
				}
				if(intval($newData["jam_number"]) > intval($authorList[$author]["last_jam_number"])){
					$authorList[$author]["last_jam_number"] = $newData["jam_number"];
				}
				$authorList[$author]["entries"][] = $entry;
			}else{
				if(isset($users[$author])){
					$authorList[$author] = $users[$author];
				}else{
					//Author does not have matching account (very old entry)
					$authorList[$author] = Array("username" => $author, "display_name" => $author_display);
				}
				$authorList[$author]["entry_count"] = 1;
				$authorList[$author]["first_jam_number"] = $newData["jam_number"];
				$authorList[$author]["last_jam_number"] = $newData["jam_number"];
				$authorList[$author]["entries"][] = $entry;
			}
			
			$newData["entries"][$i] = $entry;
			$entries[] = $entry;
			$i++;
		}
		
		$totalEntries += count($newData["entries"]);
		$newData["entries_count"] = count($newData["entries"]);
		
		//Hide theme of not-yet-started jams
		
		$now = new DateTime();
		$datetime = new DateTime($newData["start_time"] . " UTC");
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
		$authors[$authorData["username"]] = $authorData;
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

// Returns a jam given its number.
// The dictionary of jams must have been previously loaded.
function GetJamByNumber($jamNumber) {
	global $jams;

	foreach ($jams as $jam) {
		if ($jam["jam_number"] == $jamNumber) {
			return $jam;
		}
	}

	return null;
}

//Creates or updates a jam entry. $jam_number is a mandatory jam number to submit to.
//All other parameters are strings: $gameName and $gameURL must be non-blank
//$gameURL must be a valid URL, $screenshotURL can either be blank or a valid URL.
//If blank, a default image is used instead. description must be non-blank.
//Function also authorizes the user (must be logged in)
//TODO: Replace die() with in-page warning
function SubmitEntry($jam_number, $gameName, $gameURL, $gameURLWeb, $gameURLWin, $gameURLMac, $gameURLLinux, $gameURLiOS, $gameURLAndroid, $screenshotURL, $description){
	global $loggedInUser, $_FILES, $dbConn, $ip, $userAgent, $jams;

	$gameName = trim($gameName);
	$gameURL = trim($gameURL);
	$gameURLWeb = trim($gameURLWeb);
	$gameURLWin = trim($gameURLWin);
	$gameURLMac = trim($gameURLMac);
	$gameURLLinux = trim($gameURLLinux);
	$gameURLiOS = trim($gameURLiOS);
	$gameURLAndroid = trim($gameURLAndroid);
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
	
	$urlValid = FALSE;
	//Validate that at least one of the provided game URLs is valid
	if(SanitizeURL($gameURL) !== false){
		$urlValid = TRUE;
	}
	if(SanitizeURL($gameURLWeb) !== false){
		$urlValid = TRUE;
	}
	if(SanitizeURL($gameURLWin) !== false){
		$urlValid = TRUE;
	}
	if(SanitizeURL($gameURLMac) !== false){
		$urlValid = TRUE;
	}
	if(SanitizeURL($gameURLLinux) !== false){
		$urlValid = TRUE;
	}
	if(SanitizeURL($gameURLiOS) !== false){
		$urlValid = TRUE;
	}
	if(SanitizeURL($gameURLAndroid) !== false){
		$urlValid = TRUE;
	}
	
	//Did at least one url pass validation?
	if($urlValid == FALSE){
		die("Invalid game url");
	}
	
	//Validate description
	if(strlen($description) <= 0){
		die("Invalid description");
	}
	
	//Check that a jam exists
	if (!is_int($jam_number)) {
		die('Invalid jam number');
	}
	$jam = GetJamByNumber($jam_number);
	if($jam == null || $jam["jam_number"] == 0){
		die("No jam to submit to");
	}
	
	if(count($jams) == 0){
		die("No jam to submit to");
	}
	
	//Upload screenshot
	$jam_folder = "data/jams/jam_$jam_number";
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
	
	//Default screenshot URL
	if($screenshotURL == ""){
		$screenshotURL = "logo.png";
	}
	
	//Create or update entry
	if(isset($jam["entries"])){
		$entryUpdated = false;
		foreach($jam["entries"] as $i => $entry){
			if($entry["author"] == $loggedInUser["username"]){
				//Updating existing entry
				$existingScreenshot = $jam["entries"][$i]["screenshot_url"];
				if($screenshotURL == "logo.png"){
					if($existingScreenshot != "" && $existingScreenshot != "logo.png"){
						$screenshotURL = $existingScreenshot;
					}
				}
				
				$escapedGameName = mysqli_real_escape_string($dbConn, $gameName);
				$escapedGameURL = mysqli_real_escape_string($dbConn, $gameURL);
				$escapedGameURLWeb = mysqli_real_escape_string($dbConn, $gameURLWeb);
				$escapedGameURLWin = mysqli_real_escape_string($dbConn, $gameURLWin);
				$escapedGameURLMac = mysqli_real_escape_string($dbConn, $gameURLMac);
				$escapedGameURLLinux = mysqli_real_escape_string($dbConn, $gameURLLinux);
				$escapedGameURLiOS = mysqli_real_escape_string($dbConn, $gameURLiOS);
				$escapedGameURLAndroid = mysqli_real_escape_string($dbConn, $gameURLAndroid);
				$escapedScreenshotURL = mysqli_real_escape_string($dbConn, $screenshotURL);
				$escapedDescription = mysqli_real_escape_string($dbConn, $description);
				$escapedAuthorName = mysqli_real_escape_string($dbConn, $entry["author"]);
				$escaped_jamNumber = mysqli_real_escape_string($dbConn, $jam_number);
				
				$sql = "
				UPDATE entry
				SET
					entry_title = '$escapedGameName',
					entry_url = '$escapedGameURL',
					entry_url_web = '$escapedGameURLWeb',
					entry_url_windows = '$escapedGameURLWin',
					entry_url_mac = '$escapedGameURLMac',
					entry_url_linux = '$escapedGameURLLinux',
					entry_url_ios = '$escapedGameURLiOS',
					entry_url_android = '$escapedGameURLAndroid',
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
			$currentJam = $jams[0];
			if ($jam_number != $currentJam["jam_number"]) {
				die('Cannot make a new submission to a past jam');
			}

			$escaped_ip = mysqli_real_escape_string($dbConn, $ip);
			$escaped_userAgent = mysqli_real_escape_string($dbConn, $userAgent);
			$escaped_jamId = mysqli_real_escape_string($dbConn, $jam["jam_id"]);
			$escaped_jamNumber = mysqli_real_escape_string($dbConn, $jam["jam_number"]);
			$escaped_gameName = mysqli_real_escape_string($dbConn, $gameName);
			$escaped_description = mysqli_real_escape_string($dbConn, $description);
			$escaped_aurhor = mysqli_real_escape_string($dbConn, $loggedInUser["username"]);
			$escaped_gameURL = mysqli_real_escape_string($dbConn, $gameURL);
			$escaped_gameURLWeb = mysqli_real_escape_string($dbConn, $gameURLWeb);
			$escaped_gameURLWin = mysqli_real_escape_string($dbConn, $gameURLWin);
			$escaped_gameURLMac = mysqli_real_escape_string($dbConn, $gameURLMac);
			$escaped_gameURLLinux = mysqli_real_escape_string($dbConn, $gameURLLinux);
			$escaped_gameURLiOS = mysqli_real_escape_string($dbConn, $gameURLiOS);
			$escaped_gameURLAndroid = mysqli_real_escape_string($dbConn, $gameURLAndroid);
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
				entry_url_web,
				entry_url_windows,
				entry_url_mac,
				entry_url_linux,
				entry_url_ios,
				entry_url_android,
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
				'$escaped_gameURLWeb',
				'$escaped_gameURLWin',
				'$escaped_gameURLMac',
				'$escaped_gameURLLinux',
				'$escaped_gameURLiOS',
				'$escaped_gameURLAndroid',
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

?>