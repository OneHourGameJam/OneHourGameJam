<?php

BeforeInit();	//Plugin hook
Init();
AfterInit();	//Plugin hook

//Initializes the site.
function Init(){
	global $dictionary, $configData, $adminLogData, $userData, $jamData, $gameData, $assetData, $loggedInUser, $satisfactionData, $adminVotes, $nextSuggestedJamDateTime, $nextJamTime, $themeData, $themesByVoteDifference, $themesByPopularity, $pollData, $cookieData, $siteActionData, $page, $dep;
	AddActionLog("Init");
	StartTimer("Init");

	
	StartTimer("Init - Load Data");

	UpdateCookies();
	$cookieData = new CookieData();

	$adminLogData = new AdminLogData();
	$configData = new ConfigData($adminLogData);
	$nextSuggestedJamDateTime = GetSuggestedNextJamDateTime($configData);

    RedirectToHttpsIfRequired($configData);

	$userData = new UserData();

	$loggedInUser = IsLoggedIn($configData, $userData);
	
	$page = ValidatePage($page, $loggedInUser);

	$jamData = new JamData();
	$gameData = new GameData();

	$themeData = new ThemeData($loggedInUser);
	$themesByVoteDifference = CalculateThemeSelectionProbabilityByVoteDifference($themeData, $configData);
	$themesByPopularity = CalculateThemeSelectionProbabilityByPopularity($themeData, $configData);

	$nextScheduledJamTime = GetNextJamDateAndTime($jamData);
	$nextSuggestedJamTime = GetSuggestedNextJamDateTime($configData);
	CheckNextJamSchedule($configData, $jamData, $themeData, $nextScheduledJamTime, $nextSuggestedJamTime);

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
		$renderAdminLog = new AdminLogPresenter($adminLogData);
		$dictionary["adminlog"] = $renderAdminLog->AdminLogRender;
	}
	if(FindDependency("RenderUsers", $dep) !== false){
		$dependency = FindDependency("RenderUsers", $dep);
		$dictionary["users"] = RenderUsers($configData, $cookieData, $userData, $gameData, $jamData, $adminVoteData, $dependency["RenderDepth"]);
	}
	if(FindDependency("RenderAllJams", $dep) !== false){
		$dependency1 = FindDependency("RenderAllJams", $dep);
		$dependency2 = FindDependency("RenderJams", $dep);
		$renderDepth = $dependency1["RenderDepth"] | $dependency2["RenderDepth"];
		$dictionary["jams"] = RenderJams($configData, $userData, $gameData, $jamData, $satisfactionData, $loggedInUser, $renderDepth, true);
	}else if(FindDependency("RenderJams", $dep) !== false){
		$dependency1 = FindDependency("RenderAllJams", $dep);
		$dependency2 = FindDependency("RenderJams", $dep);
		$renderDepth = $dependency1["RenderDepth"] | $dependency2["RenderDepth"];
		$loadAll = false;
		if(isset($_GET["loadAll"])){
			$loadAll = true;
		}
		$dictionary["jams"] = RenderJams($configData, $userData, $gameData, $jamData, $satisfactionData, $loggedInUser, $renderDepth, $loadAll);
	}
	if(FindDependency("RenderGames", $dep) !== false){
		$dependency = FindDependency("RenderGames", $dep);
		$dictionary["entries"] = RenderGames($userData, $gameData, $jamData, $dependency["RenderDepth"]);
	}
	if(FindDependency("RenderThemes", $dep) !== false){
		$dictionary["themes"] = RenderThemes($configData, $jamData, $themeData, $themesByVoteDifference, $themesByPopularity, $loggedInUser);
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
	
	$dictionary["page"] = RenderPageSpecific($page, $configData, $userData, $gameData, $jamData, $themeData, $pollData,  $satisfactionData, $loggedInUser, $assetData, $cookieData, $adminVoteData, $nextSuggestedJamDateTime, $adminLogData);
	
	if($loggedInUser !== false){
		if(FindDependency("RenderLoggedInUser", $dep) !== false){
			$dependency = FindDependency("RenderLoggedInUser", $dep);
			$dictionary["user"] = RenderLoggedInUser($configData, $cookieData, $userData, $gameData, $jamData, $adminVoteData, $loggedInUser, $dependency["RenderDepth"]);
		}
	}
	
	StopTimer("Init - Render");
	StopTimer("Init");
}

?>