<?php

//Loads users
function LoadUsers(){
	global $dictionary, $dbConn;
	AddActionLog("LoadUsers");
	StartTimer("LoadUsers");

	$users = Array();

	$sql = "SELECT user_id, user_username, user_display_name, user_twitter, user_email,
                   user_password_salt, user_password_hash, user_password_iterations, user_role,
                   DATEDIFF(Now(), user_last_login_datetime) AS days_since_last_login,
                   DATEDIFF(Now(), log_max_datetime) AS days_since_last_admin_action
            FROM
                user u LEFT JOIN
                (
                    SELECT log_admin_username, max(log_datetime) AS log_max_datetime
                    FROM admin_log
                    GROUP BY log_admin_username
                ) al ON u.user_username = al.log_admin_username";
	$data = mysqli_query($dbConn, $sql);
    $sql = "";

	while($info = mysqli_fetch_array($data)){
		//Read data about the user
		$currentUser = Array();
		$currentUser["id"] = $info["user_id"];
		$currentUser["username"] = $info["user_username"];
		$currentUser["display_name"] = $info["user_display_name"];
		$currentUser["twitter"] = $info["user_twitter"];
		$currentUser["twitter_text_only"] = str_replace("@", "", $info["user_twitter"]);
		$currentUser["email"] = $info["user_email"];
		$currentUser["salt"] = $info["user_password_salt"];
		$currentUser["password_hash"] = $info["user_password_hash"];
		$currentUser["password_iterations"] = intval($info["user_password_iterations"]);
        $currentUser["admin"] = intval($info["user_role"]);

        //This fixes an issue where user_last_login_datetime was not set properly in the database, which results in days_since_last_login being null for users who have not logged in since the fix was applied
        if($info["days_since_last_login"] == null){
            $info["days_since_last_login"] = 1000000;
        }

        //For cases where users have never performed an admin action
        if($info["days_since_last_admin_action"] == null){
            $info["days_since_last_admin_action"] = 1000000;
        }

		$currentUser["days_since_last_login"] = intval($info["days_since_last_login"]);
		$currentUser["days_since_last_admin_action"] = intval($info["days_since_last_admin_action"]);

		$users[$currentUser["username"]] = $currentUser;
	}

    ksort($users);

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

        $users[$voteSubjectUsername]["is_sponsored"] = 1;
        $users[$voteSubjectUsername]["sponsored_by"] = $voteVoterUsername;
    }

	StopTimer("LoadUsers");
	return $users;
}

