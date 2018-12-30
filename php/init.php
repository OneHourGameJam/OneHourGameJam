<?php

BeforeInit();	//Plugin hook
Init();
AfterInit();	//Plugin hook

//Initializes the site.
function Init(){
	LoadConfig();
    LoadAdminLog();
	LoadUsers();
	IsLoggedIn();	//Sets $loggedInUser
	LoadEntries();
	LoadThemes();
	CheckNextJamSchedule();
	LoadAssets();
	LoadPolls();
    LoadSatisfaction();
    LoadAdminVotes();
	LoadLoggedInUsersAdminVotes();
	//InitStream();
	GetNextJamDateAndTime();
}

?>