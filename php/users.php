<?php

$userPreferenceSettings = Array(
	Array("PREFERENCE_KEY" => "DISABLE_THEMES_NOTIFICATION", "BIT_FLAG_EXPONENT" => 0)
);
                            
function RenderUser(&$config, &$cookies, &$user, &$users, &$games, &$jams, &$adminVotes, &$loggedInUserAdminVotes, $renderDepth){
	AddActionLog("RenderUser");
    StartTimer("RenderUser");
    
    $userData["id"] = $user->Id;
    $userData["username"] = $user->Username;
    $userData["display_name"] = $user->DisplayName;
    $userData["twitter"] = $user->Twitter;
    $userData["twitter_text_only"] = $user->TwitterTextOnly;
    $userData["email"] = $user->Email;
    $userData["salt"] = $user->Salt;
    $userData["password_hash"] = $user->PasswordHash;
    $userData["password_iterations"] = $user->PasswordIterations;
    $userData["admin"] = $user->Admin;
    $userData["user_preferences"] = $user->UserPreferences;
    $userData["preferences"] = $user->Preferences;
    $userData["days_since_last_login"] = $user->DaysSinceLastLogin;
    $userData["days_since_last_admin_action"] = $user->DaysSinceLastAdminAction;
    if($user->IsSponsored){
        $userData["is_sponsored"] = 1;
        $userData["sponsored_by"] = $user->SponsoredBy;
    }

    $currentJamData = GetCurrentJamNumberAndID();

    $username = $userData["username"];
    $userData["username_alphanumeric"] = preg_replace("/[^a-zA-Z0-9]+/", "", $username);
    $userData["recent_participation"] = 0;

    //Determine if this user is an author and their participation
	StartTimer("RenderUser - foreach games");
    $userData["entry_count"] = 0;
    $userData["first_jam_number"] = 0;
    $userData["last_jam_number"] = 0;
    foreach($games as $j => $gameData){
        if($gameData->Author != $username){
            continue;
        }

        if($gameData->Deleted == 1){
            continue;
        }

	    StartTimer("RenderUser - foreach games - Foreach Jams");
        foreach($jams as $k => $jam){
            if($jam->Id == $gameData->JamId){
                $jamData = $jam;
                break;
            }
        }
	    StopTimer("RenderUser - foreach games - Foreach Jams");

	    StartTimer("RenderUser - foreach games - entry count, first and last jam, recent");
        $userData["is_author"] = 1;
        
        if($userData["first_jam_number"] == 0){
            $userData["first_jam_number"] = $gameData->JamNumber;
        }
        
        if($userData["last_jam_number"] == 0){
            $userData["last_jam_number"] = $gameData->JamNumber;
        }

        $userData["entry_count"] += 1;

        if($gameData->JamNumber < $userData["first_jam_number"] ){
            $userData["first_jam_number"] = $gameData->JamNumber;
        }
        if($gameData->JamNumber > $userData["last_jam_number"] ){
            $userData["last_jam_number"] = $gameData->JamNumber;
        }

        $isJamRecent = intval($jamData->JamNumber) > (intval($currentJamData["NUMBER"]) - intval($config["JAMS_CONSIDERED_RECENT"]->Value));
        if($isJamRecent){
            $userData["recent_participation"] += 100.0 / $config["JAMS_CONSIDERED_RECENT"]->Value;
        }

	    StopTimer("RenderUser - foreach games - entry count, first and last jam, recent");

        StartTimer("RenderUser - foreach games - RenderGame");
        if(($renderDepth & RENDER_DEPTH_GAMES) > 0){
            $userData["entries"][] = RenderGame($users, $gameData, $jams, $renderDepth & ~RENDER_DEPTH_USERS);
        }
	    StopTimer("RenderUser - foreach games - RenderGame");

	    StartTimer("RenderUser - preferences");
        foreach($userData["preferences"] as $preferenceKey => $preferenceValue){
            if($preferenceValue != 0){
                $render[$preferenceKey] = 1;
            }
        }
	    StopTimer("RenderUser - preferences");
    }
	StopTimer("RenderUser - foreach games");

    //Find admin candidates
	StartTimer("RenderUser - admin candidates");
    if($userData["recent_participation"] >= $config["ADMIN_SUGGESTION_RECENT_PARTICIPATION"]->Value){
        $userData["admin_candidate_recent_participation_check_pass"] = 1;
    }
    if($userData["entry_count"] >= $config["ADMIN_SUGGESTION_TOTAL_PARTICIPATION"]->Value){
        $userData["admin_candidate_total_participation_check_pass"] = 1;
    }
    if(	isset($userData["admin_candidate_recent_participation_check_pass"]) &&
    isset($userData["admin_candidate_total_participation_check_pass"])){
            $userData["system_suggestsed_admin_candidate"] = 1;
    }
	StopTimer("RenderUser - admin candidates");

    StartTimer("RenderUser - inactive admins");

    $inactiveColor = "#FFECEC";
    $activeColor = "#F6FFEC";
    $highlyAciveColor = "#ECFFEC";

    if($cookies->DarkMode == 1)
    {
        $inactiveColor = "#4A3636";
        $activeColor = "#3E4A36";
        $highlyAciveColor = "#364A36";
    }

    //Find inactive admins (participation in jams)
    $jamsSinceLastParticipation = ($currentJamData["NUMBER"] - $userData["last_jam_number"]);
    $userData["jams_since_last_participation"] = $jamsSinceLastParticipation;
    if($userData["last_jam_number"] < ($currentJamData["NUMBER"] - $config["ADMIN_ACTIVITY_JAMS_SINCE_LAST_PARTICIPATION_WARNING"]->Value)){
        $userData["activity_jam_participation"] = "inactive";
        $userData["activity_jam_participation_color"] = $inactiveColor;
    }else if($userData["last_jam_number"] >= ($currentJamData["NUMBER"] - $config["ADMIN_ACTIVITY_JAMS_SINCE_LAST_PARTICIPATION_GOOD"]->Value)){
        $userData["activity_jam_participation"] = "highly active";
        $userData["activity_jam_participation_color"] = $highlyAciveColor;
    }else{
        $userData["activity_jam_participation"] = "active";
        $userData["activity_jam_participation_color"] = $activeColor;
    }

    //Find inactive admins (days since last login)
    if($userData["days_since_last_login"] > $config["ADMIN_ACTIVITY_DAYS_SINCE_LAST_LOGIN_WARNING"]->Value){
        $userData["activity_login"] = "inactive";
        $userData["activity_login_color"] = $inactiveColor;
    }else if($userData["days_since_last_login"] < $config["ADMIN_ACTIVITY_DAYS_SINCE_LAST_LOGIN_GOOD"]->Value){
        $userData["activity_login"] = "highly active";
        $userData["activity_login_color"] = $highlyAciveColor;
    }else{
        $userData["activity_login"] = "active";
        $userData["activity_login_color"] = $activeColor;
    }

    //Find inactive admins (days since last login)
    if($userData["days_since_last_admin_action"] > $config["ADMIN_ACTIVITY_DAYS_SINCE_LAST_ADMIN_ACTION_WARNING"]->Value){
        $userData["activity_administration"] = "inactive";
        $userData["activity_administration_color"] = $inactiveColor;
    }else if($userData["days_since_last_admin_action"] < $config["ADMIN_ACTIVITY_DAYS_SINCE_LAST_ADMIN_ACTION_GOOD"]->Value){
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
    
    StartTimer("RenderUser - Admin Votes");
    $userData["votes_for"] = 0;
    $userData["votes_neutral"] = 0;
    $userData["votes_against"] = 0;
    $userData["votes_vetos"] = 0;
    foreach($adminVotes as $j => $adminVoteData){
        if($userData["username"] == $adminVoteData->SubjectUsername){
            switch($adminVoteData->VoteType){
                case "FOR":
                    $userData["votes_for"] += 1;
                    break;
                case "NEUTRAL":
                    $userData["votes_neutral"] += 1;
                    break;
                case "AGAINST":
                    $userData["votes_against"] += 1;
                    break;
                case "SPONSOR":
                    $userData["votes_for"] += 1;
                    $userData["is_sponsored"] = 1;
                    break;
                case "VETO":
                    $userData["votes_vetos"] += 1;
                    $userData["is_vetoed"] = 1;
                    break;
            }
        }
    }
    StopTimer("RenderUser - Admin Votes");
    
    StartTimer("RenderUser - Logged in users admin votes");
    foreach($loggedInUserAdminVotes as $j => $adminVoteData){
        if($userData["username"] == $adminVoteData["subject_username"]){
            $userData["vote_type"] = $adminVoteData["vote_type"];

            switch($adminVoteData["vote_type"]){
                case "FOR":
                    $userData["vote_type_for"] = 1;
                    break;
                case "NEUTRAL":
                    $userData["vote_type_neutral"] = 1;
                    break;
                case "AGAINST":
                    $userData["vote_type_against"] = 1;
                    break;
                case "SPONSOR":
                    $userData["vote_type_sponsor"] = 1;
                    break;
                case "VETO":
                    $userData["vote_type_veto"] = 1;
                    break;
            }
        }
    }
    StopTimer("RenderUser - Logged in users admin votes");

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

function RenderUsers(&$config, &$cookies, &$users, &$games, &$jams, &$adminVotes, &$loggedInUserAdminVotes, $renderDepth){
	AddActionLog("RenderUsers");
    StartTimer("RenderUsers");
    
    $render = Array("LIST" => Array());

    $authorCount = 0;
    $gamesByUsername = GroupGamesByUsername($games);
    $jamsByUsername = GroupJamsByUsername($jams, $gamesByUsername);

    foreach($users as $i => $user){
        $username = $user->Username;
        $userGames = Array();
        if(isset($gamesByUsername[$username])){
            $userGames = $gamesByUsername[$username];
        }
        $userJams = Array();
        if(isset($jamsByUsername[$username])){
            $userJams = $jamsByUsername[$username];
        }
        $userAsArray = Array($user);
        
		if(($renderDepth & RENDER_DEPTH_USERS) > 0){
            $userData = RenderUser($config, $cookies, $user, $users, $userGames, $userJams, $adminVotes, $loggedInUserAdminVotes, $renderDepth);
            $render["LIST"][] = $userData;
        }

        if(count($userGames) > 0){
            $authorCount += 1;
        }
    }
    
    
    $missingAdminCandidateVotes = 0;
	foreach($render["LIST"] as $i => $userRender){
		if(!isset($userRender["is_admin_candidate"])){
            continue;
		}
		if(isset($userRender["is_admin"])){
            continue;
		}
		if(!isset($userRender["vote_type"])){
            $missingAdminCandidateVotes += 1;
        }
    }

    if($missingAdminCandidateVotes > 0){
        $render["missing_admin_candidate_votes"] = 1;
        $render["missing_admin_candidate_votes_number"] = $missingAdminCandidateVotes;
    }
  
    $render["all_authors_count"] = $authorCount;

	StopTimer("RenderUsers");
	return $render;
}

function RenderLoggedInUser(&$config, &$cookies, &$users, &$games, &$jams, &$adminVotes, &$loggedInUserAdminVotes, &$loggedInUser, $renderDepth){
    AddActionLog("RenderLoggedInUser");
    
    return RenderUser($config, $cookies, $loggedInUser, $users, $games, $jams, $adminVotes, $loggedInUserAdminVotes, $renderDepth);
}

function GroupGamesByUsername(&$games)
{
	AddActionLog("GroupGamesByUsername");
    StartTimer("GroupGamesByUsername");
    
    $gamesByUsername = Array();
    foreach($games as $i => $game) {
        $username = $game->Author;
        if (!isset($gamesByUsername[$username])){
            $gamesByUsername[$username] = Array();
        }
        $gamesByUsername[$username][] = $game;
    }

	StopTimer("GroupGamesByUsername");
    return $gamesByUsername;
}

function GroupJamsByUsername(&$jams, &$gamesByUsername)
{
	AddActionLog("GroupJamsByUsername");
    StartTimer("GroupJamsByUsername");

    $jamsByUsername = Array();
    foreach($gamesByUsername as $username => $games){
        $jamsByUsername[$username] = Array();
        foreach($games as $i => $game){
            $jamsByUsername[$username][$game->JamId] = $jams[$game->JamId];
        }
    }

	StopTimer("GroupJamsByUsername");
    return $jamsByUsername;
}

?>