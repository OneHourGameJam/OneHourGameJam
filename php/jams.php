<?php

function LoadJams(){
	global $dbConn;	

	$jams = Array();

	$sql = "SELECT jam_id, jam_username, jam_jam_number, jam_theme, jam_start_datetime, jam_colors, jam_deleted
	 FROM jam ORDER BY jam_jam_number DESC";
	$data = mysqli_query($dbConn, $sql);
	$sql = "";

	while($info = mysqli_fetch_array($data)){
		$jamData = Array();
		$jamData["jam_id"] = intval($info["jam_id"]);
		$jamData["username"] = $info["jam_username"];
		$jamData["jam_number"] = intval($info["jam_jam_number"]);
		$jamData["theme"] = $info["jam_theme"];
		$jamData["start_time"] = $info["jam_start_datetime"];
		$jamData["colors"] = ParseJamColors($info["jam_colors"]);
		$jamData["jam_deleted"] = $info["jam_deleted"];

		$jamID = $jamData["jam_id"];

		$jams[$jamID] = $jamData;
	}

	return $jams;
}

function ParseJamColors($colorString){
	$jamColors = explode("|", $colorString);
	if(count($jamColors) == 0){
		return Array("FFFFFF");
	}

	return $jamColors;
}

function RenderJam($jam, $nonDeletedJamCounter, $config, $games, $users, $loggedInUser){
	$jamData = Array();

	$jamData["jam_id"] = $jam["jam_id"];
	$jamData["username"] = $jam["username"];
	$jamData["jam_number"] = $jam["jam_number"];
	$jamData["theme"] = $jam["theme"];
	$jamData["start_time"] = $jam["start_time"];
	if($jam["jam_deleted"] == 1){
		$jamData["jam_deleted"] = 1;
	}

	$jamData["theme_visible"] = $jam["theme"]; //Theme is visible to admins
	$jamData["jam_number_ordinal"] = ordinal(intval($jam["jam_number"]));
	$jamData["date"] = date("d M Y", strtotime($jam["start_time"]));
	$jamData["time"] = date("H:i", strtotime($jam["start_time"]));

	//Jam Colors
	$jamData["colors"] = Array();
	foreach($jam["colors"] as $num => $color){
		$jamData["colors"][] = Array("number" => $num, "color" => "#".$color, "color_hex" => $color);
	}
	$jamData["colors_input_string"] = implode("-", $jam["colors"]);

	$jamData["minutes_to_jam"] = floor((strtotime($jam["start_time"] ." UTC") - time()) / 60);

	//Games in jam
	$jamData["entries"] = Array();
	foreach($games as $j => $game){
		if($game["jam_id"] == $jamData["jam_id"]){
			$jamData["entries"][] = RenderGame($game, $jams, $users);
			
			//Has logged in user participated in this jam?
			if(!$game["entry_deleted"]){
				if($loggedInUser["username"] == $game["author"]){
					$jamData["user_participated_in_jam"] = 1;
				}
			}
		}
	}

	//Hide theme of not-yet-started jams
	$now = new DateTime();
	$datetime = new DateTime($jamData["start_time"] . " UTC");
	$timeUntilJam = date_diff($datetime, $now);

	$jamData["first_jam"] = $nonDeletedJamCounter == 1;
	$jamData["entries_visible"] = $nonDeletedJamCounter <= 2;
	$jamData["entries_count"] = count($jamData["entries"]);

	if($datetime > $now){
		$jamData["theme"] = "Not yet announced";
		$jamData["jam_started"] = false;
		if($timeUntilJam->days > 0){
			$jamData["time_left"] = $timeUntilJam->format("%a days %H:%I:%S");
		}else if($timeUntilJam->h > 0){
			$jamData["time_left"] = $timeUntilJam->format("%H:%I:%S");
		}else  if($timeUntilJam->i > 0){
			$jamData["time_left"] = $timeUntilJam->format("%I:%S");
		}else if($timeUntilJam->s > 0){
			$jamData["time_left"] = $timeUntilJam->format("%S seconds");
		}else{
			$jamData["time_left"] = "Now!";
		}
	}else{
		$jamData["jam_started"] = true;
	}

	return $jamData;
}

function RenderSubmitJam($jam, $config, $games, $users, $loggedInUser){
	return RenderJam($jam, 0, $config, $games, $users, $loggedInUser);
}

