<?php

BeforeInit();	//Plugin hook
Init();
AfterInit();	//Plugin hook

//Initializes the site.
function Init(){
	global $dictionary, $configData, $adminLogData, $users, $jams, $gameData, $assetData, $loggedInUser, $satisfaction, $adminVotes, $nextSuggestedJamDateTime, $nextJamTime, $themes, $themesByVoteDifference, $themesByPopularity, $polls, $cookieData, $actions, $page, $dep;
	AddActionLog("Init");
	StartTimer("Init");

	
	StartTimer("Init - Load Data");

	UpdateCookies();
	$cookieData = new CookieData();

	$configData = new ConfigData();
	$nextSuggestedJamDateTime = GetSuggestedNextJamDateTime($configData);

    RedirectToHttpsIfRequired($configData);

	$adminLogData = new AdminLogData();
	$users = new UserData();

	$loggedInUser = IsLoggedIn($configData, $users->UserModels);
	
	$page = ValidatePage($page, $loggedInUser);

	$jams = new JamData();
	$gameData = new GameData();

	$themes = new ThemeData($loggedInUser);
	$themesByVoteDifference = CalculateThemeSelectionProbabilityByVoteDifference($themes->ThemeModels, $configData);
	$themesByPopularity = CalculateThemeSelectionProbabilityByPopularity($themes->ThemeModels, $configData);

	$nextScheduledJamTime = GetNextJamDateAndTime($jams->JamModels);
	$nextSuggestedJamTime = GetSuggestedNextJamDateTime($configData);
	CheckNextJamSchedule($configData, $jams->JamModels, $themes->ThemeModels, $nextScheduledJamTime, $nextSuggestedJamTime);

	$actions = new SiteActionData($configData);
	$assetData = new AssetData();
	$polls = new PollData($loggedInUser);
    $satisfaction = new SatisfactionData($configData);
    $adminVoteData = new AdminVoteData($loggedInUser);
	$messages = new MessageData($actions->SiteActionModels);
	
	StopTimer("Init - Load Data");
	StartTimer("Init - Render");

	PerformPendingSiteAction($configData, $actions->SiteActionModels, $loggedInUser);
 
	if(FindDependency("RenderConfig", $dep) !== false){
		$dictionary["CONFIG"] = RenderConfig($configData);
	}
	if(FindDependency("RenderAdminLog", $dep) !== false){
		$dictionary["adminlog"] = RenderAdminLog($adminLogData);
	}
	if(FindDependency("RenderUsers", $dep) !== false){
		$dependency = FindDependency("RenderUsers", $dep);
		$dictionary["users"] = RenderUsers($configData, $cookieData, $users->UserModels, $gameData, $jams->JamModels, $adminVoteData, $dependency["RenderDepth"]);
	}
	if(FindDependency("RenderAllJams", $dep) !== false){
		$dependency1 = FindDependency("RenderAllJams", $dep);
		$dependency2 = FindDependency("RenderJams", $dep);
		$renderDepth = $dependency1["RenderDepth"] | $dependency2["RenderDepth"];
		$dictionary["jams"] = RenderJams($configData, $users->UserModels, $gameData, $jams->JamModels, $satisfaction->SatisfactionModels, $loggedInUser, $renderDepth, true);
	}else if(FindDependency("RenderJams", $dep) !== false){
		$dependency1 = FindDependency("RenderAllJams", $dep);
		$dependency2 = FindDependency("RenderJams", $dep);
		$renderDepth = $dependency1["RenderDepth"] | $dependency2["RenderDepth"];
		$loadAll = false;
		if(isset($_GET["loadAll"])){
			$loadAll = true;
		}
		$dictionary["jams"] = RenderJams($configData, $users->UserModels, $gameData, $jams->JamModels, $satisfaction->SatisfactionModels, $loggedInUser, $renderDepth, $loadAll);
	}
	if(FindDependency("RenderGames", $dep) !== false){
		$dependency = FindDependency("RenderGames", $dep);
		$dictionary["entries"] = RenderGames($users->UserModels, $gameData, $jams->JamModels, $dependency["RenderDepth"]);
	}
	if(FindDependency("RenderThemes", $dep) !== false){
		$dictionary["themes"] = RenderThemes($configData, $jams->JamModels, $themes->ThemeModels, $themes->LoggedInUserThemeVotes, $themesByVoteDifference, $themesByPopularity, $loggedInUser);
	}
	if(FindDependency("RenderAssets", $dep) !== false){
		$dictionary["assets"] = RenderAssets($assetData);
	}
	if(FindDependency("RenderPolls", $dep) !== false){
		$dictionary["polls"] = RenderPolls($polls->PollModels, $polls->LoggedInUserPollVotes);
	}
	if(FindDependency("RenderCookies", $dep) !== false){
		$dictionary["cookies"] = RenderCookies($cookieData);
	}
	if(FindDependency("RenderMessages", $dep) !== false){
		$dictionary["messages"] = RenderMessages($messages->MessageModels);
	}
	if(FindDependency("RenderStream", $dep) !== false){
		$now = Time();
		$jamTime = strtotime($dictionary["jams"]["current_jam"]["start_time"] . " UTC");
		$dictionary["stream"] = Array();

		if($jamTime + 3600 <= $now && $now <= $jamTime + 7 * 3600)
			$dictionary["stream"] = InitStream($configData);
	}
	
	$dictionary["page"] = RenderPageSpecific($page, $configData, $users->UserModels, $gameData, $jams->JamModels, $satisfaction->SatisfactionModels, $loggedInUser, $assetData, $cookieData, $adminVoteData, $nextSuggestedJamDateTime);
	
	if($loggedInUser !== false){
		if(FindDependency("RenderLoggedInUser", $dep) !== false){
			$dependency = FindDependency("RenderLoggedInUser", $dep);
			$dictionary["user"] = RenderLoggedInUser($configData, $cookieData, $users->UserModels, $gameData, $jams->JamModels, $adminVoteData, $loggedInUser, $dependency["RenderDepth"]);
		}
	}
	
	StopTimer("Init - Render");
	StopTimer("Init");
}

?>