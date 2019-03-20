<?php

BeforeInit();	//Plugin hook
Init();
AfterInit();	//Plugin hook

//Initializes the site.
function Init(){
	global $dictionary, $config, $adminLog, $users, $jams, $games, $assets, $loggedInUser, $satisfaction, $adminVotes, $nextSuggestedJamDateTime, $nextJamTime, $themes, $themesByVoteDifference, $themesByPopularity, $polls, $cookies, $actions, $page, $dep;
	AddActionLog("Init");
	StartTimer("Init");

	
	StartTimer("Init - Load Data");

	UpdateCookies();
	$cookies = new CookieData();

	$config = new ConfigData();
	$nextSuggestedJamDateTime = GetSuggestedNextJamDateTime($config->ConfigModels);

    RedirectToHttpsIfRequired($config->ConfigModels);

	$adminLog = new AdminLogData();
	$users = new UserData();

	$loggedInUser = IsLoggedIn($config->ConfigModels, $users->UserModels);
	
	$page = ValidatePage($page, $loggedInUser);

	$jams = new JamData();
	$games = new GameData();

	$themes = new ThemeData($loggedInUser);
	$themesByVoteDifference = CalculateThemeSelectionProbabilityByVoteDifference($themes->ThemeModels, $config->ConfigModels);
	$themesByPopularity = CalculateThemeSelectionProbabilityByPopularity($themes->ThemeModels, $config->ConfigModels);

	$nextScheduledJamTime = GetNextJamDateAndTime($jams->JamModels);
	$nextSuggestedJamTime = GetSuggestedNextJamDateTime($config->ConfigModels);
	CheckNextJamSchedule($config->ConfigModels, $jams->JamModels, $themes->ThemeModels, $nextScheduledJamTime, $nextSuggestedJamTime);

	$actions = LoadSiteActions($config->ConfigModels);
	$assets = new AssetData();
	$polls = new PollData($loggedInUser);
    $satisfaction = new SatisfactionData($config->ConfigModels);
    $adminVotes = new AdminVoteData($loggedInUser);
	$messages = new MessageData($actions);
	
	StopTimer("Init - Load Data");
	StartTimer("Init - Render");

	PerformPendingSiteAction($config->ConfigModels, $actions, $loggedInUser);
 
	if(FindDependency("RenderConfig", $dep) !== false){
		$dictionary["CONFIG"] = RenderConfig($config->ConfigModels);
	}
	if(FindDependency("RenderAdminLog", $dep) !== false){
		$dictionary["adminlog"] = RenderAdminLog($adminLog->AdminLogModels);
	}
	if(FindDependency("RenderUsers", $dep) !== false){
		$dependency = FindDependency("RenderUsers", $dep);
		$dictionary["users"] = RenderUsers($config->ConfigModels, $cookies->CookieModel, $users->UserModels, $games->GameModels, $jams->JamModels, $adminVotes->AdminVoteModels, $adminVotes->LoggedInUserAdminVotes, $dependency["RenderDepth"]);
	}
	if(FindDependency("RenderAllJams", $dep) !== false){
		$dependency1 = FindDependency("RenderAllJams", $dep);
		$dependency2 = FindDependency("RenderJams", $dep);
		$renderDepth = $dependency1["RenderDepth"] | $dependency2["RenderDepth"];
		$dictionary["jams"] = RenderJams($config->ConfigModels, $users->UserModels, $games->GameModels, $jams->JamModels, $satisfaction->SatisfactionModels, $loggedInUser, $renderDepth, true);
	}else if(FindDependency("RenderJams", $dep) !== false){
		$dependency1 = FindDependency("RenderAllJams", $dep);
		$dependency2 = FindDependency("RenderJams", $dep);
		$renderDepth = $dependency1["RenderDepth"] | $dependency2["RenderDepth"];
		$loadAll = false;
		if(isset($_GET["loadAll"])){
			$loadAll = true;
		}
		$dictionary["jams"] = RenderJams($config->ConfigModels, $users->UserModels, $games->GameModels, $jams->JamModels, $satisfaction->SatisfactionModels, $loggedInUser, $renderDepth, $loadAll);
	}
	if(FindDependency("RenderGames", $dep) !== false){
		$dependency = FindDependency("RenderGames", $dep);
		$dictionary["entries"] = RenderGames($users->UserModels, $games->GameModels, $jams->JamModels, $dependency["RenderDepth"]);
	}
	if(FindDependency("RenderThemes", $dep) !== false){
		$dictionary["themes"] = RenderThemes($config->ConfigModels, $jams->JamModels, $themes->ThemeModels, $themes->LoggedInUserThemeVotes, $themesByVoteDifference, $themesByPopularity, $loggedInUser);
	}
	if(FindDependency("RenderAssets", $dep) !== false){
		$dictionary["assets"] = RenderAssets($assets->AssetModels);
	}
	if(FindDependency("RenderPolls", $dep) !== false){
		$dictionary["polls"] = RenderPolls($polls->PollModels, $polls->LoggedInUserPollVotes);
	}
	if(FindDependency("RenderCookies", $dep) !== false){
		$dictionary["cookies"] = RenderCookies($cookies->CookieModel);
	}
	if(FindDependency("RenderMessages", $dep) !== false){
		$dictionary["messages"] = RenderMessages($messages->MessageModels);
	}
	if(FindDependency("RenderStream", $dep) !== false){
		$now = Time();
		$jamTime = strtotime($dictionary["jams"]["current_jam"]["start_time"] . " UTC");
		$dictionary["stream"] = Array();

		if($jamTime + 3600 <= $now && $now <= $jamTime + 7 * 3600)
			$dictionary["stream"] = InitStream($config->ConfigModels);
	}
	
	$dictionary["page"] = RenderPageSpecific($page, $config->ConfigModels, $users->UserModels, $games->GameModels, $jams->JamModels, $satisfaction->SatisfactionModels, $loggedInUser, $assets->AssetModels, $cookies->CookieModel, $adminVotes->AdminVoteModels, $adminVotes->LoggedInUserAdminVotes, $nextSuggestedJamDateTime);
	
	if($loggedInUser !== false){
		if(FindDependency("RenderLoggedInUser", $dep) !== false){
			$dependency = FindDependency("RenderLoggedInUser", $dep);
			$dictionary["user"] = RenderLoggedInUser($config->ConfigModels, $cookies->CookieModel, $users->UserModels, $games->GameModels, $jams->JamModels, $adminVotes->AdminVoteModels, $adminVotes->LoggedInUserAdminVotes, $loggedInUser, $dependency["RenderDepth"]);
		}
	}
	
	StopTimer("Init - Render");
	StopTimer("Init");
}

?>