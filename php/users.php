<?php
                            
function RenderUser(&$configData, &$cookieData, &$userModel, &$userData, &$gameData, &$jamData, &$adminVoteData, $renderDepth){
	AddActionLog("RenderUser");
    StartTimer("RenderUser");
    
    $render["id"] = $userModel->Id;
    $render["username"] = $userModel->Username;
    $render["display_name"] = $userModel->DisplayName;
    $render["twitter"] = $userModel->Twitter;
    $render["twitter_text_only"] = $userModel->TwitterTextOnly;
    $render["email"] = $userModel->Email;
    $render["salt"] = $userModel->Salt;
    $render["password_hash"] = $userModel->PasswordHash;
    $render["password_iterations"] = $userModel->PasswordIterations;
    $render["admin"] = $userModel->Admin;
    $render["user_preferences"] = $userModel->UserPreferences;
    $render["preferences"] = $userModel->Preferences;
    $render["days_since_last_login"] = $userModel->DaysSinceLastLogin;
    $render["days_since_last_admin_action"] = $userModel->DaysSinceLastAdminAction;
    if($userModel->IsSponsored){
        $render["is_sponsored"] = 1;
        $render["sponsored_by"] = $userModel->SponsoredBy;
    }

    $currentJam = GetCurrentJamNumberAndID();

    $username = $render["username"];
    $usernameId = $render["id"];
    $render["username_alphanumeric"] = preg_replace("/[^a-zA-Z0-9]+/", "", $username);
    $render["recent_participation"] = 0;

    //Determine if this user is an author and their participation
	StartTimer("RenderUser - foreach games");
    $render["entry_count"] = 0;
    $render["first_jam_number"] = 0;
    $render["last_jam_number"] = 0;
    foreach($gameData->GetGamesMadeByUserId($usernameId) as $j => $gameModel){
        if($gameModel->AuthorUserId != $usernameId){
            continue;
        }

        if($gameModel->Deleted == 1){
            continue;
        }

	    StartTimer("RenderUser - foreach games - Foreach Jams");
        $jamModelForGame = $jamData->JamModels[$gameModel->JamId];
	    StopTimer("RenderUser - foreach games - Foreach Jams");

	    StartTimer("RenderUser - foreach games - entry count, first and last jam, recent");
        $render["is_author"] = 1;
        
        if($render["first_jam_number"] == 0){
            $render["first_jam_number"] = $gameModel->JamNumber;
        }
        
        if($render["last_jam_number"] == 0){
            $render["last_jam_number"] = $gameModel->JamNumber;
        }

        $render["entry_count"] += 1;

        if($gameModel->JamNumber < $render["first_jam_number"] ){
            $render["first_jam_number"] = $gameModel->JamNumber;
        }
        if($gameModel->JamNumber > $render["last_jam_number"] ){
            $render["last_jam_number"] = $gameModel->JamNumber;
        }

        $isJamRecent = intval($jamModelForGame->JamNumber) > (intval($currentJam["NUMBER"]) - intval($configData->ConfigModels["JAMS_CONSIDERED_RECENT"]->Value));
        if($isJamRecent){
            $render["recent_participation"] += 100.0 / $configData->ConfigModels["JAMS_CONSIDERED_RECENT"]->Value;
        }

	    StopTimer("RenderUser - foreach games - entry count, first and last jam, recent");

        StartTimer("RenderUser - foreach games - RenderGame");
        if(($renderDepth & RENDER_DEPTH_GAMES) > 0){
            $render["entries"][] = RenderGame($userData, $gameModel, $jamData, $renderDepth & ~RENDER_DEPTH_USERS);
        }
	    StopTimer("RenderUser - foreach games - RenderGame");

	    StartTimer("RenderUser - preferences");
        foreach($render["preferences"] as $preferenceKey => $preferenceValue){
            if($preferenceValue != 0){
                $render[$preferenceKey] = 1;
            }
        }
	    StopTimer("RenderUser - preferences");
    }
	StopTimer("RenderUser - foreach games");

    //Find admin candidates
	StartTimer("RenderUser - admin candidates");
    if($render["recent_participation"] >= $configData->ConfigModels["ADMIN_SUGGESTION_RECENT_PARTICIPATION"]->Value){
        $render["admin_candidate_recent_participation_check_pass"] = 1;
    }
    if($render["entry_count"] >= $configData->ConfigModels["ADMIN_SUGGESTION_TOTAL_PARTICIPATION"]->Value){
        $render["admin_candidate_total_participation_check_pass"] = 1;
    }
    if(	isset($render["admin_candidate_recent_participation_check_pass"]) &&
    isset($render["admin_candidate_total_participation_check_pass"])){
            $render["system_suggestsed_admin_candidate"] = 1;
    }
	StopTimer("RenderUser - admin candidates");

    StartTimer("RenderUser - inactive admins");

    $inactiveColor = "#FFECEC";
    $activeColor = "#F6FFEC";
    $highlyAciveColor = "#ECFFEC";

    if($cookieData->CookieModel->DarkMode == 1)
    {
        $inactiveColor = "#4A3636";
        $activeColor = "#3E4A36";
        $highlyAciveColor = "#364A36";
    }

    //Find inactive admins (participation in jams)
    $jamsSinceLastParticipation = ($currentJam["NUMBER"] - $render["last_jam_number"]);
    $render["jams_since_last_participation"] = $jamsSinceLastParticipation;
    if($render["last_jam_number"] < ($currentJam["NUMBER"] - $configData->ConfigModels["ADMIN_ACTIVITY_JAMS_SINCE_LAST_PARTICIPATION_WARNING"]->Value)){
        $render["activity_jam_participation"] = "inactive";
        $render["activity_jam_participation_color"] = $inactiveColor;
    }else if($render["last_jam_number"] >= ($currentJam["NUMBER"] - $configData->ConfigModels["ADMIN_ACTIVITY_JAMS_SINCE_LAST_PARTICIPATION_GOOD"]->Value)){
        $render["activity_jam_participation"] = "highly active";
        $render["activity_jam_participation_color"] = $highlyAciveColor;
    }else{
        $render["activity_jam_participation"] = "active";
        $render["activity_jam_participation_color"] = $activeColor;
    }

    //Find inactive admins (days since last login)
    if($render["days_since_last_login"] > $configData->ConfigModels["ADMIN_ACTIVITY_DAYS_SINCE_LAST_LOGIN_WARNING"]->Value){
        $render["activity_login"] = "inactive";
        $render["activity_login_color"] = $inactiveColor;
    }else if($render["days_since_last_login"] < $configData->ConfigModels["ADMIN_ACTIVITY_DAYS_SINCE_LAST_LOGIN_GOOD"]->Value){
        $render["activity_login"] = "highly active";
        $render["activity_login_color"] = $highlyAciveColor;
    }else{
        $render["activity_login"] = "active";
        $render["activity_login_color"] = $activeColor;
    }

    //Find inactive admins (days since last login)
    if($render["days_since_last_admin_action"] > $configData->ConfigModels["ADMIN_ACTIVITY_DAYS_SINCE_LAST_ADMIN_ACTION_WARNING"]->Value){
        $render["activity_administration"] = "inactive";
        $render["activity_administration_color"] = $inactiveColor;
    }else if($render["days_since_last_admin_action"] < $configData->ConfigModels["ADMIN_ACTIVITY_DAYS_SINCE_LAST_ADMIN_ACTION_GOOD"]->Value){
        $render["activity_administration"] = "highly active";
        $render["activity_administration_color"] = $highlyAciveColor;
    }else{
        $render["activity_administration"] = "active";
        $render["activity_administration_color"] = $activeColor;
    }

    //Render activity related statuses (inactive, active, highly active)
    switch($render["activity_jam_participation"]){
        case "inactive":
            $render["activity_jam_participation_inactive"] = 1;
            break;
        case "active":
            $render["activity_jam_participation_active"] = 1;
            break;
        case "highly active":
            $render["activity_jam_participation_highly_active"] = 1;
            break;
    }
    switch($render["activity_login"]){
        case "inactive":
            $render["activity_login_inactive"] = 1;
            break;
        case "active":
            $render["activity_login_active"] = 1;
            break;
        case "highly active":
            $render["activity_login_highly_active"] = 1;
            break;
    }
    switch($render["activity_administration"]){
        case "inactive":
            $render["activity_administration_inactive"] = 1;
            break;
        case "active":
            $render["activity_administration_active"] = 1;
            break;
        case "highly active":
            $render["activity_administration_highly_active"] = 1;
            break;
    }
    StopTimer("RenderUser - inactive admins");
    
    StartTimer("RenderUser - Admin Votes");
    $render["votes_for"] = 0;
    $render["votes_neutral"] = 0;
    $render["votes_against"] = 0;
    $render["votes_vetos"] = 0;
    foreach($adminVoteData->AdminVoteModels as $j => $adminVoteModel){
        if($render["id"] == $adminVoteModel->SubjectUserId){
            switch($adminVoteModel->VoteType){
                case "FOR":
                    $render["votes_for"] += 1;
                    break;
                case "NEUTRAL":
                    $render["votes_neutral"] += 1;
                    break;
                case "AGAINST":
                    $render["votes_against"] += 1;
                    break;
                case "SPONSOR":
                    $render["votes_for"] += 1;
                    $render["is_sponsored"] = 1;
                    break;
                case "VETO":
                    $render["votes_vetos"] += 1;
                    $render["is_vetoed"] = 1;
                    break;
            }
        }
    }
    StopTimer("RenderUser - Admin Votes");
    
    StartTimer("RenderUser - Logged in users admin votes");
    foreach($adminVoteData->LoggedInUserAdminVotes as $j => $adminVoteModel){
        if($render["id"] == $adminVoteModel["subject_user_id"]){
            $render["vote_type"] = $adminVoteModel["vote_type"];

            switch($adminVoteModel["vote_type"]){
                case "FOR":
                    $render["vote_type_for"] = 1;
                    break;
                case "NEUTRAL":
                    $render["vote_type_neutral"] = 1;
                    break;
                case "AGAINST":
                    $render["vote_type_against"] = 1;
                    break;
                case "SPONSOR":
                    $render["vote_type_sponsor"] = 1;
                    break;
                case "VETO":
                    $render["vote_type_veto"] = 1;
                    break;
            }
        }
    }
    StopTimer("RenderUser - Logged in users admin votes");

    StartTimer("RenderUser - Finish");
    //Mark system suggested and admin-sponsored users as admin candidates
    if(isset($render["system_suggestsed_admin_candidate"]) || isset($render["is_sponsored"])){
        $render["is_admin_candidate"] = 1;
    }

    //Is administrator
    if($render["admin"] == 1){
        $render["is_admin"] = 1;
    }

    StopTimer("RenderUser - Finish");
	StopTimer("RenderUser");
    return $render;
}

function RenderUsers(&$configData, &$cookieData, &$userData, &$gameData, &$jamData, &$adminVoteData, $renderDepth){
	AddActionLog("RenderUsers");
    StartTimer("RenderUsers");
    
    $render = Array("LIST" => Array());

    $authorCount = 0;

    foreach($userData->UserModels as $i => $userModel){
        $username = $userModel->Username;
        
		if(($renderDepth & RENDER_DEPTH_USERS) > 0){
            $userRender = RenderUser($configData, $cookieData, $userModel, $userData, $gameData, $jamData, $adminVoteData, $renderDepth);
            $render["LIST"][] = $userRender;
        }

        if(isset($gameData->GamesByUsername[$username]) && count($gameData->GamesByUsername[$username]) > 0){
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

function RenderLoggedInUser(&$configData, &$cookieData, &$userData, &$gameData, &$jamData, &$adminVoteData, &$loggedInUser, $renderDepth){
    AddActionLog("RenderLoggedInUser");
    
    return RenderUser($configData, $cookieData, $loggedInUser, $userData, $gameData, $jamData, $adminVoteData, $renderDepth);
}

?>