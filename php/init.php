<?php

BeforeInit();	//Plugin hook
Init();
AfterInit();	//Plugin hook

//Initializes the site.
function Init(){
	global $dictionary, $configData, $userData, $jamData, $gameData, $platformData, $platformGameData, $assetData, $loggedInUser, $satisfactionData, $adminVotes, $nextSuggestedJamDateTime, $nextJamTime, $themeData, $themesByVoteDifference, $themesByPopularity, $pollData, $cookieData, $siteActionData, $themeIdeaData, $commonDependencies, $pageSettings, $userDbInterface, $sessionDbInterface, $themeDbInterface, $themeVoteDbInterface, $themeIdeaDbInterface, $satisfactionDbInterface, $pollDbInterface, $pollOptionDbInterface, $pollVoteDbInterface, $platformDbInterface, $platformGameDbInterface, $jamDbInterface, $gameDbInterface, $configDbInterface, $assetDbInterface, $adminVoteDbInterface, $page;
	AddActionLog("Init");
	StartTimer("Init");

	StartTimer("Init - Database");

	$database = new Database();
	$database->MigrateDatabase();
	
	StopTimer("Init - Database");

	StartTimer("Init - Plugins");

	$messageService = new MessageService();

	$plugins = Array(
		new \Plugins\AdminLog\AdminLogPlugin($messageService),
		new /*\Plugins\entry\*/GamePlugin($messageService)
	);

	foreach($plugins as $i => $plugin){
		foreach($plugin->PageSettings() as $pageName => $pageSetting){
			$pageSettings[$pageName] = $pageSetting;
		}
	}
	
	foreach($plugins as $i => $plugin){
		$plugin->EstablishDatabaseConnection();
	}
	
	foreach($plugins as $i => $plugin){
		$plugin->RetrieveData();
	}
	
	StopTimer("Init - Plugins");
	
	StartTimer("Init - Database Interfaces");

	$userDbInterface = new UserDbInterface($database);
	$sessionDbInterface = new SessionDbInterface($database);
	$themeDbInterface = new ThemeDbInterface($database);
	$themeVoteDbInterface = new ThemeVoteDbInterface($database);
	$themeIdeaDbInterface = new ThemeIdeaDbInterface($database);
	$satisfactionDbInterface = new SatisfactionDbInterface($database);
	$pollDbInterface = new PollDbInterface($database);
	$pollOptionDbInterface = new PollOptionDbInterface($database);
	$pollVoteDbInterface = new PollVoteDbInterface($database);
	$platformDbInterface = new PlatformDbInterface($database);
	$platformGameDbInterface = new PlatformGameDbInterface($database);
	$jamDbInterface = new JamDbInterface($database);
	$gameDbInterface = new GameDbInterface($database);
	$configDbInterface = new ConfigDbInterface($database);
	$assetDbInterface = new AssetDbInterface($database);
	$adminVoteDbInterface = new AdminVoteDbInterface($database);
	
	StopTimer("Init - Database Interfaces");

	StartTimer("Init - Load Data");

	CookieController::UpdateCookies();
	$cookieData = new CookieData();

	$configData = new ConfigData($configDbInterface, $messageService);
	$nextSuggestedJamDateTime = GetSuggestedNextJamDateTime($configData);

    RedirectToHttpsIfRequired($configData);

	$userData = new UserData($userDbInterface, $sessionDbInterface);

	$loggedInUser = IsLoggedIn($configData, $userData);
	
	$page = PAGE_MAIN;
	if(isset($_GET[GET_PAGE])){
		$page = strtolower(trim($_GET[GET_PAGE]));
	}
	$page = ValidatePage($page, $loggedInUser);
	$dependencies = LoadDependencies($page, $pageSettings, $commonDependencies);

	$jamData = new JamData($jamDbInterface);
	$gameData = new GameData($gameDbInterface);
	$platformData = new PlatformData($platformDbInterface);
	$platformGameData = new PlatformGameData($platformGameDbInterface);

	$themeData = new ThemeData($themeDbInterface, $themeVoteDbInterface, $loggedInUser);

	$nextScheduledJamTime = $jamData->GetNextJamDateAndTime();
	JamController::CheckNextJamSchedule($messageService, $configData, $jamData, $themeData, $nextScheduledJamTime, $nextSuggestedJamDateTime);

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
	JamController::ProcessJamStates($messageService, $jamData, $themeData, $configData);

	PerformPendingSiteAction($messageService, $configData, $siteActionData, $loggedInUser);
	$streamData = StreamController::InitStream($configData);

	StopTimer("Init - Process");
	StartTimer("Init - Render");

	//print(ArrayToHTML($platformData->GetAllPublicData()));

	loadCSRFToken();
	$dictionary["csrf_token"] = $_SESSION[SESSION_CSRF_TOKEN];
 
	if(FindDependency(RENDER_CONFIG, $dependencies) !== false){
		$dictionary["CONFIG"] = ConfigurationPresenter::RenderConfig($configData);
	}
	if(FindDependency(RENDER_USERS, $dependencies) !== false){
		$dependency = FindDependency(RENDER_USERS, $dependencies);
		$dictionary["users"] = UserPresenter::RenderUsers($configData, $cookieData, $userData, $gameData, $jamData, $platformData, $platformGameData, $adminVoteData, $dependency["RenderDepth"]);
	}
	if(FindDependency(RENDER_ALL_JAMS, $dependencies) !== false){
		$dependency1 = FindDependency(RENDER_ALL_JAMS, $dependencies);
		$dependency2 = FindDependency(RENDER_JAMS, $dependencies);
		$renderDepth = $dependency1["RenderDepth"] | $dependency2["RenderDepth"];
		$dictionary["jams"] = JamPresenter::RenderJams($configData, $userData, $gameData, $jamData, $platformData, $platformGameData, $satisfactionData, $loggedInUser, $renderDepth, true);
	}else if(FindDependency(RENDER_JAMS, $dependencies) !== false){
		$dependency1 = FindDependency(RENDER_ALL_JAMS, $dependencies);
		$dependency2 = FindDependency(RENDER_JAMS, $dependencies);
		$renderDepth = $dependency1["RenderDepth"] | $dependency2["RenderDepth"];
		$loadAll = false;
		if(isset($_GET[GET_LOAD_ALL])){
			$loadAll = true;
		}
		$dictionary["jams"] = JamPresenter::RenderJams($configData, $userData, $gameData, $jamData, $platformData, $platformGameData, $satisfactionData, $loggedInUser, $renderDepth, $loadAll);
	}
	if(FindDependency(RENDER_GAMES, $dependencies) !== false){
		$dependency = FindDependency(RENDER_GAMES, $dependencies);
		$dictionary["entries"] = GamePresenter::RenderGames($userData, $gameData, $jamData, $platformData, $platformGameData, $dependency["RenderDepth"]);
	}
	if(FindDependency(RENDER_THEMES, $dependencies) !== false){
		$dependency = FindDependency(RENDER_THEMES, $dependencies);
		$dictionary["themes"] = ThemePresenter::RenderThemes($configData, $jamData, $userData, $themeData, $themeIdeaData, $themesByVoteDifference, $themesByPopularity, $loggedInUser, $dependency["RenderDepth"]);
	}
	if(FindDependency(RENDER_ASSETS, $dependencies) !== false){
		$dictionary["assets"] = AssetPresenter::RenderAssets($assetData, $userData);
	}
	if(FindDependency(RENDER_POLLS, $dependencies) !== false){
		$dictionary["polls"] = PollPresenter::RenderPolls($pollData);
	}
	if(FindDependency(RENDER_COOKIES, $dependencies) !== false){
		$dictionary["cookies"] = CookiePresenter::RenderCookies($cookieData);
	}
	if(FindDependency(RENDER_MESSAGES, $dependencies) !== false){
		$dictionary["messages"] = MessagePresenter::RenderMessages($messageData);
	}
	if(FindDependency(RENDER_STREAM, $dependencies) !== false){
		$dictionary["stream"] = StreamPresenter::RenderStream($streamData, $configData);
	}
	if(FindDependency(RENDER_PLATFORMS, $dependencies) !== false){
		$dictionary["platforms"] = PlatformPresenter::RenderPlatforms($platformData);
	}
	if(FindDependency(RENDER_FORMS, $dependencies) !== false){
		$dictionary["forms"] = FormPresenter::RenderForms($plugins);
	}
	
	$dictionary["page"] = RenderPageSpecific($page, $configData, $userData, $gameData, $jamData, $themeData, $themeIdeaData, $platformData, $platformGameData, $pollData,  $satisfactionData, $loggedInUser, $assetData, $cookieData, $adminVoteData, $nextSuggestedJamDateTime, $plugins);
	
	if($loggedInUser !== false){
		if(FindDependency(RENDER_LOGGED_IN_USER, $dependencies) !== false){
			$dependency = FindDependency(RENDER_LOGGED_IN_USER, $dependencies);
			$dictionary["user"] = UserPresenter::RenderLoggedInUser($configData, $cookieData, $userData, $gameData, $jamData, $platformData, $platformGameData, $adminVoteData, $loggedInUser, $dependency["RenderDepth"]);
		}
	}

	foreach($plugins as $plugin){
		if($plugin->ShouldBeRendered($dependencies)){;
			$renders = $plugin->Render($userData);	
			foreach($renders as $renderIdentifier => $render){
				$dictionary[$renderIdentifier] = $render;
			}
		}
	}
	
	StopTimer("Init - Render");
	StopTimer("Init");
}

?>