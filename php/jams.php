<?php

class Jam{
	public $Id;
	public $Username;
	public $JamNumber;
	public $Theme;
	public $StartTime;
	public $Colors;
	public $Deleted;
}

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
		$jam = new Jam();
		$jamID = intval($info["jam_id"]);

		$jam->Id = $jamID;
		$jam->Username = $info["jam_username"];
		$jam->JamNumber = intval($info["jam_jam_number"]);
		$jam->Theme = $info["jam_theme"];
		$jam->StartTime = $info["jam_start_datetime"];
		$jam->Colors = ParseJamColors($info["jam_colors"]);
		$jam->Deleted = $info["jam_deleted"];

		$jams[$jamID] = $jam;
	}

	StopTimer("LoadJams");
	return $jams;
}

function GetNextJamDateAndTime(&$jams){
	AddActionLog("GetNextJamDateAndTime");
	StartTimer("GetNextJamDateAndTime");

	$nextJamStartTime = null;

	$now = time();
	foreach($jams as $i => $jamData){
		$nextJamTime = strtotime($jamData->StartTime . " UTC");

		if($nextJamTime > $now){
			$nextJamStartTime = $nextJamTime;
		}
	}

	StopTimer("GetNextJamDateAndTime");
	return $nextJamStartTime;
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

function RenderJam(&$config, &$users, &$games, &$jam, &$jams, &$satisfaction, &$loggedInUser, $nonDeletedJamCounter, $renderDepth){
	AddActionLog("RenderJam");
	StartTimer("RenderJam");

	$render = Array();

	$render["jam_id"] = $jam->Id;
	$render["username"] = $jam->Username;
	$render["jam_number"] = $jam->JamNumber;
	$render["theme"] = $jam->Theme;
	$render["start_time"] = $jam->StartTime;

	if($jam->Deleted == 1){
		$render["jam_deleted"] = 1;
	}

	$render["theme_visible"] = $jam->Theme; //Theme is visible to admins
	$render["jam_number_ordinal"] = ordinal(intval($jam->JamNumber));
	$render["date"] = date("d M Y", strtotime($jam->StartTime));
	$render["time"] = date("H:i", strtotime($jam->StartTime));

	//Jam Colors
	$render["colors"] = Array();
	foreach($jam->Colors as $num => $color){
		$render["colors"][] = Array("number" => $num, "color" => "#".$color, "color_hex" => $color);
	}
	$render["colors_input_string"] = implode("-", $jam->Colors);

	$render["minutes_to_jam"] = floor((strtotime($jam->StartTime ." UTC") - time()) / 60);

	//Games in jam
	$render["entries"] = Array();
	$render["entries_count"] = 0;
	foreach($games as $j => $game){
		if($game["jam_id"] == $render["jam_id"]){
			if(($renderDepth & RENDER_DEPTH_GAMES) > 0){
				$render["entries"][] = RenderGame($users, $game, $jams, $renderDepth & ~RENDER_DEPTH_JAMS);
			}

			if(!$game["entry_deleted"]){
				//Has logged in user participated in this jam?
				if($loggedInUser !== false){
					if($loggedInUser->Username == $game["author"]){
						$render["user_participated_in_jam"] = 1;
					}
				}

				//Count non-deleted entries in jam
				$render["entries_count"] += 1;
			}
		}
	}
	$render["entries"] = array_reverse($render["entries"]);

	//Hide theme of not-yet-started jams
	$now = new DateTime();
	$datetime = new DateTime($render["start_time"] . " UTC");
	$timeUntilJam = date_diff($datetime, $now);

	$render["first_jam"] = $nonDeletedJamCounter == 1;
	$render["entries_visible"] = $nonDeletedJamCounter <= 2;

	if($datetime > $now){
		$render["theme"] = "Not yet announced";
		$render["jam_started"] = false;
		if($timeUntilJam->days > 0){
			$render["time_left"] = $timeUntilJam->format("%a days %H:%I:%S");
		}else if($timeUntilJam->h > 0){
			$render["time_left"] = $timeUntilJam->format("%H:%I:%S");
		}else  if($timeUntilJam->i > 0){
			$render["time_left"] = $timeUntilJam->format("%I:%S");
		}else if($timeUntilJam->s > 0){
			$render["time_left"] = $timeUntilJam->format("%S seconds");
		}else{
			$render["time_left"] = "Now!";
		}
	}else{
		$render["jam_started"] = true;
	}
	
	$render["satisfaction"] = "No Data";
	if(isset($satisfaction["JAM_".$render["jam_number"]])){
		$arrayId = "JAM_".$render["jam_number"];

		$satisfactionSum = 0;
		$satisfactionCount = 0;
		foreach($satisfaction[$arrayId]->Scores as $score => $votes){
			$satisfactionSum += $score * $votes;
			$satisfactionCount += $votes;
		}
		$satisfactionAverage = $satisfactionSum / $satisfactionCount;

		$render["satisfaction_average_score"] = $satisfactionAverage;
		$render["satisfaction_submitted_scores"] = $satisfactionCount;
		$render["enough_scores_to_show_satisfaction"] = $satisfactionCount >= $config["SATISFACTION_RATINGS_TO_SHOW_SCORE"]["VALUE"];
		$render["score-5"] = $satisfaction[$arrayId]->Scores[-5];
		$render["score-4"] = $satisfaction[$arrayId]->Scores[-4];
		$render["score-3"] = $satisfaction[$arrayId]->Scores[-3];
		$render["score-2"] = $satisfaction[$arrayId]->Scores[-2];
		$render["score-1"] = $satisfaction[$arrayId]->Scores[-1];
		$render["score0"] = $satisfaction[$arrayId]->Scores[0];
		$render["score1"] = $satisfaction[$arrayId]->Scores[1];
		$render["score2"] = $satisfaction[$arrayId]->Scores[2];
		$render["score3"] = $satisfaction[$arrayId]->Scores[3];
		$render["score4"] = $satisfaction[$arrayId]->Scores[4];
		$render["score5"] = $satisfaction[$arrayId]->Scores[5];
	}

	StopTimer("RenderJam");
	return $render;
}

function RenderSubmitJam(&$config, &$users, &$games, &$jam, &$jams, &$satisfaction, &$loggedInUser, $renderDepth){
	AddActionLog("RenderSubmitJam");

	return RenderJam($config, $users, $games, $jam, $jams, $satisfaction, $loggedInUser, 0, $renderDepth);
}

function RenderJams(&$config, &$users, &$games, &$jams, &$satisfaction, &$loggedInUser, $renderDepth, $loadAll){
	AddActionLog("RenderJams");
	StartTimer("RenderJams");

	$render = Array("LIST" => Array());
	$suggestedNextGameJamTime = GetSuggestedNextJamDateTime($config);
	$render["next_jam_timer_code"] = gmdate("Y-m-d", $suggestedNextGameJamTime)."T".gmdate("H:i", $suggestedNextGameJamTime).":00Z";

    $nonDeletedJamCounter = 0;
	$latestStartedJamFound = false;
	$currentJamData = GetCurrentJamNumberAndID();

	$jamsToLoad = $config["JAMS_TO_LOAD"]["VALUE"];

	$allJamsLoaded = true;
	$render["current_jam"] = $currentJamData["NUMBER"] !== 0;

	foreach($jams as $i => $jam){
		if($jam->Deleted != 1){
			$nonDeletedJamCounter += 1;
		}
		if($loadAll || $nonDeletedJamCounter <= $jamsToLoad)
		{
			if(($renderDepth & RENDER_DEPTH_JAMS) > 0){
				$jamData = RenderJam($config, $users, $games, $jam, $jams, $satisfaction, $loggedInUser, $nonDeletedJamCounter, $renderDepth);

				$now = time();
				$datetime = strtotime($jamData["start_time"] . " UTC");
				if($datetime > $now){
					$render["next_jam_timer_code"] = gmdate("Y-m-d", $datetime)."T".gmdate("H:i", $datetime).":00Z";
				}else{
					if(!isset($jamData["jam_deleted"])){
						if($latestStartedJamFound == false){
							$jamData["is_latest_started_jam"] = 1;
							$latestStartedJamFound = true;
						}
					}
				}
	
				$render["LIST"][] = $jamData;
			}
			if($currentJamData["ID"] == $jam->Id){
				$render["current_jam"] = RenderJam($config, $users, $games, $jam, $jams, $satisfaction, $loggedInUser, $nonDeletedJamCounter, $renderDepth);
			}
		}else{
			$allJamsLoaded = false;
			continue;
		}
    }

	$render["all_jams_loaded"] = $allJamsLoaded;
	$render["all_jams_count"] = $nonDeletedJamCounter;

	StopTimer("RenderJams");
	return $render;
}



//Checks if a jam is scheduled. If not and a jam is coming up, one is scheduled automatically.
function CheckNextJamSchedule(&$config, &$jams, &$themes, $nextScheduledJamTime, $nextSuggestedJamTime){
	AddActionLog("CheckNextJamSchedule");
	StartTimer("CheckNextJamSchedule"); 

	//print "<br>CHECK JAM SCHEDULING";

	if($config["JAM_AUTO_SCHEDULER_ENABLED"]["VALUE"] == 0){
		//print "<br>AUTO SCHEDULER DISABLED";
		StopTimer("CheckNextJamSchedule");
		return;
	}

	//print "<br>AUTO SCHEDULER ENABLED";
	$autoScheduleThreshold = $config["JAM_AUTO_SCHEDULER_MINUTES_BEFORE_JAM"]["VALUE"] * 60;

	$now = time();
	$timeToNextScheduledJam = $nextScheduledJamTime - $now;
	$timeToNextSuggestedJam = $nextSuggestedJamTime - $now;

	$isTimeToScheduleJam = $timeToNextSuggestedJam > 0 && $timeToNextSuggestedJam <= $autoScheduleThreshold;
	$isAJamAlreadyScheduled = $timeToNextScheduledJam > 0;
	$isAJamScheduledInAuthSchedulerThresholdTime = $isAJamAlreadyScheduled && $timeToNextScheduledJam <= $autoScheduleThreshold;

	//print "<br>nextScheduledJamTime = ".gmdate("Y-m-d H:i:s", $nextScheduledJamTime);
	//print "<br>nextSuggestedJamTime = ".gmdate("Y-m-d H:i:s", $nextSuggestedJamTime);
	//print "<br>now = ".gmdate("Y-m-d H:i:s", $now);
	//print "<br>timeToNextScheduledJam = $timeToNextScheduledJam";
	//print "<br>timeToNextSuggestedJam = $timeToNextSuggestedJam";
	//print "<br>autoScheduleThreshold = $autoScheduleThreshold";
	//print "<br>isTimeToScheduleJam = ".($isTimeToScheduleJam ? "YES" : "NO");
	//print "<br>isAJamAlreadyScheduled = ".($isAJamAlreadyScheduled ? "YES" : "NO");
	//print "<br>isAJamScheduledInAuthSchedulerThresholdTime = ".($isAJamScheduledInAuthSchedulerThresholdTime ? "YES" : "NO");

	$colors = "e38484|e3b684|dee384|ade384|84e38d|84e3be|84d6e3|84a4e3|9684e3|c784e3";

	if($isTimeToScheduleJam){
		//print "<br>IT IS TIME TO SCHEDULE A JAM";

		if($isAJamScheduledInAuthSchedulerThresholdTime){
			//A future jam is already scheduled
			//print "<br>A JAM IS ALREADY SCHEDULED";
			return;
		}

		$selectedTheme = "";

		$selectedTheme = SelectRandomThemeByVoteDifference($themes, $config);
		if($selectedTheme == ""){
			$selectedTheme = SelectRandomThemeByPopularity($themes, $config);
		}
		if($selectedTheme == ""){
			$selectedTheme = SelectRandomTheme($themes);
		}
		if($selectedTheme == ""){
			$selectedTheme = "Any theme";
		}

		//print "<br>A THEME WAS SELECTED";

		$currentJamData = GetCurrentJamNumberAndID();
		$jamNumber = intval($currentJamData["NUMBER"] + 1);
		//print "<br>A JAM NUMBER WAS SELECTED: ".$jamNumber;

		AddJamToDatabase("127.0.0.1", "AUTO", "AUTOMATIC", $jamNumber, $selectedTheme, "".gmdate("Y-m-d H:i", $nextSuggestedJamTime), $colors, Array("username" => "AUTOMATIC"));
	}
	
	StopTimer("CheckNextJamSchedule");
}

//Selects a random theme (or "" if none can be selected) by calculating the difference between positive and negative votes and
//selecting a proportional random theme by this difference
function SelectRandomThemeByVoteDifference(&$themes, &$config){
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

		$votesFor = $theme->VotesFor;
		$votesNeutral = $theme->VotesNeutral;
		$votesAgainst = $theme->VotesAgainst;
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

		$themeOption["theme"] = $theme->Theme;
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
function SelectRandomThemeByPopularity(&$themes, &$config){
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

		$votesFor = $theme->VotesFor;
		$votesNeutral = $theme->VotesNeutral;
		$votesAgainst = $theme->VotesAgainst;
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

		$themeOption["theme"] = $theme->Theme;
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
function SelectRandomTheme(&$themes){
	AddActionLog("SelectRandomTheme");
	StartTimer("SelectRandomTheme");

	$selectedTheme = "";

	$availableThemes = Array();
	foreach($themes as $id => $theme){
		$themeOption = Array();

		if($theme->Banned){
			continue;
		}

		$themeOption["theme"] = $theme->Theme;

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
function AddJamToDatabase($ip, $userAgent, $username, $jamNumber, $theme, $startTime, $colors, $loggedInUser){
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
    AddToAdminLog("JAM_ADDED", "Jam scheduled with values: JamNumber: $jamNumber, Theme: '$theme', StartTime: '$startTime', Colors: $colors", "", $loggedInUser->Username);
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
function GetJamByNumber(&$jams, $jamNumber) {
	AddActionLog("GetJamByNumber");
	StartTimer("GetJamByNumber");

	foreach ($jams as $jam) {
		if ($jam->JamNumber == $jamNumber && $jam->Deleted != 1) {
			StopTimer("GetJamByNumber");
			return $jam;
		}
	}

	StopTimer("GetJamByNumber");
	return null;
}



?>