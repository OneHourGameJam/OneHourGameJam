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
	$users = LoadUsers();

	$loggedInUser = IsLoggedIn($config, $users);
	
	$page = ValidatePage($page, $loggedInUser);

	$jams =  LoadJams();
	$games = LoadGames();

	$themes = LoadThemes();
	$loggedInUserThemeVotes = LoadUserThemeVotes($loggedInUser);
	$themesByVoteDifference = CalculateThemeSelectionProbabilityByVoteDifference($themes, $config);
	$themesByPopularity = CalculateThemeSelectionProbabilityByPopularity($themes, $config);

	$nextScheduledJamTime = GetNextJamDateAndTime($jams);
	$nextSuggestedJamTime = GetSuggestedNextJamDateTime($config);
	CheckNextJamSchedule($config, $jams, $themes, $nextScheduledJamTime, $nextSuggestedJamTime);

	$actions = LoadSiteActions($config);
	$assets = LoadAssets();
	$polls = LoadPolls();
	$loggedInUserPollVotes = LoadLoggedInUserPollVotes($loggedInUser);
    $satisfaction = LoadSatisfaction($config);
    $adminVotes = LoadAdminVotes();
	$loggedInUserAdminVotes = LoadLoggedInUsersAdminVotes($loggedInUser);
	$messages = LoadMessages($actions);
	
	StopTimer("Init - Load Data");
	StartTimer("Init - Render");

	PerformPendingSiteAction($config, $actions, $loggedInUser);
 
	if(array_search("RenderStream", $dep) !== false){
		$dictionary["stream"] = InitStream();
	}
	if(array_search("RenderConfig", $dep) !== false){
		$dictionary["CONFIG"] = RenderConfig($config);
	}
	if(array_search("RenderAdminLog", $dep) !== false){
		$dictionary["adminlog"] = RenderAdminLog($adminLog);
	}
	if(array_search("RenderUsers", $dep) !== false){
		$dictionary["users"] = RenderUsers($config, $cookies, $users, $games, $jams, $adminVotes, $loggedInUserAdminVotes);
	}
	if(array_search("RenderJams", $dep) !== false){
		$loadAll = false;
		if(isset($_GET["loadAll"])){
			$loadAll = true;
		}
		$dictionary["jams"] = RenderJams($config, $users, $games, $jams, $satisfaction, $loggedInUser, $loadAll);
	}
	if(array_search("RenderAllJams", $dep) !== false){
		$dictionary["jams"] = RenderJams($config, $users, $games, $jams, $satisfaction, $loggedInUser, true);
	}
	if(array_search("RenderGames", $dep) !== false){
		$dictionary["entries"] = RenderGames($users, $games, $jams);
	}
	if(array_search("RenderThemes", $dep) !== false){
		$dictionary["themes"] = RenderThemes($config, $themes, $loggedInUserThemeVotes, $themesByVoteDifference, $themesByPopularity, $loggedInUser);
	}
	if(array_search("RenderAssets", $dep) !== false){
		$dictionary["assets"] = RenderAssets($assets);
	}
	if(array_search("RenderPolls", $dep) !== false){
		$dictionary["polls"] = RenderPolls($polls, $loggedInUserPollVotes);
	}
	if(array_search("RenderCookies", $dep) !== false){
		$dictionary["cookies"] = RenderCookies($cookies);
	}
	if(array_search("RenderMessages", $dep) !== false){
		$dictionary["messages"] = RenderMessages($messages);
	}
	
	$dictionary["page"] = RenderPageSpecific($page, $config, $users, $games, $jams, $satisfaction, $loggedInUser, $assets, $cookies, $adminVotes, $loggedInUserAdminVotes, $nextSuggestedJamDateTime);
	
	if($loggedInUser !== false){
		if(array_search("RenderLoggedInUser", $dep) !== false){
			$dictionary["user"] = RenderLoggedInUser($config, $cookies, $users, $games, $jams, $adminVotes, $loggedInUserAdminVotes, $loggedInUser);
		}
	}
	
	StopTimer("Init - Render");
	StopTimer("Init");
}

?>