function RenderJams($jams, $config, $games, $users, $loggedInUser){
	$render = Array("LIST" => Array());

    $nonDeletedJamCounter = 0;
	$latestStartedJamFound = false;
	$currentJamData = GetCurrentJamNumberAndID();

	foreach($jams as $i => $jam){
		if($jam["jam_deleted"] != 1){
			$nonDeletedJamCounter += 1;
		}
		
		$jamData = RenderJam($jam, $nonDeletedJamCounter, $config, $games, $users, $loggedInUser);
		
		$now = new DateTime();
		$datetime = new DateTime($jamData["start_time"] . " UTC");
		if($datetime > $now){
			$nextJamTime = strtotime($jamData["start_time"]);
			$render["next_jam_timer_code"] = date("Y-m-d", $nextJamTime)."T".date("H:i", $nextJamTime).":00Z";
		}else{
			if(!isset($jamData["jam_deleted"])){
				if($latestStartedJamFound == false){
					$jamData["is_latest_started_jam"] = 1;
					$latestStartedJamFound = true; 
				}
			}
		}

		$render["LIST"][] = $jamData;

		if($currentJamData["ID"] == $jamData["jam_id"]){
			$render["current_jam"] = $jamData;
		}
    }
    
    $render["all_jams_count"] = $nonDeletedJamCounter;

	return $render;
}



//Checks if a jam is scheduled. If not and a jam is coming up, one is scheduled automatically.
function CheckNextJamSchedule(){
	global $themes, $nextJamTime;
	
	$autoScheduleThreshold = 2 * 60 * 60;
	
	$suggestedNextJamTime = GetNextJamDateAndTime();
	$now = time();
	$interval = $suggestedNextJamTime - $now;
	$colors = "e38484|e3b684|dee384|ade384|84e38d|84e3be|84d6e3|84a4e3|9684e3|c784e3";
	
	if($interval > 0 && $interval <= $autoScheduleThreshold){
		if($nextJamTime != ""){
			//A future jam is already scheduled
			return;
		}
		
		$selectedTheme = "";
		
		$selectedTheme = SelectRandomThemeByVoteDifference();
		if($selectedTheme == ""){
			$selectedTheme = SelectRandomThemeByPopularity();
		}
		if($selectedTheme == ""){
			$selectedTheme = SelectRandomTheme();
		}
		if($selectedTheme == ""){
			$selectedTheme = "Any theme";
		}
		
		$currentJamData = GetCurrentJamNumberAndID();
		$jamNumber = intval($currentJamData["NUMBER"] + 1);
		
		AddJamToDatabase("127.0.0.1", "AUTO", "AUTOMATIC", $jamNumber, $selectedTheme, "".gmdate("Y-m-d H:i", $suggestedNextJamTime), $colors);
	}
}

//Selects a random theme (or "" if none can be selected) by calculating the difference between positive and negative votes and
//selecting a proportional random theme by this difference
function SelectRandomThemeByVoteDifference(){
	global $themes;
	$minimumVotes = 10;
	
	$selectedTheme = "";
	
	$availableThemes = Array();
	$totalVotesDifference = 0;
	foreach($themes as $id => $theme){
		$themeOption = Array();
		
		if($theme["banned"]){
			continue;
		}
		
		$votesFor = $theme["votes_for"];
		$votesNeutral = $theme["votes_neutral"];
		$votesAgainst = $theme["votes_against"];
		$votesDifference = $votesFor - $votesAgainst;
		
		$votesTotal = $votesFor + $votesNeutral + $votesAgainst;
		$votesOpinionatedTotal = $votesFor + $votesAgainst;
		
		if($votesOpinionatedTotal <= 0){
			continue;
		}
		
		$votesPopularity = $votesFor / ($votesOpinionatedTotal);
		
		if($votesTotal <= 0 || $votesTotal <= $minimumVotes){
			continue;
		}
		
		$themeOption["theme"] = $theme["theme"];
		$themeOption["votes_for"] = $votesFor;
		$themeOption["votes_difference"] = $votesDifference;
		$themeOption["popularity"] = $votesPopularity;
		$totalVotesDifference += max(0, $votesDifference);
		
		$availableThemes[] = $themeOption;
	}
	
	if($totalVotesDifference > 0 && count($availableThemes) > 0){
		$selectedVote = rand(0, $totalVotesDifference);
		
		$runningVoteNumber = $selectedVote;
		foreach($availableThemes as $i => $availableTheme){
			$runningVoteNumber -= $availableTheme["votes_difference"];
			if($runningVoteNumber <= 0){
				$selectedTheme = $availableTheme["theme"];
				break;
			}
		}
	}
	
	return $selectedTheme;
}

