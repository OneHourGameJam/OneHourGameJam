<?php

class CookiePresenter{
	public static function RenderCookies(&$cookieData){
		global $_GET;
		AddActionLog("RenderCookies");
		StartTimer("RenderCookies");
		
		$cookieViewModel = new CookieViewModel();
	
		$cookieViewModel->is_streamer = $cookieData->CookieModel->IsStreamer;
		$cookieViewModel->darkmode = $cookieData->CookieModel->DarkMode;
	
		if ($cookieData->CookieModel->CookieNotice != -1){
			$cookieViewModel->show_cookie_notice = !$cookieData->CookieModel->CookieNotice;
		} else if(isset($_GET[COOKIE_STREAMING]) || isset($_GET[COOKIE_DARKMODE])){
			$cookieViewModel->show_cookie_notice = 1;
		} else{
			$cookieViewModel->show_cookie_notice = 0;
		}
	
		StopTimer("RenderCookies");
		return $cookieViewModel;
	}
}

?>