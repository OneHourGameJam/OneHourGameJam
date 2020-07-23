<?php

class CookieController{

	public static function UpdateCookies(){
		global $_COOKIE, $_GET, $_POST;
		AddActionLog("UpdateCookies");
		StartTimer("UpdateCookies");
		
		//Determine if the user is in dark mode
		if(isset($_GET["darkmode"])){
			if($_GET["darkmode"]){
				setcookie("darkmode", 1, time() + (60 * 60 * 24 * 365));
				$_COOKIE["darkmode"] = 1;
			}else{
				setcookie("darkmode", null, -1);
				unset($_COOKIE["darkmode"]);
			}
		}
	
		//Update streaming cookie
		if(isset($_GET["streaming"])){
			if($_GET["streaming"] == 1){
				setcookie("streaming", 1, time() + (60 * 60 * 3));	//Streamer mode lasts for 3 hours
				$_COOKIE["streaming"] = 1;
			}else{
				setcookie("streaming", null, -1);
				unset($_COOKIE["streaming"]);
			}
		}
	
		//Update cookie notification cookie
		if(isset($_POST["cookienotice-accepted"])){
			setcookie("cookienotice", 1, time() + (60 * 60 * 24 * 365));
			echo "<meta http-equiv='refresh' content='0'>";
		}
		if((isset($_GET["streaming"]) || isset($_GET["darkmode"])) && !isset($_COOKIE["cookienotice"])){
			setcookie("cookienotice", 0, time() + (60 * 60 * 24 * 365));
		}
	
		StopTimer("UpdateCookies");
	}
	
}

?>
