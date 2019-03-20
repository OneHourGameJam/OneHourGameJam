<?php

BeforeInit();	//Plugin hook
Init();
AfterInit();	//Plugin hook

//Initializes the site.
function Init(){
	global $dictionary, $config, $adminLog, $users, $jams, $games, $assets, $loggedInUser, $satisfaction, $adminVotes, $loggedInUserAdminVotes, $nextSuggestedJamDateTime, $nextJamTime, $themes, $loggedInUserThemeVotes, $themesByVoteDifference, $themesByPopularity, $polls, $loggedInUserPollVotes, $cookies, $actions, $page, $dep;
	AddActionLog("Init");
	StartTimer("Init");

	
	StartTimer("Init - Load Data");

	UpdateCookies();
	$cookies = LoadCookies();

	$config = LoadConfig();
	$nextSuggestedJamDateTime = GetSuggestedNextJamDateTime($config);

    RedirectToHttpsIfRequired($config);

	$adminLog = LoadAdminLog();
	$users = new UserData();

	$loggedInUser = IsLoggedIn($config, $users->UserModels);
	
	$page = ValidatePage($page, $loggedInUser);

	$jams =  LoadJams();
	$games = LoadGames();

	$themes = new ThemeData();
	$loggedInUserThemeVotes = LoadUserThemeVotes($loggedInUser);
	$themesByVoteDifference = CalculateThemeSelectionProbabilityByVoteDifference($themes->ThemeModels, $config);
	$themesByPopularity = CalculateThemeSelectionProbabilityByPopularity($themes->ThemeModels, $config);

	$nextScheduledJamTime = GetNextJamDateAndTime($jams);
	$nextSuggestedJamTime = GetSuggestedNextJamDateTime($config);
	CheckNextJamSchedule($config, $jams, $themes->ThemeModels, $nextScheduledJamTime, $nextSuggestedJamTime);

	$actions = LoadSiteActions($config);
	$assets = LoadAssets();
	$polls = LoadPolls();
	$loggedInUserPollVotes = LoadLoggedInUserPollVotes($loggedInUser);
    $satisfaction = new SatisfactionData($config);
    $adminVotes = LoadAdminVotes();
	$loggedInUserAdminVotes = LoadLoggedInUsersAdminVotes($loggedInUser);
	$messages = LoadMessages($actions);
	
	StopTimer("Init - Load Data");
	StartTimer("Init - Render");

	PerformPendingSiteAction($config, $actions, $loggedInUser);
 
	if(FindDependency("RenderConfig", $dep) !== false){
		$dictionary["CONFIG"] = RenderConfig($config);
	}
	if(FindDependency("RenderAdminLog", $dep) !== false){
		$dictionary["adminlog"] = RenderAdminLog($adminLog);
	}
	if(FindDependency("RenderUsers", $dep) !== false){
		$dependency = FindDependency("RenderUsers", $dep);
		$dictionary["users"] = RenderUsers($config, $cookies, $users->UserModels, $games, $jams, $adminVotes, $loggedInUserAdminVotes, $dependency["RenderDepth"]);
	}
	if(FindDependency("RenderAllJams", $dep) !== false){
		$dependency1 = FindDependency("RenderAllJams", $dep);
		$dependency2 = FindDependency("RenderJams", $dep);
		$renderDepth = $dependency1["RenderDepth"] | $dependency2["RenderDepth"];
		$dictionary["jams"] = RenderJams($config, $users->UserModels, $games, $jams, $satisfaction->SatisfactionModels, $loggedInUser, $renderDepth, true);
	}else if(FindDependency("RenderJams", $dep) !== false){
		$dependency1 = FindDependency("RenderAllJams", $dep);
		$dependency2 = FindDependency("RenderJams", $dep);
		$renderDepth = $dependency1["RenderDepth"] | $dependency2["RenderDepth"];
		$loadAll = false;
		if(isset($_GET["loadAll"])){
			$loadAll = true;
		}
		$dictionary["jams"] = RenderJams($config, $users->UserModels, $games, $jams, $satisfaction->SatisfactionModels, $loggedInUser, $renderDepth, $loadAll);
	}
	if(FindDependency("RenderGames", $dep) !== false){
		$dependency = FindDependency("RenderGames", $dep);
		$dictionary["entries"] = RenderGames($users->UserModels, $games, $jams, $dependency["RenderDepth"]);
	}
	if(FindDependency("RenderThemes", $dep) !== false){
		$dictionary["themes"] = RenderThemes($config, $jams, $themes->ThemeModels, $loggedInUserThemeVotes, $themesByVoteDifference, $themesByPopularity, $loggedInUser);
	}
	if(FindDependency("RenderAssets", $dep) !== false){
		$dictionary["assets"] = RenderAssets($assets);
	}
	if(FindDependency("RenderPolls", $dep) !== false){
		$dictionary["polls"] = RenderPolls($polls, $loggedInUserPollVotes);
	}
	if(FindDependency("RenderCookies", $dep) !== false){
		$dictionary["cookies"] = RenderCookies($cookies);
	}
	if(FindDependency("RenderMessages", $dep) !== false){
		$dictionary["messages"] = RenderMessages($messages);
	}
	if(FindDependency("RenderStream", $dep) !== false){
		$now = Time();
		$jamTime = strtotime($dictionary["jams"]["current_jam"]["start_time"] . " UTC");
		$dictionary["stream"] = Array();

		if($jamTime + 3600 <= $now && $now <= $jamTime + 7 * 3600)
			$dictionary["stream"] = InitStream($config);
	}
	
	$dictionary["page"] = RenderPageSpecific($page, $config, $users->UserModels, $games, $jams, $satisfaction->SatisfactionModels, $loggedInUser, $assets, $cookies, $adminVotes, $loggedInUserAdminVotes, $nextSuggestedJamDateTime);
	
	if($loggedInUser !== false){
		if(FindDependency("RenderLoggedInUser", $dep) !== false){
			$dependency = FindDependency("RenderLoggedInUser", $dep);
			$dictionary["user"] = RenderLoggedInUser($config, $cookies, $users->UserModels, $games, $jams, $adminVotes, $loggedInUserAdminVotes, $loggedInUser, $dependency["RenderDepth"]);
		}
	}
	
	StopTimer("Init - Render");
	StopTimer("Init");
}

?>