<?php

class PlatformPresenter{
	public static function RenderPlatform(&$platformModel){
		AddActionLog("RenderPlatform");
		StartTimer("RenderPlatform");
	
		$platformViewModel = new PlatformViewModel();
		
		$platformViewModel->id = $platformModel->Id;
		$platformViewModel->name = $platformModel->Name;
		$platformViewModel->icon_url = $platformModel->IconUrl;
		$platformViewModel->deleted = $platformModel->Deleted;

		StopTimer("RenderPlatform");
		return $platformViewModel;
	}
	
	public static function RenderPlatforms(&$platformData){
		AddActionLog("RenderPlatforms");
		StartTimer("RenderPlatforms");
		
		$platformsViewModel = new PlatformsViewModel();
	
		foreach($platformData->PlatformModels as $i => $platformModel){
			$platformsViewModel->LIST[] = PlatformPresenter::RenderPlatform($platformModel);
		}

		StopTimer("RenderPlatforms");
		return $platformsViewModel;
	}
}

?>