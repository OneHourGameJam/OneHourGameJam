<?php

BeforeInit();	//Plugin hook
Init();
AfterInit();	//Plugin hook

//Initializes the site.
function Init(){
	global $dictionary, $config, $adminLog, $users, $jams, $games, $assets, $loggedInUser, $satisfaction, $adminVotes, $loggedInUserAdminVotes, $nextSuggestedJamDateTime, $nextJamTime, $themes, $loggedInUserThemeVotes, $themesByVoteDifference, $themesByPopularity, $polls, $loggedInUserPollVotes;

	AddActionLog("Init");
	StartTimer("Init");

	$config = LoadConfig();
	$nextSuggestedJamDateTime = GetSuggestedNextJamDateTime($config);

    RedirectToHttpsIfRequired($config);

	$adminLog = LoadAdminLog();
	$users = LoadUsers();

	$loggedInUser = IsLoggedIn($users, $config);

	$jams =  LoadJams();
	$games = LoadGames();

	$themes = LoadThemes();
	$loggedInUserThemeVotes = LoadUserThemeVotes($loggedInUser);
	$themesByVoteDifference = CalculateThemeSelectionProbabilityByVoteDifference($themes, $config);
	$themesByPopularity = CalculateThemeSelectionProbabilityByPopularity($themes, $config);

	$nextScheduledJamTime = GetNextJamDateAndTime($jams);
	$nextSuggestedJamTime = GetSuggestedNextJamDateTime($config);
	CheckNextJamSchedule($themes, $jams, $config, $nextScheduledJamTime , $nextSuggestedJamTime);
	$assets = LoadAssets();
	$polls = LoadPolls();
	$loggedInUserPollVotes = LoadLoggedInUserPollVotes($loggedInUser);
    $satisfaction = LoadSatisfaction($config);
    $adminVotes = LoadAdminVotes();
	$loggedInUserAdminVotes = LoadLoggedInUsersAdminVotes($loggedInUser);
	InitStream();

	$dictionary["CONFIG"] = RenderConfig($config);
	$dictionary["adminlog"] = RenderAdminLog($adminLog);
	$dictionary["users"] = RenderUsers($users, $games, $jams, $config, $adminVotes, $loggedInUserAdminVotes);
	$dictionary["jams"] = RenderJams($jams, $config, $games, $users, $satisfaction, $loggedInUser);
	$dictionary["entries"] = RenderGames($games, $jams, $users);
	$dictionary["themes"] = RenderThemes($themes, $loggedInUserThemeVotes, $themesByVoteDifference, $themesByPopularity, $loggedInUser, $config);
	$dictionary["assets"] = RenderAssets($assets);
	$dictionary["polls"] = RenderPolls($polls, $loggedInUserPollVotes);
	
	if($loggedInUser !== false){
		$dictionary["user"] = RenderUser($loggedInUser, $users, $games, $jams, $config, $adminVotes, $loggedInUserAdminVotes);
	}

	StopTimer("Init");
}

?>