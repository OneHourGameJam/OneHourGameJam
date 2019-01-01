<?php

function LoadEntries(){
	global $dictionary, $jams, $authors, $entries, $users, $config, $dbConn, $nextJamTime, $loggedInUser;
	
	//Clear public lists which get updated by this function
	$dictionary["jams"] = Array();
	$dictionary["jams_with_deleted"] = Array();
	$dictionary["authors"] = Array();
	$dictionary["admin_candidates"] = Array();
	$jams = Array();
	$authors = Array();
	$entries = Array();
	
	//Create lists of jams and jam entries
	$authorList = Array();
	$firstJam = true;
	$jamFromStart = 1;
	$totalEntries = 0;
	$largest_jam_number = -1;
	
	$sql = "SELECT * FROM jam ORDER BY jam_jam_number DESC";
	$data = mysqli_query($dbConn, $sql);
	$sql = "";
	
	$suggestedNextJamTime = GetNextJamDateAndTime();
	$dictionary["next_jam_timer_code"] = gmdate("Y-m-d", $suggestedNextJamTime)."T".gmdate("H:i", $suggestedNextJamTime).":00Z";
	
	$currentJamData = GetCurrentJamNumberAndID();
	$latestStartedJamFound = false;

	while($info = mysqli_fetch_array($data)){
		
		//Read data about the jam
		$newData = Array();
		$newData["jam_number"] = intval($info["jam_jam_number"]);
		$newData["start_time"] = $info["jam_start_datetime"];
		$newData["jam_id"] = intval($info["jam_id"]);
		$newData["jam_number_ordinal"] = ordinal(intval($info["jam_jam_number"]));
		$newData["username"] = $info["jam_username"];
		$newData["theme"] = $info["jam_theme"];
		$newData["theme_visible"] = $info["jam_theme"]; //Used for administration
		$newData["date"] = date("d M Y", strtotime($info["jam_start_datetime"]));
		$newData["time"] = date("H:i", strtotime($info["jam_start_datetime"]));
		$newData["colors"] = Array();
		if(intval($info["jam_deleted"]) == 1){
			$newData["jam_deleted"] = 1;
		}
		$jamColors = explode("|", $info["jam_colors"]);
		if(count($jamColors) == 0){
			$jamColors = Array("FFFFFF");
		}
		foreach($jamColors as $num => $color){
			$newData["colors"][] = Array("number" => $num, "color" => "#".$color, "color_hex" => $color);
		}
		$newData["colors_input_string"] = implode("-", $jamColors);
		$newData["minutes_to_jam"] = floor((strtotime($info["jam_start_datetime"] ." UTC") - time()) / 60);
		$newData["entries"] = Array();
		$newData["first_jam"] = $firstJam;
		$newData["entries_visible"] = $jamFromStart <= 2;
		if($firstJam){
			$firstJam = false;
		}
		
		$newData["is_recent"] = (intval($newData["jam_number"]) > intval($currentJamData["NUMBER"]) - intval($config["JAMS_CONSIDERED_RECENT"]));
		
		$sql = "SELECT * FROM entry WHERE entry_jam_id = ".$newData["jam_id"]." ORDER BY entry_id ASC";
		$data2 = mysqli_query($dbConn, $sql);
		$sql = "";
		
		while($info2 = mysqli_fetch_array($data2)){
			$entry = Array();
			
			//Entry basic information
			$entry["entry_id"] = $info2["entry_id"];
			$entry["title"] = $info2["entry_title"];
			$entry["title_url_encoded"] = urlencode($info2["entry_title"]);
			$entry["description"] = $info2["entry_description"];
			if(intval($info2["entry_deleted"]) == 1){
				$entry["entry_deleted"] = 1;
			}
			
			//Entry color
			$entry["color"] = "#".$info2["entry_color"];
			$entry["color256_red"] = hexdec(substr($info2["entry_color"], 0, 2));
			$entry["color256_green"] = hexdec(substr($info2["entry_color"], 2, 2));
			$entry["color256_blue"] = hexdec(substr($info2["entry_color"], 4, 2));
			$entry["color_lighter"] = "#".str_pad(dechex( ($entry["color256_red"] + 255) / 2 ), 2, "0", STR_PAD_LEFT).str_pad(dechex( ($entry["color256_green"] + 255) / 2 ), 2, "0", STR_PAD_LEFT).str_pad(dechex( ($entry["color256_blue"] + 255) / 2 ), 2, "0", STR_PAD_LEFT);
			$entry["color_non_white"] = "#".str_pad(dechex(min($entry["color256_red"], 0xDD)), 2, "0", STR_PAD_LEFT).str_pad(dechex(min($entry["color256_green"], 0xDD)), 2, "0", STR_PAD_LEFT).str_pad(dechex(min($entry["color256_blue"], 0xDD)), 2, "0", STR_PAD_LEFT);
			$entry["color_number"] = rand(0, count($newData["colors"]) - 1);
			foreach($newData["colors"] as $j => $clr){
				if($clr["color_hex"] == $entry["color"]){
					$entry["color_number"] = $clr["number"];
				}
			}
			
			//Entry author
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
			$entry["url_source"] = str_replace("'", "\\'", $info2["entry_url_source"]);
			$entry["screenshot_url"] = str_replace("'", "\\'", $info2["entry_screenshot_url"]);
			
			if($entry["url"] != ""){$entry["has_url"] = 1;}
			if($entry["url_web"] != ""){$entry["has_url_web"] = 1;}
			if($entry["url_windows"] != ""){$entry["has_url_windows"] = 1;}
			if($entry["url_mac"] != ""){$entry["has_url_mac"] = 1;}
			if($entry["url_linux"] != ""){$entry["has_url_linux"] = 1;}
			if($entry["url_ios"] != ""){$entry["has_url_ios"] = 1;}
			if($entry["url_android"] != ""){$entry["has_url_android"] = 1;}
			if($entry["url_source"] != ""){$entry["has_url_source"] = 1;}
			
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

			//Has logged in user participated in this jam?
			if(!$entry["entry_deleted"]){
				if($loggedInUser["username"] == $author){
					$newData["user_participated_in_jam"] = 1;
				}
			}
			
			if(!isset($entry["entry_deleted"])){
				if(isset($authorList[$author])){
					$authorList[$author]["entry_count"] += 1;
					$authorList[$author]["recent_participation"] += (($newData["is_recent"]) ? (100.0 / $config["JAMS_CONSIDERED_RECENT"]) : 0);
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
					$authorList[$author]["recent_participation"] = (($newData["is_recent"]) ? (100.0 / $config["JAMS_CONSIDERED_RECENT"]) : 0);
					$authorList[$author]["first_jam_number"] = $newData["jam_number"];
					$authorList[$author]["last_jam_number"] = $newData["jam_number"];
					$authorList[$author]["entries"][] = $entry;
				}
			
				$newData["entries"][] = $entry;
				$entries[] = $entry;
			}
			$newData["entries_with_deleted"][] = $entry;
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
			if(!isset($newData["jam_deleted"])){
				$nextJamTime = strtotime($newData["start_time"]);
				$dictionary["next_jam_timer_code"] = date("Y-m-d", $nextJamTime)."T".date("H:i", $nextJamTime).":00Z";
			}
		}else{
			$newData["jam_started"] = true;

			if(!isset($newData["jam_deleted"])){
				if($latestStartedJamFound == false){
					$newData["is_latest_started_jam"] = 1;
					$latestStartedJamFound = true; 
				}
			}
		}
		
		//Insert into dictionary array
		if(!isset($newData["jam_deleted"])){
			$dictionary["jams"][] = $newData;
			$jams[] = $newData;
			$jamFromStart++;
			if($newData["jam_started"]){
				if($largest_jam_number < intval($newData["jam_number"])){
					$largest_jam_number = intval($newData["jam_number"]);
					$dictionary["current_jam"] = $newData;
				}
			}
		}
		$dictionary["jams_with_deleted"][] = $newData;
    }
    
    //Get list of sponsored users to be administration candidates, ensuring the voter is still an admin and the candidate hasn't become an admin since the vote was cast
	$sql = "
        SELECT v.vote_voter_username, v.vote_subject_username
        FROM admin_vote v, user u1, user u2
        WHERE v.vote_voter_username = u1.user_username
          AND u1.user_role = 1
          AND v.vote_subject_username = u2.user_username
          AND u2.user_role = 0
          AND v.vote_type = 'SPONSOR'
    ";
    $data = mysqli_query($dbConn, $sql);
    $sql = "";

    while($info = mysqli_fetch_array($data)){
        $voteVoterUsername = $info["vote_voter_username"];
        $voteSubjectUsername = $info["vote_subject_username"];

        $authorList[$voteSubjectUsername]["is_sponsored"] = 1;
        $authorList[$voteSubjectUsername]["sponsored_by"] = $voteVoterUsername;
    }

	//Process authors list
	foreach($authorList as $k => $authorData){
		//Find admin candidates
		if($authorList[$k]["recent_participation"] >= $config["ADMIN_SUGGESTION_RECENT_PARTICIPATION"]){
			$authorList[$k]["admin_candidate_recent_participation_check_pass"] = 1;
		}
		if($authorList[$k]["entry_count"] >= $config["ADMIN_SUGGESTION_TOTAL_PARTICIPATION"]){
			$authorList[$k]["admin_candidate_total_participation_check_pass"] = 1;
		}
		if(	$authorList[$k]["admin_candidate_recent_participation_check_pass"] &&
			$authorList[$k]["admin_candidate_total_participation_check_pass"]){
				$authorList[$k]["is_admin_candidate"] = 1;
		}
		
        //Find inactive admins (participation in jams)
        $jamsSinceLastParticipation = (count($jams) - $authorList[$k]["last_jam_number"]);
        $authorList[$k]["jams_since_last_participation"] = $jamsSinceLastParticipation;
		if($authorList[$k]["last_jam_number"] < (count($jams) - $config["ADMIN_ACTIVITY_JAMS_SINCE_LAST_PARTICIPATION_WARNING"])){
			$authorList[$k]["activity_jam_participation"] = "inactive";
			$authorList[$k]["activity_jam_participation_color"] = "#FFECEC";
		}else if($authorList[$k]["last_jam_number"] >= (count($jams) - $config["ADMIN_ACTIVITY_JAMS_SINCE_LAST_PARTICIPATION_GOOD"])){
			$authorList[$k]["activity_jam_participation"] = "highly active";
			$authorList[$k]["activity_jam_participation_color"] = "#ECFFEC";
		}else{
			$authorList[$k]["activity_jam_participation"] = "active";
			$authorList[$k]["activity_jam_participation_color"] = "#F6FFEC";
        }
		
        //Find inactive admins (days since last login)
		if($authorList[$k]["days_since_last_login"] > $config["ADMIN_ACTIVITY_DAYS_SINCE_LAST_LOGIN_WARNING"]){
			$authorList[$k]["activity_login"] = "inactive";
			$authorList[$k]["activity_login_color"] = "#FFECEC";
		}else if($authorList[$k]["days_since_last_login"] < $config["ADMIN_ACTIVITY_DAYS_SINCE_LAST_LOGIN_GOOD"]){
			$authorList[$k]["activity_login"] = "highly active";
			$authorList[$k]["activity_login_color"] = "#ECFFEC";
		}else{
            $authorList[$k]["activity_login"] = "active";
			$authorList[$k]["activity_login_color"] = "#F6FFEC";
        }
		
        //Find inactive admins (days since last login)
		if($authorList[$k]["days_since_last_admin_action"] > $config["ADMIN_ACTIVITY_DAYS_SINCE_LAST_ADMIN_ACTION_WARNING"]){
			$authorList[$k]["activity_administration"] = "inactive";
			$authorList[$k]["activity_administration_color"] = "#FFECEC";
		}else if($authorList[$k]["days_since_last_admin_action"] < $config["ADMIN_ACTIVITY_DAYS_SINCE_LAST_ADMIN_ACTION_GOOD"]){
			$authorList[$k]["activity_administration"] = "highly active";
			$authorList[$k]["activity_administration_color"] = "#ECFFEC";
		}else{
            $authorList[$k]["activity_administration"] = "active";
			$authorList[$k]["activity_administration_color"] = "#F6FFEC";
        }
	}
	
	//Insert authors into dictionary
	foreach($authorList as $k => $authorData){
		$dictionary["authors"][] = $authorData;
		
		//Update users list with entry count for each
		foreach($dictionary["users"] as $i => $dictUserInfo){
			if($dictUserInfo["username"] == $k){
				$dictionary["users"][$i]["entry_count"] = $authorData["entry_count"];
				$dictionary["users"][$i]["recent_participation"] = $authorData["recent_participation"];
				$dictionary["users"][$i]["first_jam_number"] = $authorData["first_jam_number"];
				$dictionary["users"][$i]["last_jam_number"] = $authorData["last_jam_number"];
			}
		}
		//Update admins list with entry count for each
		foreach($dictionary["admins"] as $i => $dictUserInfo){
			if($dictUserInfo["username"] == $k){
				$dictionary["admins"][$i]["entry_count"] = $authorData["entry_count"];
				$dictionary["admins"][$i]["recent_participation"] = $authorData["recent_participation"];
				$dictionary["admins"][$i]["first_jam_number"] = $authorData["first_jam_number"];
                $dictionary["admins"][$i]["last_jam_number"] = $authorData["last_jam_number"];
                $dictionary["admins"][$i]["activity_jam_participation"] = $authorData["activity_jam_participation"];
                switch($authorData["activity_jam_participation"]){
                    case "inactive":
                        $dictionary["admins"][$i]["activity_jam_participation_inactive"] = 1;
                        break;
                    case "active":
                        $dictionary["admins"][$i]["activity_jam_participation_active"] = 1;
                        break;
                    case "highly active":
                        $dictionary["admins"][$i]["activity_jam_participation_highly_active"] = 1;
                        break;
                }
				$dictionary["admins"][$i]["activity_jam_participation_color"] = $authorData["activity_jam_participation_color"];
				$dictionary["admins"][$i]["activity_login"] = $authorData["activity_login"];
                switch($authorData["activity_login"]){
                    case "inactive":
                        $dictionary["admins"][$i]["activity_login_inactive"] = 1;
                        break;
                    case "active":
                        $dictionary["admins"][$i]["activity_login_active"] = 1;
                        break;
                    case "highly active":
                        $dictionary["admins"][$i]["activity_login_highly_active"] = 1;
                        break;
                }
				$dictionary["admins"][$i]["activity_login_color"] = $authorData["activity_login_color"];
				$dictionary["admins"][$i]["activity_administration"] = $authorData["activity_administration"];
                switch($authorData["activity_administration"]){
                    case "inactive":
                        $dictionary["admins"][$i]["activity_administration_inactive"] = 1;
                        break;
                    case "active":
                        $dictionary["admins"][$i]["activity_administration_active"] = 1;
                        break;
                    case "highly active":
                        $dictionary["admins"][$i]["activity_administration_highly_active"] = 1;
                        break;
                }
                $dictionary["admins"][$i]["activity_administration_color"] = $authorData["activity_administration_color"];
			}
		}
		//Update registered users list with entry count for each
		foreach($dictionary["registered_users"] as $i => $dictUserInfo){
			if($dictUserInfo["username"] == $k){
				$dictionary["registered_users"][$i]["entry_count"] = $authorData["entry_count"];
				$dictionary["registered_users"][$i]["recent_participation"] = $authorData["recent_participation"];
				$dictionary["registered_users"][$i]["first_jam_number"] = $authorData["first_jam_number"];
				$dictionary["registered_users"][$i]["last_jam_number"] = $authorData["last_jam_number"];
				if(isset($authorData["is_admin_candidate"]) || isset($authorData["is_sponsored"])){
                    $dictionary["registered_users"][$i]["is_admin_candidate"] = 1;
                    if(isset($authorData["is_sponsored"])){
                        $dictionary["registered_users"][$i]["is_sponsored"] = 1;
                    }
					$dictionary["registered_users"][$i]["sponsored_by"] = $authorData["sponsored_by"];
					$dictionary["admin_candidates"][] = $dictionary["registered_users"][$i];
				}
			}
		}
		$authors[$authorData["username"]] = $authorData;
	}
	
	$dictionary["all_authors_count"] = count($authors);
	$dictionary["all_jams_count"] = count($jams);
	
	$dictionary["all_entries_count"] = $totalEntries;
	$dictionary["entries"] = $entries;
	
	//Prepare data for "Manage content" charts
	$jsFormattedThemesList = Array();
	$jsFormattedEntriesCountList = Array();
	foreach($jams as $id => $jam){
		$jsFormattedThemesList[] = "\"".str_replace("\"", "\\\"", $jam["theme"])."\"";
		$jsFormattedEntriesCountList[] = count($jam["entries"]);
	}
	$dictionary["js_formatted_themes_list"] = implode(",", array_reverse($jsFormattedThemesList));
	$dictionary["js_formatted_entries_count_list"] = implode(",", array_reverse($jsFormattedEntriesCountList));
	
	//Prepare data for "Manage users" charts
	$jsFormattedFirstTimeNumberList = Array();
	$jsFormattedLastTimeNumberList = Array();
	$jsFormattedFirstVsLastTimeDifferenceNumberList = Array();
	
	foreach($jams as $id => $jam){
		$jsFormattedFirstTimeNumberList[$jam["jam_number"]] = 0;
		$jsFormattedLastTimeNumberList[$jam["jam_number"]] = 0;
		$jsFormattedFirstVsLastTimeDifferenceNumberList[$jam["jam_number"]] = 0;
	}
	
	foreach($authorList as $id => $author){
		$firstJamNumber = $author["first_jam_number"];
		$lastJamNumber = $author["last_jam_number"];
		$jsFormattedFirstTimeNumberList[$firstJamNumber]++;
		$jsFormattedLastTimeNumberList[$lastJamNumber]--;
		
		$jsFormattedFirstVsLastTimeDifferenceNumberList[$firstJamNumber]++;
		$jsFormattedFirstVsLastTimeDifferenceNumberList[$lastJamNumber]--;
	}
	$dictionary["js_formatted_first_time_number_list"] = implode(",", array_reverse($jsFormattedFirstTimeNumberList));
	$dictionary["js_formatted_last_time_number_list"] = implode(",", array_reverse($jsFormattedLastTimeNumberList));
	$dictionary["js_formatted_first_vs_last_time_difference_number_list"] = implode(",", array_reverse($jsFormattedFirstVsLastTimeDifferenceNumberList));
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

//Returns true / false based on whether or not the specified entry exists (and has not been deleted)
function EntryExists($entryID){
	global $dbConn;
	
	//Validate values
	$entryID = intval($entryID);
	if($entryID <= 0){
		return FALSE;
	}
	
	$escapedEntryID = mysqli_real_escape_string($dbConn, "$entryID");
	
	$sql = "
		SELECT 1
		FROM entry
		WHERE entry_id = $escapedEntryID
		AND entry_deleted = 0;
		";
	$data = mysqli_query($dbConn, $sql);
	$sql = "";
	
	if(mysqli_fetch_array($data)){
		return true;
	}else{
		return false;
	}
}

function GetEntriesOfUserFormatted($author){
	global $dbConn;
	
	$escapedAuthor = mysqli_real_escape_string($dbConn, $author);
	$sql = "
		SELECT *
		FROM entry
		WHERE entry_author = '$escapedAuthor';
	";
	$data = mysqli_query($dbConn, $sql);
	$sql = "";
	
	return ArrayToHTML(MySQLDataToArray($data)); 
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

?>