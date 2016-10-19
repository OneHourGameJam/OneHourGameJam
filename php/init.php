<?php

BeforeInit();	//Plugin hook
Init();
AfterInit();	//Plugin hook

//Initializes the site.
function Init(){
	LoadConfig();
	LoadUsers();
	LoadEntries();
	LoadThemes();
	LoadAssets();
	InitStream();
	GetNextJamDateAndTime();
}

?>