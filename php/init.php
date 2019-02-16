<?php

BeforeInit();	//Plugin hook
Init($page);
AfterInit();	//Plugin hook

//Initializes the site.
function Init($page){
	global $dictionary, $config, $adminLog, $users, $jams, $games, $assets, $loggedInUser, $satisfaction, $adminVotes, $loggedInUserAdminVotes, $nextSuggestedJamDateTime, $nextJamTime, $themes, $loggedInUserThemeVotes, $themesByVoteDifference, $themesByPopularity, $polls, $loggedInUserPollVotes, $cookies, $actions;
	AddActionLog("Init");
	StartTimer("Init");

	UpdateCookies();
	$cookies = LoadCookies();

	$config = LoadConfig();
	$nextSuggestedJamDateTime = GetSuggestedNextJamDateTime($config);

    RedirectToHttpsIfRequired($config);

	$adminLog = LoadAdminLog();
	$users = LoadUsers();

	$loggedInUser = IsLoggedIn($config, $users);

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

	PerformPendingSiteAction($config, $actions, $loggedInUser);
 
	$dictionary["stream"] = InitStream();
	$dictionary["CONFIG"] = RenderConfig($config);
	$dictionary["adminlog"] = RenderAdminLog($adminLog);
	$dictionary["users"] = RenderUsers($config, $cookies, $users, $games, $jams, $adminVotes, $loggedInUserAdminVotes);
	$dictionary["jams"] = RenderJams($config, $users, $games, $jams, $satisfaction, $loggedInUser);
	$dictionary["entries"] = RenderGames($users, $games, $jams);
	$dictionary["themes"] = RenderThemes($config, $themes, $loggedInUserThemeVotes, $themesByVoteDifference, $themesByPopularity, $loggedInUser);
	$dictionary["assets"] = RenderAssets($assets);
	$dictionary["polls"] = RenderPolls($polls, $loggedInUserPollVotes);
	$dictionary["cookies"] = RenderCookies($cookies);
	$dictionary["page"] = RenderPageSpecific($page, $config, $users, $games, $jams, $satisfaction, $loggedInUser, $assets, $cookies, $adminVotes, $loggedInUserAdminVotes, $nextSuggestedJamDateTime);
	$dictionary["messages"] = RenderMessages($messages);
	
	if($loggedInUser !== false){
		$dictionary["user"] = RenderLoggedInUser($config, $cookies, $users, $games, $jams, $adminVotes, $loggedInUserAdminVotes, $loggedInUser);
	}

	StopTimer("Init");
}

?>