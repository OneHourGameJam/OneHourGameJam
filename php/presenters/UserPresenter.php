<?php

class UserPresenter{
	public static function RenderUser(&$configData, &$cookieData, &$userModel, &$userData, &$gameData, &$jamData, &$platformData, &$platformGameData, &$adminVoteData, $renderDepth){
		AddActionLog("RenderUser");
		StartTimer("RenderUser");
	
		$userViewModel = new UserViewModel();
		
		$userViewModel->id = $userModel->Id;
		$userViewModel->username = $userModel->Username;
		$userViewModel->display_name = $userModel->DisplayName;
		$userViewModel->twitter = $userModel->Twitter;
		$userViewModel->twitter_text_only = $userModel->TwitterTextOnly;
		$userViewModel->email = $userModel->Email;
		$userViewModel->salt = $userModel->Salt;
		$userViewModel->password_hash = $userModel->PasswordHash;
		$userViewModel->password_iterations = $userModel->PasswordIterations;
		$userViewModel->admin = $userModel->Admin;
		$userViewModel->user_preferences = $userModel->UserPreferences;
		$userViewModel->preferences = $userModel->Preferences;
		$userViewModel->days_since_last_login = $userModel->DaysSinceLastLogin;
		$userViewModel->days_since_last_admin_action = $userModel->DaysSinceLastAdminAction;
		if($userModel->IsSponsored){
			$userViewModel->is_sponsored = 1;
			$userViewModel->sponsored_by = $userModel->SponsoredBy;
		}
	
		$currentJam = GetCurrentJamNumberAndID();
	
		$userId = $userModel->Id;
		$userViewModel->username_alphanumeric = preg_replace("/[^a-zA-Z0-9]+/", "", $userModel->Username);
		$userViewModel->recent_participation = 0;
	
		//Determine if this user is an author and their participation
		StartTimer("RenderUser - foreach games");
		$userViewModel->entry_count = 0;
		$userViewModel->first_jam_number = 0;
		$userViewModel->last_jam_number = 0;
		foreach($gameData->GetGamesMadeByUserId($userId) as $j => $gameModel){
			if($gameModel->AuthorUserId != $userId){
				continue;
			}
	
			if($gameModel->Deleted == 1){
				continue;
			}
	
			StartTimer("RenderUser - foreach games - Foreach Jams");
			$jamModelForGame = $jamData->JamModels[$gameModel->JamId];
			StopTimer("RenderUser - foreach games - Foreach Jams");
	
			StartTimer("RenderUser - foreach games - entry count, first and last jam, recent");
			$userViewModel->is_author = 1;
			
			if($userViewModel->first_jam_number == 0){
				$userViewModel->first_jam_number = $gameModel->JamNumber;
			}
			
			if($userViewModel->last_jam_number == 0){
				$userViewModel->last_jam_number = $gameModel->JamNumber;
			}
	
			$userViewModel->entry_count += 1;
	
			if($gameModel->JamNumber < $userViewModel->first_jam_number ){
				$userViewModel->first_jam_number = $gameModel->JamNumber;
			}
			if($gameModel->JamNumber > $userViewModel->last_jam_number ){
				$userViewModel->last_jam_number = $gameModel->JamNumber;
			}
	
			$isJamRecent = intval($jamModelForGame->JamNumber) > (intval($currentJam["NUMBER"]) - intval($configData->ConfigModels["JAMS_CONSIDERED_RECENT"]->Value));
			if($isJamRecent){
				$userViewModel->recent_participation += 100.0 / $configData->ConfigModels["JAMS_CONSIDERED_RECENT"]->Value;
			}
	
			StopTimer("RenderUser - foreach games - entry count, first and last jam, recent");
	
			StartTimer("RenderUser - foreach games - RenderGame");
			if(($renderDepth & RENDER_DEPTH_GAMES) > 0){
				$userViewModel->entries[] = GamePresenter::RenderGame($userData, $gameModel, $jamData, $platformData, $platformGameData, $renderDepth & ~RENDER_DEPTH_USERS);
			}
			StopTimer("RenderUser - foreach games - RenderGame");
	
			StartTimer("RenderUser - preferences");
			foreach($userViewModel->preferences as $preferenceKey => $preferenceValue){
				if($preferenceValue != 0){
					$userViewModel->preferences_list[$preferenceKey] = 1;
				}
			}
			StopTimer("RenderUser - preferences");
		}
		StopTimer("RenderUser - foreach games");
	
		//Find admin candidates
		StartTimer("RenderUser - admin candidates");
		if($userViewModel->recent_participation >= $configData->ConfigModels["ADMIN_SUGGESTION_RECENT_PARTICIPATION"]->Value){
			$userViewModel->admin_candidate_recent_participation_check_pass = 1;
		}
		if($userViewModel->entry_count >= $configData->ConfigModels["ADMIN_SUGGESTION_TOTAL_PARTICIPATION"]->Value){
			$userViewModel->admin_candidate_total_participation_check_pass = 1;
		}
		if(	isset($userViewModel->admin_candidate_recent_participation_check_pass) &&
		isset($userViewModel->admin_candidate_total_participation_check_pass)){
				$userViewModel->system_suggestsed_admin_candidate = 1;
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
		$jamsSinceLastParticipation = ($currentJam["NUMBER"] - $userViewModel->last_jam_number);
		$userViewModel->jams_since_last_participation = $jamsSinceLastParticipation;
		if($userViewModel->last_jam_number < ($currentJam["NUMBER"] - $configData->ConfigModels["ADMIN_ACTIVITY_JAMS_SINCE_LAST_PARTICIPATION_WARNING"]->Value)){
			$userViewModel->activity_jam_participation = "inactive";
			$userViewModel->activity_jam_participation_color = $inactiveColor;
		}else if($userViewModel->last_jam_number >= ($currentJam["NUMBER"] - $configData->ConfigModels["ADMIN_ACTIVITY_JAMS_SINCE_LAST_PARTICIPATION_GOOD"]->Value)){
			$userViewModel->activity_jam_participation = "highly active";
			$userViewModel->activity_jam_participation_color = $highlyAciveColor;
		}else{
			$userViewModel->activity_jam_participation = "active";
			$userViewModel->activity_jam_participation_color = $activeColor;
		}
	
		//Find inactive admins (days since last login)
		if($userViewModel->days_since_last_login > $configData->ConfigModels["ADMIN_ACTIVITY_DAYS_SINCE_LAST_LOGIN_WARNING"]->Value){
			$userViewModel->activity_login = "inactive";
			$userViewModel->activity_login_color = $inactiveColor;
		}else if($userViewModel->days_since_last_login < $configData->ConfigModels["ADMIN_ACTIVITY_DAYS_SINCE_LAST_LOGIN_GOOD"]->Value){
			$userViewModel->activity_login = "highly active";
			$userViewModel->activity_login_color = $highlyAciveColor;
		}else{
			$userViewModel->activity_login = "active";
			$userViewModel->activity_login_color = $activeColor;
		}
	
		//Find inactive admins (days since last login)
		if($userViewModel->days_since_last_admin_action > $configData->ConfigModels["ADMIN_ACTIVITY_DAYS_SINCE_LAST_ADMIN_ACTION_WARNING"]->Value){
			$userViewModel->activity_administration = "inactive";
			$userViewModel->activity_administration_color = $inactiveColor;
		}else if($userViewModel->days_since_last_admin_action < $configData->ConfigModels["ADMIN_ACTIVITY_DAYS_SINCE_LAST_ADMIN_ACTION_GOOD"]->Value){
			$userViewModel->activity_administration = "highly active";
			$userViewModel->activity_administration_color = $highlyAciveColor;
		}else{
			$userViewModel->activity_administration = "active";
			$userViewModel->activity_administration_color = $activeColor;
		}
	
		//Render activity related statuses (inactive, active, highly active)
		switch($userViewModel->activity_jam_participation){
			case "inactive":
				$userViewModel->activity_jam_participation_inactive = 1;
				break;
			case "active":
				$userViewModel->activity_jam_participation_active = 1;
				break;
			case "highly active":
				$userViewModel->activity_jam_participation_highly_active = 1;
				break;
		}
		switch($userViewModel->activity_login){
			case "inactive":
				$userViewModel->activity_login_inactive = 1;
				break;
			case "active":
				$userViewModel->activity_login_active = 1;
				break;
			case "highly active":
				$userViewModel->activity_login_highly_active = 1;
				break;
		}
		switch($userViewModel->activity_administration){
			case "inactive":
				$userViewModel->activity_administration_inactive = 1;
				break;
			case "active":
				$userViewModel->activity_administration_active = 1;
				break;
			case "highly active":
				$userViewModel->activity_administration_highly_active = 1;
				break;
		}
		StopTimer("RenderUser - inactive admins");
		
		StartTimer("RenderUser - Admin Votes");
		$userViewModel->votes_for = 0;
		$userViewModel->votes_neutral = 0;
		$userViewModel->votes_against = 0;
		$userViewModel->votes_vetos = 0;
		foreach($adminVoteData->AdminVoteModels as $j => $adminVoteModel){
			if($userViewModel->id == $adminVoteModel->SubjectUserId){
				switch($adminVoteModel->VoteType){
					case "FOR":
						$userViewModel->votes_for += 1;
						break;
					case "NEUTRAL":
						$userViewModel->votes_neutral += 1;
						break;
					case "AGAINST":
						$userViewModel->votes_against += 1;
						break;
					case "SPONSOR":
						$userViewModel->votes_for += 1;
						$userViewModel->is_sponsored = 1;
						break;
					case "VETO":
						$userViewModel->votes_vetos += 1;
						$userViewModel->is_vetoed = 1;
						break;
				}
			}
		}
		StopTimer("RenderUser - Admin Votes");
		
		StartTimer("RenderUser - Logged in users admin votes");
		foreach($adminVoteData->LoggedInUserAdminVotes as $j => $adminVoteModel){
			if($userViewModel->id == $adminVoteModel->SubjectUserId){
				$userViewModel->vote_type = $adminVoteModel->VoteType;
	
				switch($adminVoteModel->VoteType){
					case "FOR":
						$userViewModel->vote_type_for = 1;
						break;
					case "NEUTRAL":
						$userViewModel->vote_type_neutral = 1;
						break;
					case "AGAINST":
						$userViewModel->vote_type_against = 1;
						break;
					case "SPONSOR":
						$userViewModel->vote_type_sponsor = 1;
						break;
					case "VETO":
						$userViewModel->vote_type_veto = 1;
						break;
				}
			}
		}
		StopTimer("RenderUser - Logged in users admin votes");
	
		StartTimer("RenderUser - Finish");
		//Mark system suggested and admin-sponsored users as admin candidates
		if(isset($userViewModel->system_suggestsed_admin_candidate) || isset($userViewModel->is_sponsored)){
			$userViewModel->is_admin_candidate = 1;
		}
	
		//Is administrator
		if($userViewModel->admin == 1){
			$userViewModel->is_admin = 1;
		}
	
		StopTimer("RenderUser - Finish");
		StopTimer("RenderUser");
		return $userViewModel;
	}
	
	public static function RenderUsers(&$configData, &$cookieData, &$userData, &$gameData, &$jamData, &$platformData, &$platformGameData, &$adminVoteData, $renderDepth){
		AddActionLog("RenderUsers");
		StartTimer("RenderUsers");
		
		$usersViewModel = new UsersViewModel();
	
		$authorCount = 0;
	
		foreach($userData->UserModels as $i => $userModel){
			if(($renderDepth & RENDER_DEPTH_USERS) > 0){
				$userRender = UserPresenter::RenderUser($configData, $cookieData, $userModel, $userData, $gameData, $jamData, $platformData, $platformGameData, $adminVoteData, $renderDepth);
				$usersViewModel->LIST[] = $userRender;
			}
	
			if(isset($gameData->GamesByUserId[$userModel->Id]) && count($gameData->GamesByUserId[$userModel->Id]) > 0){
				$authorCount += 1;
			}
		}
		
		
		$missingAdminCandidateVotes = 0;
		foreach($usersViewModel->LIST as $i => $userRender){
			if(!isset($userRender->is_admin_candidate)){
				continue;
			}
			if(isset($userRender->is_admin)){
				continue;
			}
			if(!isset($userRender->vote_type)){
				$missingAdminCandidateVotes += 1;
			}
		}
	
		if($missingAdminCandidateVotes > 0){
			$usersViewModel->missing_admin_candidate_votes = 1;
			$usersViewModel->missing_admin_candidate_votes_number = $missingAdminCandidateVotes;
		}
	  
		$usersViewModel->all_authors_count = $authorCount;
	
		StopTimer("RenderUsers");
		return $usersViewModel;
	}
	
	public static function RenderLoggedInUser(&$configData, &$cookieData, &$userData, &$gameData, &$jamData, &$platformData, &$platformGameData, &$adminVoteData, &$loggedInUser, $renderDepth){
		AddActionLog("RenderLoggedInUser");
		
		return UserPresenter::RenderUser($configData, $cookieData, $loggedInUser, $userData, $gameData, $jamData, $platformData, $platformGameData, $adminVoteData, $renderDepth);
	}
}

?>