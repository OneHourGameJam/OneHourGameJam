<?php

class CookieController{

	public static function UpdateCookies(){
		global $_COOKIE, $_GET, $_POST;
		AddActionLog("UpdateCookies");
		StartTimer("UpdateCookies");
		
		//Determine if the user is in dark mode
		if(isset($_GET[GET_DARK_MODE])){
			if($_GET[GET_DARK_MODE]){
				setcookie(COOKIE_DARKMODE, 1, time() + (60 * 60 * 24 * 365));
				$_COOKIE[COOKIE_DARKMODE] = 1;
			}else{
				setcookie(COOKIE_DARKMODE, null, -1);
				unset($_COOKIE[COOKIE_DARKMODE]);
			}
		}
	
		//Update streaming cookie
		if(isset($_GET[GET_STREAMING_MODE])){
			if($_GET[GET_STREAMING_MODE] == 1){
				setcookie(COOKIE_STREAMING, 1, time() + (60 * 60 * 3));	//Streamer mode lasts for 3 hours
				$_COOKIE[COOKIE_STREAMING] = 1;
			}else{
				setcookie(COOKIE_STREAMING, null, -1);
				unset($_COOKIE[COOKIE_STREAMING]);
			}
		}
	
		//Update cookie notification cookie
		if(isset($_POST[FORM_COOKIENOTICE_ACCEPT])){
			setcookie(COOKIE_COOKIENOTICE, 1, time() + (60 * 60 * 24 * 365));
			echo "<meta http-equiv='refresh' content='0'>";
		}
		if((isset($_GET[GET_STREAMING_MODE]) || isset($_GET[GET_DARK_MODE])) && !isset($_COOKIE[COOKIE_COOKIENOTICE])){
			setcookie(COOKIE_COOKIENOTICE, 0, time() + (60 * 60 * 24 * 365));
		}
	
		StopTimer("UpdateCookies");
	}
	
}

?>
