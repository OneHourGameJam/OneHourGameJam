<?php

BeforeInit();	//Plugin hook
Init();
AfterInit();	//Plugin hook

//Initializes the site.
function Init(){
	global $dictionary, $configData, $adminLogData, $userData, $jamData, $gameData, $platformData, $platformGameData, $assetData, $loggedInUser, $satisfactionData, $adminVotes, $nextSuggestedJamDateTime, $nextJamTime, $themeData, $themesByVoteDifference, $themesByPopularity, $pollData, $cookieData, $siteActionData, $themeIdeaData, $commonDependencies, $pageSettings, $page, $dbConn, $userDbInterface, $sessionDbInterface, $themeDbInterface, $themeVoteDbInterface, $themeIdeaDbInterface, $satisfactionDbInterface, $pollDbInterface, $pollOptionDbInterface, $pollVoteDbInterface, $platformDbInterface, $platformGameDbInterface, $jamDbInterface, $gameDbInterface, $configDbInterface, $assetDbInterface, $adminVoteDbInterface, $adminLogDbInterface;
	AddActionLog("Init");
	StartTimer("Init");

	MigrateDatabase();
	
	StartTimer("Init - Database Interfaces");

	$userDbInterface = new UserDbInterface($dbConn);
	$sessionDbInterface = new SessionDbInterface($dbConn);
	$themeDbInterface = new ThemeDbInterface($dbConn);
	$themeVoteDbInterface = new ThemeVoteDbInterface($dbConn);
	$themeIdeaDbInterface = new ThemeIdeaDbInterface($dbConn);
	$satisfactionDbInterface = new SatisfactionDbInterface($dbConn);
	$pollDbInterface = new PollDbInterface($dbConn);
	$pollOptionDbInterface = new PollOptionDbInterface($dbConn);
	$pollVoteDbInterface = new PollVoteDbInterface($dbConn);
	$platformDbInterface = new PlatformDbInterface($dbConn);
	$platformGameDbInterface = new PlatformGameDbInterface($dbConn);
	$jamDbInterface = new JamDbInterface($dbConn);
	$gameDbInterface = new GameDbInterface($dbConn);
	$configDbInterface = new ConfigDbInterface($dbConn);
	$assetDbInterface = new AssetDbInterface($dbConn);
	$adminVoteDbInterface = new AdminVoteDbInterface($dbConn);
	$adminLogDbInterface = new AdminLogDbInterface($dbConn);
	
	StopTimer("Init - Database Interfaces");

	StartTimer("Init - Load Data");

	CookieController::UpdateCookies();
	$cookieData = new CookieData();

	$adminLogData = new AdminLogData($adminLogDbInterface);
	$configData = new ConfigData($configDbInterface, $adminLogData);
	$nextSuggestedJamDateTime = GetSuggestedNextJamDateTime($configData);

    RedirectToHttpsIfRequired($configData);

	$userData = new UserData($userDbInterface, $sessionDbInterface);

	$loggedInUser = IsLoggedIn($configData, $userData);
	
	$page = ValidatePage($page, $loggedInUser);
	$dependencies = LoadDependencies($page, $pageSettings, $commonDependencies);

	$jamData = new JamData($jamDbInterface);
	$gameData = new GameData($gameDbInterface);
	$platformData = new PlatformData($platformDbInterface);
	$platformGameData = new PlatformGameData($platformGameDbInterface);

	$themeData = new ThemeData($themeDbInterface, $themeVoteDbInterface, $loggedInUser);

	$nextScheduledJamTime = $jamData->GetNextJamDateAndTime();
	JamController::CheckNextJamSchedule($configData, $jamData, $themeData, $nextScheduledJamTime, $nextSuggestedJamDateTime, $adminLogData);

	$siteActionData = new SiteActionData($configData);
	$assetData = new AssetData($assetDbInterface);
	$pollData = new PollData($pollDbInterface, $pollOptionDbInterface, $pollVoteDbInterface, $loggedInUser);
    $satisfactionData = new SatisfactionData($satisfactionDbInterface, $configData);
    $adminVoteData = new AdminVoteData($adminVoteDbInterface, $loggedInUser);
	$messageData = new MessageData($siteActionData);
	$themeIdeaData = new ThemeIdeaData($themeIdeaDbInterface, $loggedInUser);
	
	StopTimer("Init - Load Data");
	StartTimer("Init - Process");

	$themesByVoteDifference = ThemeController::CalculateThemeSelectionProbabilityByVoteDifference($themeData, $configData);
	$themesByPopularity = ThemeController::CalculateThemeSelectionProbabilityByPopularity($themeData, $configData);
	JamController::ProcessJamStates($jamData, $themeData, $configData, $adminLogData);

	PerformPendingSiteAction($configData, $siteActionData, $loggedInUser);
	$streamData = StreamController::InitStream($configData);

	StopTimer("Init - Process");
	StartTimer("Init - Render");

	//print(ArrayToHTML($platformData->GetAllPublicData()));

	loadCSRFToken();
	$dictionary["csrf_token"] = $_SESSION["csrf_token"];
 
	if(FindDependency("RenderConfig", $dependencies) !== false){
		$dictionary["CONFIG"] = ConfigurationPresenter::RenderConfig($configData);
	}
	if(FindDependency("RenderAdminLog", $dependencies) !== false){
		$renderAdminLog = new AdminLogPresenter($adminLogData, $userData);
		$dictionary["adminlog"] = $renderAdminLog->AdminLogRender;
	}
	if(FindDependency("RenderUsers", $dependencies) !== false){
		$dependency = FindDependency("RenderUsers", $dependencies);
		$dictionary["users"] = UserPresenter::RenderUsers($configData, $cookieData, $userData, $gameData, $jamData, $platformData, $platformGameData, $adminVoteData, $dependency["RenderDepth"]);
	}
	if(FindDependency("RenderAllJams", $dependencies) !== false){
		$dependency1 = FindDependency("RenderAllJams", $dependencies);
		$dependency2 = FindDependency("RenderJams", $dependencies);
		$renderDepth = $dependency1["RenderDepth"] | $dependency2["RenderDepth"];
		$dictionary["jams"] = JamPresenter::RenderJams($configData, $userData, $gameData, $jamData, $platformData, $platformGameData, $satisfactionData, $loggedInUser, $renderDepth, true);
	}else if(FindDependency("RenderJams", $dependencies) !== false){
		$dependency1 = FindDependency("RenderAllJams", $dependencies);
		$dependency2 = FindDependency("RenderJams", $dependencies);
		$renderDepth = $dependency1["RenderDepth"] | $dependency2["RenderDepth"];
		$loadAll = false;
		if(isset($_GET["loadAll"])){
			$loadAll = true;
		}
		$dictionary["jams"] = JamPresenter::RenderJams($configData, $userData, $gameData, $jamData, $platformData, $platformGameData, $satisfactionData, $loggedInUser, $renderDepth, $loadAll);
	}
	if(FindDependency("RenderGames", $dependencies) !== false){
		$dependency = FindDependency("RenderGames", $dependencies);
		$dictionary["entries"] = GamePresenter::RenderGames($userData, $gameData, $jamData, $platformData, $platformGameData, $dependency["RenderDepth"]);
	}
	if(FindDependency("RenderThemes", $dependencies) !== false){
		$dependency = FindDependency("RenderThemes", $dependencies);
		$dictionary["themes"] = ThemePresenter::RenderThemes($configData, $jamData, $userData, $themeData, $themeIdeaData, $themesByVoteDifference, $themesByPopularity, $loggedInUser, $dependency["RenderDepth"]);
	}
	if(FindDependency("RenderAssets", $dependencies) !== false){
		$dictionary["assets"] = AssetPresenter::RenderAssets($assetData, $userData);
	}
	if(FindDependency("RenderPolls", $dependencies) !== false){
		$dictionary["polls"] = PollPresenter::RenderPolls($pollData);
	}
	if(FindDependency("RenderCookies", $dependencies) !== false){
		$dictionary["cookies"] = CookiePresenter::RenderCookies($cookieData);
	}
	if(FindDependency("RenderMessages", $dependencies) !== false){
		$dictionary["messages"] = MessagePresenter::RenderMessages($messageData);
	}
	if(FindDependency("RenderStream", $dependencies) !== false){
		$dictionary["stream"] = StreamPresenter::RenderStream($streamData, $configData);
	}
	if(FindDependency("RenderPlatforms", $dependencies) !== false){
		$dictionary["platforms"] = PlatformPresenter::RenderPlatforms($platformData);
	}
	
	$dictionary["page"] = RenderPageSpecific($page, $configData, $userData, $gameData, $jamData, $themeData, $themeIdeaData, $platformData, $platformGameData, $pollData,  $satisfactionData, $loggedInUser, $assetData, $cookieData, $adminVoteData, $nextSuggestedJamDateTime, $adminLogData);
	
	if($loggedInUser !== false){
		if(FindDependency("RenderLoggedInUser", $dependencies) !== false){
			$dependency = FindDependency("RenderLoggedInUser", $dependencies);
			$dictionary["user"] = UserPresenter::RenderLoggedInUser($configData, $cookieData, $userData, $gameData, $jamData, $platformData, $platformGameData, $adminVoteData, $loggedInUser, $dependency["RenderDepth"]);
		}
	}
	
	StopTimer("Init - Render");
	StopTimer("Init");
}

?>