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
		
		//Redirect to new URL if viewing from old one
		$url = strtolower($_SERVER['HTTP_HOST']);
		$reqURI = $_SERVER["REQUEST_URI"];
		if($url == "weekjam.com" || $url == "www.weekjam.com"){
			header("HTTP/1.1 301 Moved Permanently"); 
			header("Location: http://onehourgamejam.com".$reqURI);
		}
		
	}
	
	//Called in site.php, as soon as Init() concludes. 
	function AfterInit(){
		
		//Bot actions
		if(isset($_GET["theme"]) && isset($_GET["password"])){
			$theme = $_GET["theme"];
			$pw = $_GET["password"];
			if($pw == "DvyhsjRBdr218EMJJKdE"){
				AddTheme($theme, true);
			}
		}
		if(isset($_POST["theme"]) && isset($_POST["password"])){
			$theme = $_POST["theme"];
			$pw = $_POST["password"];
			if($pw == "DvyhsjRBdr218EMJJKdE"){
				AddTheme($theme, true);
			}
		}
		
	}
	
	//Called at the end of the <head> tag, intended to contain Analytics code.
	function GetAnalyticsCode(){
		global $config;
		if(isset($config["GOOGLE_ANALYTICS_CODE"]) && $config["GOOGLE_ANALYTICS_CODE"] != ""){
			return "
				<script>
					(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
					(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
					m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
					})(window,document,'script','//www.google-analytics.com/analytics.js','ga');
	
					ga('create', '".$config["GOOGLE_ANALYTICS_CODE"]."', 'auto');
					ga('send', 'pageview');
	
				</script>
			";
		}
	}
?>