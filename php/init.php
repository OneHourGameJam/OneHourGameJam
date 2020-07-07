<?php

BeforeInit();	//Plugin hook
Init();
AfterInit();	//Plugin hook

//Initializes the site.
function Init(){
	global $dictionary, $configData, $adminLogData, $userData, $jamData, $gameData, $platformData, $platformGameData, $assetData, $loggedInUser, $satisfactionData, $adminVotes, $nextSuggestedJamDateTime, $nextJamTime, $themeData, $themesByVoteDifference, $themesByPopularity, $pollData, $cookieData, $siteActionData, $themeIdeasData, $commonDependencies, $pageSettings, $page;
	AddActionLog("Init");
	StartTimer("Init");

	MigrateDatabase();

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
	$dependencies = LoadDependencies($page, $pageSettings, $commonDependencies);

	$jamData = new JamData();
	$gameData = new GameData();
	$platformData = new PlatformData();
	$platformGameData = new PlatformGameData();

	$themeData = new ThemeData($loggedInUser);
	$themesByVoteDifference = CalculateThemeSelectionProbabilityByVoteDifference($themeData, $configData);
	$themesByPopularity = CalculateThemeSelectionProbabilityByPopularity($themeData, $configData);

	$nextScheduledJamTime = GetNextJamDateAndTime($jamData);
	$nextSuggestedJamTime = GetSuggestedNextJamDateTime($configData);
	CheckNextJamSchedule($configData, $jamData, $themeData, $nextScheduledJamTime, $nextSuggestedJamTime, $adminLogData);

	$siteActionData = new SiteActionData($configData);
	$assetData = new AssetData();
	$pollData = new PollData($loggedInUser);
    $satisfactionData = new SatisfactionData($configData);
    $adminVoteData = new AdminVoteData($loggedInUser);
	$messageData = new MessageData($siteActionData);
	$themeIdeasData = new ThemeIdeasData($loggedInUser);
	
	StopTimer("Init - Load Data");
	StartTimer("Init - Process");

	ProcessJamStates($jamData, $themeData, $configData, $adminLogData);

	StopTimer("Init - Process");
	StartTimer("Init - Render");

	PerformPendingSiteAction($configData, $siteActionData, $loggedInUser);

	loadCSRFToken();
	$dictionary["csrf_token"] = $_SESSION["csrf_token"];
 
	if(FindDependency("RenderConfig", $dependencies) !== false){
		$dictionary["CONFIG"] = RenderConfig($configData);
	}
	if(FindDependency("RenderAdminLog", $dependencies) !== false){
		$renderAdminLog = new AdminLogPresenter($adminLogData, $userData);
		$dictionary["adminlog"] = $renderAdminLog->AdminLogRender;
	}
	if(FindDependency("RenderUsers", $dependencies) !== false){
		$dependency = FindDependency("RenderUsers", $dependencies);
		$dictionary["users"] = RenderUsers($configData, $cookieData, $userData, $gameData, $jamData, $platformData, $platformGameData, $adminVoteData, $dependency["RenderDepth"]);
	}
	if(FindDependency("RenderAllJams", $dependencies) !== false){
		$dependency1 = FindDependency("RenderAllJams", $dependencies);
		$dependency2 = FindDependency("RenderJams", $dependencies);
		$renderDepth = $dependency1["RenderDepth"] | $dependency2["RenderDepth"];
		$dictionary["jams"] = RenderJams($configData, $userData, $gameData, $jamData, $platformData, $platformGameData, $satisfactionData, $loggedInUser, $renderDepth, true);
	}else if(FindDependency("RenderJams", $dependencies) !== false){
		$dependency1 = FindDependency("RenderAllJams", $dependencies);
		$dependency2 = FindDependency("RenderJams", $dependencies);
		$renderDepth = $dependency1["RenderDepth"] | $dependency2["RenderDepth"];
		$loadAll = false;
		if(isset($_GET["loadAll"])){
			$loadAll = true;
		}
		$dictionary["jams"] = RenderJams($configData, $userData, $gameData, $jamData, $platformData, $platformGameData, $satisfactionData, $loggedInUser, $renderDepth, $loadAll);
	}
	if(FindDependency("RenderGames", $dependencies) !== false){
		$dependency = FindDependency("RenderGames", $dependencies);
		$dictionary["entries"] = RenderGames($userData, $gameData, $jamData, $platformData, $platformGameData, $dependency["RenderDepth"]);
	}
	if(FindDependency("RenderThemes", $dependencies) !== false){
		$dependency = FindDependency("RenderThemes", $dependencies);
		$dictionary["themes"] = RenderThemes($configData, $jamData, $userData, $themeData, $themeIdeasData, $themesByVoteDifference, $themesByPopularity, $loggedInUser, $dependency["RenderDepth"]);
	}
	if(FindDependency("RenderAssets", $dependencies) !== false){
		$dictionary["assets"] = RenderAssets($assetData, $userData);
	}
	if(FindDependency("RenderPolls", $dependencies) !== false){
		$dictionary["polls"] = RenderPolls($pollData);
	}
	if(FindDependency("RenderCookies", $dependencies) !== false){
		$dictionary["cookies"] = RenderCookies($cookieData);
	}
	if(FindDependency("RenderMessages", $dependencies) !== false){
		$dictionary["messages"] = RenderMessages($messageData);
	}
	if(FindDependency("RenderStream", $dependencies) !== false){
		$now = Time();
		$jamTime = strtotime($dictionary["jams"]["current_jam"]["start_time"] . " UTC");
		$dictionary["stream"] = Array();

		if($jamTime + 3600 <= $now && $now <= $jamTime + 7 * 3600)
			$dictionary["stream"] = InitStream($configData);
	}
	
	$dictionary["page"] = RenderPageSpecific($page, $configData, $userData, $gameData, $jamData, $themeData, $themeIdeasData, $platformData, $platformGameData, $pollData,  $satisfactionData, $loggedInUser, $assetData, $cookieData, $adminVoteData, $nextSuggestedJamDateTime, $adminLogData);
	
	if($loggedInUser !== false){
		if(FindDependency("RenderLoggedInUser", $dependencies) !== false){
			$dependency = FindDependency("RenderLoggedInUser", $dependencies);
			$dictionary["user"] = RenderLoggedInUser($configData, $cookieData, $userData, $gameData, $jamData, $platformData, $platformGameData, $adminVoteData, $loggedInUser, $dependency["RenderDepth"]);
		}
	}
	
	StopTimer("Init - Render");
	StopTimer("Init");
}

?>