function RenderUser(&$user, &$users, &$games, &$jams, &$config){
    global $nightmode;

	AddActionLog("RenderUser");
	StartTimer("RenderUser");
    $userData = $user;

    $currentJamData = GetCurrentJamNumberAndID();

    $username = $userData["username"];
    $userData["recent_participation"] = 0;

    //Determine if this user is an author and their participation
	StartTimer("RenderUser - foreach games");
    foreach($games as $j => $gameData){
        if($gameData["author"] != $username){
            continue;
        }

        if($gameData["entry_deleted"] == 1){
            continue;
        }

	    StartTimer("RenderUser - foreach games - Foreach Jams");
        foreach($jams as $k => $jam){
            if($jam["jam_id"] == $gameData["jam_id"]){
                $jamData = $jam;
                break;
            }
        }
	    StopTimer("RenderUser - foreach games - Foreach Jams");

	    StartTimer("RenderUser - foreach games - entry count, first and last jam, recent");
        $userData["is_author"] = 1;
        if(!isset($userData["entry_count"])){
            $userData["entry_count"] = 0;
            $userData["first_jam_number"] = $gameData["jam_number"];
            $userData["last_jam_number"] = $gameData["jam_number"];
        }

        $userData["entry_count"] += 1;

        if($gameData["jam_number"] < $userData["first_jam_number"] ){
            $userData["first_jam_number"] = $gameData["jam_number"];
        }
        if($gameData["jam_number"] > $userData["last_jam_number"] ){
            $userData["last_jam_number"] = $gameData["jam_number"];
        }

        $isJamRecent = intval($jamData["jam_number"]) > (intval($currentJamData["NUMBER"]) - intval($config["JAMS_CONSIDERED_RECENT"]["VALUE"]));
        if($isJamRecent){
            $userData["recent_participation"] += 100.0 / $config["JAMS_CONSIDERED_RECENT"]["VALUE"];
        }

	    StopTimer("RenderUser - foreach games - entry count, first and last jam, recent");

	    StartTimer("RenderUser - foreach games - RenderGame");
        $userData["entries"][] = RenderGame($gameData, $jams, $users);
	    StopTimer("RenderUser - foreach games - RenderGame");
    }
	StopTimer("RenderUser - foreach games");

    //Find admin candidates
	StartTimer("RenderUser - admin candidates");
    if($userData["recent_participation"] >= $config["ADMIN_SUGGESTION_RECENT_PARTICIPATION"]["VALUE"]){
        $userData["admin_candidate_recent_participation_check_pass"] = 1;
    }
    if($userData["entry_count"] >= $config["ADMIN_SUGGESTION_TOTAL_PARTICIPATION"]["VALUE"]){
        $userData["admin_candidate_total_participation_check_pass"] = 1;
    }
    if(	$userData["admin_candidate_recent_participation_check_pass"] &&
        $userData["admin_candidate_total_participation_check_pass"]){
            $userData["system_suggestsed_admin_candidate"] = 1;
    }
	StopTimer("RenderUser - admin candidates");

    StartTimer("RenderUser - inactive admins");

    $inactiveColor = "#FFECEC";
    $activeColor = "#F6FFEC";
    $highlyAciveColor = "#ECFFEC";

    if($nightmode == 1)
    {
        $inactiveColor = "#4A3636";
        $activeColor = "#3E4A36";
        $highlyAciveColor = "#364A36";
    }

    //Find inactive admins (participation in jams)
    $jamsSinceLastParticipation = ($currentJamData["NUMBER"] - $userData["last_jam_number"]);
    $userData["jams_since_last_participation"] = $jamsSinceLastParticipation;
    if($userData["last_jam_number"] < ($currentJamData["NUMBER"] - $config["ADMIN_ACTIVITY_JAMS_SINCE_LAST_PARTICIPATION_WARNING"]["VALUE"])){
        $userData["activity_jam_participation"] = "inactive";
        $userData["activity_jam_participation_color"] = $inactiveColor;
    }else if($userData["last_jam_number"] >= ($currentJamData["NUMBER"] - $config["ADMIN_ACTIVITY_JAMS_SINCE_LAST_PARTICIPATION_GOOD"]["VALUE"])){
        $userData["activity_jam_participation"] = "highly active";
        $userData["activity_jam_participation_color"] = $highlyAciveColor;
    }else{
        $userData["activity_jam_participation"] = "active";
        $userData["activity_jam_participation_color"] = $activeColor;
    }

    //Find inactive admins (days since last login)
    if($userData["days_since_last_login"] > $config["ADMIN_ACTIVITY_DAYS_SINCE_LAST_LOGIN_WARNING"]["VALUE"]){
        $userData["activity_login"] = "inactive";
        $userData["activity_login_color"] = $inactiveColor;
    }else if($userData["days_since_last_login"] < $config["ADMIN_ACTIVITY_DAYS_SINCE_LAST_LOGIN_GOOD"]["VALUE"]){
        $userData["activity_login"] = "highly active";
        $userData["activity_login_color"] = $highlyAciveColor;
    }else{
        $userData["activity_login"] = "active";
        $userData["activity_login_color"] = $activeColor;
    }

    //Find inactive admins (days since last login)
    if($userData["days_since_last_admin_action"] > $config["ADMIN_ACTIVITY_DAYS_SINCE_LAST_ADMIN_ACTION_WARNING"]["VALUE"]){
        $userData["activity_administration"] = "inactive";
        $userData["activity_administration_color"] = $inactiveColor;
    }else if($userData["days_since_last_admin_action"] < $config["ADMIN_ACTIVITY_DAYS_SINCE_LAST_ADMIN_ACTION_GOOD"]["VALUE"]){
        $userData["activity_administration"] = "highly active";
        $userData["activity_administration_color"] = $highlyAciveColor;
    }else{
        $userData["activity_administration"] = "active";
        $userData["activity_administration_color"] = $activeColor;
    }

    //Render activity related statuses (inactive, active, highly active)
    switch($userData["activity_jam_participation"]){
        case "inactive":
            $userData["activity_jam_participation_inactive"] = 1;
            break;
        case "active":
            $userData["activity_jam_participation_active"] = 1;
            break;
        case "highly active":
            $userData["activity_jam_participation_highly_active"] = 1;
            break;
    }
    switch($userData["activity_login"]){
        case "inactive":
            $userData["activity_login_inactive"] = 1;
            break;
        case "active":
            $userData["activity_login_active"] = 1;
            break;
        case "highly active":
            $userData["activity_login_highly_active"] = 1;
            break;
    }
    switch($userData["activity_administration"]){
        case "inactive":
            $userData["activity_administration_inactive"] = 1;
            break;
        case "active":
            $userData["activity_administration_active"] = 1;
            break;
        case "highly active":
            $userData["activity_administration_highly_active"] = 1;
            break;
    }
    StopTimer("RenderUser - inactive admins");

    StartTimer("RenderUser - Finish");
    //Mark system suggested and admin-sponsored users as admin candidates
    if(isset($userData["system_suggestsed_admin_candidate"]) || isset($userData["is_sponsored"])){
        $userData["is_admin_candidate"] = 1;
    }

    //Is administrator
    if($userData["admin"] == 1){
        $userData["is_admin"] = 1;
    }

    StopTimer("RenderUser - Finish");
	StopTimer("RenderUser");
    return $userData;
}

function RenderUsers(&$users, &$games, &$jams, &$config){
	AddActionLog("RenderUsers");
	StartTimer("RenderUsers");
    $render = Array("LIST" => Array());

    $authorCount = 0;
    $gamesByUsername = GroupGamesByUsername($games);
    foreach($users as $i => $user){
        $username = $user["username"];
        $userGames = Array();
        if(isset($gamesByUsername[$username])){
            $userGames = $gamesByUsername[$username];
        }

        $userData = RenderUser($user, $users, $userGames, $jams, $config);

        if(!isset($userData["entry_count"])){
            $authorCount += 1;
        }

        $render["LIST"][] = $userData;
    }

	$render["all_authors_count"] = $authorCount;

	StopTimer("RenderUsers");
	return $render;
}

function GroupGamesByUsername($games)
{
	$gamesByUsername = [];
	foreach($games as $i => $game){
		$username = $game["author"];
		if (!isset($gamesByUsername[$username])) {
			$gamesByUsername[$username] = [];
		}
		$gamesByUsername[$username][] = $game;
	}
	return $gamesByUsername;
}

?>