<?php

	//A file intended for custom code. This is loaded at the very beginning, 
	//meaning no global variables are set at this point. 
	//The purpose of this file is to have custom code here, so you can sync the rest of
	//the software as new versions come out without ruining all your custom code. 
	//Of course since this is rather simplistic at this point, the whole way plugins
	//work might change in the future, but hey! :D
	
	//The software includes various hooks you can tap into and calls them at various points.
	//At this point, as new hooks get added, it will require you to manually sync this file
	//if you have added custom code. Sorry about that.

	//Called in site.php, before Init() is called. 
	function BeforeInit(){
		
	}
	
	//Called in site.php, as soon as Init() concludes. 
	function AfterInit(){
		
	}
	
?>