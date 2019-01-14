<?php

function LoadJams(){
	global $dbConn;

	AddActionLog("LoadJams");
	StartTimer("LoadJams");

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

	StopTimer("LoadJams");
	return $jams;
}

function ParseJamColors($colorString){
	AddActionLog("ParseJamColors");
	StartTimer("ParseJamColors");

	$jamColors = explode("|", $colorString);
	if(count($jamColors) == 0){
		StopTimer("ParseJamColors");
		return Array("FFFFFF");
	}

	StopTimer("ParseJamColors");
	return $jamColors;
}

function RenderJam(&$jam, $nonDeletedJamCounter, &$config, &$games, &$users, $loggedInUser){
	AddActionLog("RenderJam");
	StartTimer("RenderJam");

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
	$jamData["entries_count"] = 0;
	foreach($games as $j => $game){
		if($game["jam_id"] == $jamData["jam_id"]){
			$jamData["entries"][] = RenderGame($game, $jams, $users);

			if(!$game["entry_deleted"]){
				//Has logged in user participated in this jam?
				if($loggedInUser["username"] == $game["author"]){
					$jamData["user_participated_in_jam"] = 1;
				}

				//Count non-deleted entries in jam
				$jamData["entries_count"] += 1;
			}
		}
	}

	//Hide theme of not-yet-started jams
	$now = new DateTime();
	$datetime = new DateTime($jamData["start_time"] . " UTC");
	$timeUntilJam = date_diff($datetime, $now);

	$jamData["first_jam"] = $nonDeletedJamCounter == 1;
	$jamData["entries_visible"] = $nonDeletedJamCounter <= 2;

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

	StopTimer("RenderJam");
	return $jamData;
}

function RenderSubmitJam($jam, $config, $games, $users, $loggedInUser){
	AddActionLog("RenderSubmitJam");
	return RenderJam($jam, 0, $config, $games, $users, $loggedInUser);
}

function RenderJams(&$jams, &$config, &$games, &$users, $loggedInUser){
	AddActionLog("RenderJams");
	StartTimer("RenderJams");

	$render = Array("LIST" => Array());
	$render["next_jam_timer_code"] = "".gmdate("Y-m-d H:i", GetNextJamDateAndTime());

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

	StopTimer("RenderJams");
	return $render;
}



//Checks if a jam is scheduled. If not and a jam is coming up, one is scheduled automatically.
function CheckNextJamSchedule(){
	global $themes, $jams, $config;
	AddActionLog("CheckNextJamSchedule");
	StartTimer("CheckNextJamSchedule");

	StopTimer("CheckNextJamSchedule");

	if($config["JAM_AUTO_SCHEDULER_ENABLED"]["VALUE"] == 0){
		return;
	}

	$autoScheduleThreshold = $config["JAM_AUTO_SCHEDULER_MINUTES_BEFORE_JAM"]["VALUE"] * 60;

	$suggestedNextJamTime = GetNextJamDateAndTime();
	$now = time();
	$interval = $suggestedNextJamTime - $now;
	$colors = "e38484|e3b684|dee384|ade384|84e38d|84e3be|84d6e3|84a4e3|9684e3|c784e3";

	if($interval > 0 && $interval <= $autoScheduleThreshold){
		if($nextJamTime != ""){
			//A future jam is already scheduled
			StopTimer("CheckNextJamSchedule");
			return;
		}

		$jamAlreadyScheduled = false;

		foreach($jams as $i => $jam){
			if($jam["jam_deleted"]){
				continue;
			}

			$jamStartTime = strtotime($jam["start_time"]);
			if($jamStartTime > $now){
				$jamAlreadyScheduled = true;
			}
		}

		if($jamAlreadyScheduled){
			StopTimer("CheckNextJamSchedule");
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
	StopTimer("CheckNextJamSchedule");
}

//Selects a random theme (or "" if none can be selected) by calculating the difference between positive and negative votes and
//selecting a proportional random theme by this difference
function SelectRandomThemeByVoteDifference(){
	global $themes, $config;
	AddActionLog("SelectRandomThemeByVoteDifference");
	StartTimer("SelectRandomThemeByVoteDifference");

	$minimumVotes = $config["THEME_MIN_VOTES_TO_SCORE"]["VALUE"];

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

	StopTimer("SelectRandomThemeByVoteDifference");
	return $selectedTheme;
}

//Selects a random theme (or "" if none can be selected) proportionally based on its popularity.
function SelectRandomThemeByPopularity(){
	global $themes, $config;
	AddActionLog("SelectRandomThemeByPopularity");
	StartTimer("SelectRandomThemeByPopularity");

	$minimumVotes = $config["THEME_MIN_VOTES_TO_SCORE"]["VALUE"];

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

	StopTimer("SelectRandomThemeByPopularity");
	return $selectedTheme;
}

//Selects a random theme with equal probability for all themes, not caring for number of votes
function SelectRandomTheme(){
	global $themes;
	AddActionLog("SelectRandomTheme");
	StartTimer("SelectRandomTheme");

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

	StopTimer("SelectRandomTheme");
	return $selectedTheme;
}

//Adds the jam with the provided data into the database
function AddJamToDatabase($ip, $userAgent, $username, $jamNumber, $theme, $startTime, $colors){
	global $dbConn;
	AddActionLog("AddJamToDatabase");
	StartTimer("AddJamToDatabase");

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

	StopTimer("AddJamToDatabase");
    AddToAdminLog("JAM_ADDED", "Jam scheduled with values: JamNumber: $jamNumber, Theme: '$theme', StartTime: '$startTime', Colors: $colors", "");
}

function GetJamsOfUserFormatted($username){
	global $dbConn;
	AddActionLog("GetJamsOfUserFormatted");
	StartTimer("GetJamsOfUserFormatted");

	$escapedUsername = mysqli_real_escape_string($dbConn, $username);
	$sql = "
		SELECT *
		FROM jam
		WHERE jam_username = '$escapedUsername';
	";
	$data = mysqli_query($dbConn, $sql);
	$sql = "";

	StopTimer("GetJamsOfUserFormatted");
	return ArrayToHTML(MySQLDataToArray($data));
}

// Returns a jam given its number.
// The dictionary of jams must have been previously loaded.
function GetJamByNumber($jams, $jamNumber) {
	AddActionLog("GetJamByNumber");
	StartTimer("GetJamByNumber");

	foreach ($jams as $jam) {
		if ($jam["jam_number"] == $jamNumber && $jam["jam_deleted"] != 1) {
			StopTimer("GetJamByNumber");
			return $jam;
		}
	}

	StopTimer("GetJamByNumber");
	return null;
}



?>