<?php

BeforeInit();	//Plugin hook
Init();
AfterInit();	//Plugin hook

//Initializes the site.
function Init(){
	global $dictionary, $config, $adminLog, $users, $jams, $games, $assets, $loggedInUser, $satisfaction, $adminVotes, $loggedInUserAdminVotes, $nextSuggestedJamDateTime, $nextJamTime, $themes;

	AddActionLog("Init");
	StartTimer("Init");

	$config = LoadConfig();
	$nextSuggestedJamDateTime = GetSuggestedNextJamDateTime($config);

    RedirectToHttpsIfRequired($config);

	$adminLog = LoadAdminLog();
	$users = LoadUsers();

	IsLoggedIn();	//Sets $loggedInUser

	$jams =  LoadJams();
	$games = LoadGames();

	$themes = LoadThemes($loggedInUser, $config);
	$nextScheduledJamTime = GetNextJamDateAndTime($jams);
	$nextSuggestedJamTime = GetSuggestedNextJamDateTime($config);
	CheckNextJamSchedule($nextScheduledJamTime , $nextSuggestedJamTime);
	$assets = LoadAssets();
	LoadPolls();
    $satisfaction = LoadSatisfaction($config);
    $adminVotes = LoadAdminVotes();
	$loggedInUserAdminVotes = LoadLoggedInUsersAdminVotes($loggedInUser);
	InitStream();

	$dictionary["CONFIG"] = RenderConfig($config);
	$dictionary["adminlog"] = RenderAdminLog($adminLog);
	$dictionary["users"] = RenderUsers($users, $games, $jams, $config, $adminVotes, $loggedInUserAdminVotes);
	$dictionary["jams"] = RenderJams($jams, $config, $games, $users, $satisfaction, $loggedInUser);
	$dictionary["entries"] = RenderGames($games, $jams, $users);
	$dictionary["themes"] = RenderThemes($themes, $config);


	$dictionary["assets"] = RenderAssets($assets);

	StopTimer("Init");
}

?>