//Selects a random theme (or "" if none can be selected) proportionally based on its popularity.
function SelectRandomThemeByPopularity(){
	global $themes;
	$minimumVotes = 10;
	
	$selectedTheme = "";
	
	$availableThemes = Array();
	$totalPopularity = 0;
	foreach($themes as $id => $theme){
		$themeOption = Array();
		
		if($theme["banned"]){
			continue;
		}
		
		$votesFor = $theme["votes_for"];
		$votesNeutral = $theme["votes_neutral"];
		$votesAgainst = $theme["votes_against"];
		$votesDifference = $votesFor - $votesAgainst;
		
		$votesTotal = $votesFor + $votesNeutral + $votesAgainst;
		$votesOpinionatedTotal = $votesFor + $votesAgainst;
		
		if($votesOpinionatedTotal <= 0){
			continue;
		}
		
		$votesPopularity = $votesFor / ($votesOpinionatedTotal);
		
		if($votesTotal <= 0 || $votesTotal <= $minimumVotes){
			continue;
		}
		
		$themeOption["theme"] = $theme["theme"];
		$themeOption["votes_for"] = $votesFor;
		$themeOption["votes_difference"] = $votesDifference;
		$themeOption["popularity"] = $votesPopularity;
		$totalPopularity += max(0, $votesPopularity);
		
		$availableThemes[] = $themeOption;
	}
	
	if($totalPopularity > 0 && count($availableThemes) > 0){
		$selectedPopularity = (rand(0, 100000) / 100000) * $totalPopularity;
		
		$runningPopularity = $selectedPopularity;
		foreach($availableThemes as $i => $availableTheme){
			$runningPopularity -= $availableTheme["popularity"];
			if($runningPopularity <= 0){
				$selectedTheme = $availableTheme["theme"];
				break;
			}
		}
	}
	
	return $selectedTheme;
}

//Selects a random theme with equal probability for all themes, not caring for number of votes
function SelectRandomTheme(){
	global $themes;
	$minimumVotes = 10;
	
	$selectedTheme = "";
	
	$availableThemes = Array();
	foreach($themes as $id => $theme){
		$themeOption = Array();
		
		if($theme["banned"]){
			continue;
		}
		
		$themeOption["theme"] = $theme["theme"];
		
		$availableThemes[] = $themeOption;
	}
	
	if(count($availableThemes) > 0){
		$selectedIndex = rand(0, count($availableThemes));
		$selectedTheme = $availableThemes[$selectedIndex]["theme"];
	}
	
	return $selectedTheme;
}

//Adds the jam with the provided data into the database
function AddJamToDatabase($ip, $userAgent, $username, $jamNumber, $theme, $startTime, $colors){
	global $dbConn;
	
	$escapedIP = mysqli_real_escape_string($dbConn, $ip);
	$escapedUserAgent = mysqli_real_escape_string($dbConn, $userAgent);
	$escapedUsername = mysqli_real_escape_string($dbConn, $username);
	$escapedJamNumber = mysqli_real_escape_string($dbConn, $jamNumber);
	$escapedTheme = mysqli_real_escape_string($dbConn, $theme);
	$escapedStartTime = mysqli_real_escape_string($dbConn, $startTime);
	$escapedColors = mysqli_real_escape_string($dbConn, $colors);
	
	$sql = "
		INSERT INTO jam
		(jam_id,
		jam_datetime,
		jam_ip,
		jam_user_agent,
		jam_username,
		jam_jam_number,
		jam_theme,
		jam_start_datetime,
		jam_colors,
		jam_deleted)
		VALUES
		(null,
		Now(),
		'$escapedIP',
		'$escapedUserAgent',
		'$escapedUsername',
		'$escapedJamNumber',
		'$escapedTheme',
		'$escapedStartTime',
		'$escapedColors',
		0);";
	
	$data = mysqli_query($dbConn, $sql);
    $sql = "";
    
    AddToAdminLog("JAM_ADDED", "Jam scheduled with values: JamNumber: $jamNumber, Theme: '$theme', StartTime: '$startTime', Colors: $colors", "");
}

function GetJamsOfUserFormatted($username){
	global $dbConn;
	
	$escapedUsername = mysqli_real_escape_string($dbConn, $username);
	$sql = "
		SELECT *
		FROM jam
		WHERE jam_username = '$escapedUsername';
	";
	$data = mysqli_query($dbConn, $sql);
	$sql = "";
	
	return ArrayToHTML(MySQLDataToArray($data)); 
}

// Returns a jam given its number.
// The dictionary of jams must have been previously loaded.
function GetJamByNumber($jams, $jamNumber) {
	foreach ($jams as $jam) {
		if ($jam["jam_number"] == $jamNumber && $jam["jam_deleted"] != 1) {
			return $jam;
		}
	}

	return null;
}



?>