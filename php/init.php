<?php

BeforeInit();	//Plugin hook
Init();
AfterInit();	//Plugin hook

//Initializes the site.
function Init(){
	global $dictionary, $configData, $adminLogData, $users, $jamData, $gameData, $assetData, $loggedInUser, $satisfactionData, $adminVotes, $nextSuggestedJamDateTime, $nextJamTime, $themes, $themesByVoteDifference, $themesByPopularity, $pollData, $cookieData, $siteActionData, $page, $dep;
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

	$jamData = new JamData();
	$gameData = new GameData();

	$themes = new ThemeData($loggedInUser);
	$themesByVoteDifference = CalculateThemeSelectionProbabilityByVoteDifference($themes->ThemeModels, $configData);
	$themesByPopularity = CalculateThemeSelectionProbabilityByPopularity($themes->ThemeModels, $configData);

	$nextScheduledJamTime = GetNextJamDateAndTime($jamData);
	$nextSuggestedJamTime = GetSuggestedNextJamDateTime($configData);
	CheckNextJamSchedule($configData, $jamData, $themes->ThemeModels, $nextScheduledJamTime, $nextSuggestedJamTime);

	$siteActionData = new SiteActionData($configData);
	$assetData = new AssetData();
	$pollData = new PollData($loggedInUser);
    $satisfactionData = new SatisfactionData($configData);
    $adminVoteData = new AdminVoteData($loggedInUser);
	$messageData = new MessageData($siteActionData);
	
	StopTimer("Init - Load Data");
	StartTimer("Init - Render");

	PerformPendingSiteAction($configData, $siteActionData, $loggedInUser);
 
	if(FindDependency("RenderConfig", $dep) !== false){
		$dictionary["CONFIG"] = RenderConfig($configData);
	}
	if(FindDependency("RenderAdminLog", $dep) !== false){
		$dictionary["adminlog"] = RenderAdminLog($adminLogData);
	}
	if(FindDependency("RenderUsers", $dep) !== false){
		$dependency = FindDependency("RenderUsers", $dep);
		$dictionary["users"] = RenderUsers($configData, $cookieData, $users->UserModels, $gameData, $jamData, $adminVoteData, $dependency["RenderDepth"]);
	}
	if(FindDependency("RenderAllJams", $dep) !== false){
		$dependency1 = FindDependency("RenderAllJams", $dep);
		$dependency2 = FindDependency("RenderJams", $dep);
		$renderDepth = $dependency1["RenderDepth"] | $dependency2["RenderDepth"];
		$dictionary["jams"] = RenderJams($configData, $users->UserModels, $gameData, $jamData, $satisfactionData, $loggedInUser, $renderDepth, true);
	}else if(FindDependency("RenderJams", $dep) !== false){
		$dependency1 = FindDependency("RenderAllJams", $dep);
		$dependency2 = FindDependency("RenderJams", $dep);
		$renderDepth = $dependency1["RenderDepth"] | $dependency2["RenderDepth"];
		$loadAll = false;
		if(isset($_GET["loadAll"])){
			$loadAll = true;
		}
		$dictionary["jams"] = RenderJams($configData, $users->UserModels, $gameData, $jamData, $satisfactionData, $loggedInUser, $renderDepth, $loadAll);
	}
	if(FindDependency("RenderGames", $dep) !== false){
		$dependency = FindDependency("RenderGames", $dep);
		$dictionary["entries"] = RenderGames($users->UserModels, $gameData, $jamData, $dependency["RenderDepth"]);
	}
	if(FindDependency("RenderThemes", $dep) !== false){
		$dictionary["themes"] = RenderThemes($configData, $jamData, $themes->ThemeModels, $themes->LoggedInUserThemeVotes, $themesByVoteDifference, $themesByPopularity, $loggedInUser);
	}
	if(FindDependency("RenderAssets", $dep) !== false){
		$dictionary["assets"] = RenderAssets($assetData);
	}
	if(FindDependency("RenderPolls", $dep) !== false){
		$dictionary["polls"] = RenderPolls($pollData);
	}
	if(FindDependency("RenderCookies", $dep) !== false){
		$dictionary["cookies"] = RenderCookies($cookieData);
	}
	if(FindDependency("RenderMessages", $dep) !== false){
		$dictionary["messages"] = RenderMessages($messageData);
	}
	if(FindDependency("RenderStream", $dep) !== false){
		$now = Time();
		$jamTime = strtotime($dictionary["jams"]["current_jam"]["start_time"] . " UTC");
		$dictionary["stream"] = Array();

		if($jamTime + 3600 <= $now && $now <= $jamTime + 7 * 3600)
			$dictionary["stream"] = InitStream($configData);
	}
	
	$dictionary["page"] = RenderPageSpecific($page, $configData, $users->UserModels, $gameData, $jamData, $satisfactionData, $loggedInUser, $assetData, $cookieData, $adminVoteData, $nextSuggestedJamDateTime);
	
	if($loggedInUser !== false){
		if(FindDependency("RenderLoggedInUser", $dep) !== false){
			$dependency = FindDependency("RenderLoggedInUser", $dep);
			$dictionary["user"] = RenderLoggedInUser($configData, $cookieData, $users->UserModels, $gameData, $jamData, $adminVoteData, $loggedInUser, $dependency["RenderDepth"]);
		}
	}
	
	StopTimer("Init - Render");
	StopTimer("Init");
}

?>