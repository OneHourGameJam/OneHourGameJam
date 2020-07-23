<?php

class GamePresenter{
	public static function RenderGames(&$userData, &$gameData, &$jamData, &$platformData, &$platformGameData, $renderDepth){
		AddActionLog("RenderGames");
		StartTimer("RenderGames");
	
		$gamesViewModel = new GamesViewModel();
		$nonDeletedGamesCounter = 0;
		foreach($gameData->GameModels as $i => $gameModel){
			if(($renderDepth & RENDER_DEPTH_GAMES) > 0){
				$gamesViewModel->LIST[] = GamePresenter::RenderGame($userData, $gameModel, $jamData, $platformData, $platformGameData, $renderDepth);
			}
			if($gameModel->Deleted != 1){
				$nonDeletedGamesCounter += 1;
			}
		}
		$gamesViewModel->all_entries_count = $nonDeletedGamesCounter;
	
		StopTimer("RenderGames");
		return $gamesViewModel;
	}
	
	public static function RenderGame(&$userData, &$gameModel, &$jamData, &$platformData, &$platformGameData, $renderDepth){
		AddActionLog("RenderGame");
		StartTimer("RenderGame");
		
		$jamId = intval($gameModel->JamId);
		$title = $gameModel->Title;
	
		$gameViewModel = new GameViewModel();
		$gameViewModel->id = $gameModel->Id;
		$gameViewModel->jam_id = $jamId;
		$gameViewModel->jam_number = intval($gameModel->JamNumber);
		$gameViewModel->title = $title;
		$gameViewModel->description = $gameModel->Description;
		$gameViewModel->author_user_id = $gameModel->AuthorUserId;
		$gameViewModel->screenshot_url = str_replace("'", "\\'", $gameModel->UrlScreenshot);
		$gameViewModel->entry_deleted = $gameModel->Deleted;
		$gameViewModel->title_url_encoded = urlencode($title);
	
		$platforms = Array();
		
		foreach($platformData->PlatformModels as $i => $platformModel){
			if($platformModel->Deleted != 0){
				continue;
			}
			
			$platformGameViewModel = new PlatformGameViewModel();
			
			$platformGameViewModel->platform_id = $platformModel->Id;
			$platformGameViewModel->platform_name = $platformModel->Name;
			$platformGameViewModel->platform_icon_url = $platformModel->IconUrl;
			
			$platforms[$platformModel->Id] = $platformGameViewModel;
		}
		
		foreach($platformGameData->GameIdToPlatformGameIds[$gameModel->Id] as $i => $platformGameId){
			$platformGameModel = $platformGameData->PlatformGameModels[$platformGameId];
			$platformId = $platformGameModel->PlatformId;
			$url = $platformGameModel->Url;
			
			$platforms[$platformId]->url = $url;
			$platforms[$platformId]->platform_game_id = $platformGameModel->Id;
		}
		
		foreach($platforms as $i => $platform){
			$gameViewModel->platforms[] = $platform;
		}
	
		//Entry color
		$color = $gameModel->Color;
		$color256_red = hexdec(substr($color, 0, 2));
		$color256_green = hexdec(substr($color, 2, 2));
		$color256_blue = hexdec(substr($color, 4, 2));
		$lighter_color256_red = ($color256_red + 0xFF) / 2;
		$lighter_color256_green = ($color256_green + 0xFF) / 2;
		$lighter_color256_blue =($color256_blue + 0xFF) / 2;
		$lighter_color = str_pad(dechex($lighter_color256_red), 2, "0", STR_PAD_LEFT) . str_pad(dechex($lighter_color256_green), 2, "0", STR_PAD_LEFT) . str_pad(dechex($lighter_color256_blue), 2, "0", STR_PAD_LEFT);
		$border_color256_red = ($color256_red + 0xDD) / 2;
		$border_color256_green = ($color256_green + 0xDD) / 2;
		$border_color256_blue =($color256_blue + 0xDD) / 2;
		$border_color = str_pad(dechex($border_color256_red), 2, "0", STR_PAD_LEFT) . str_pad(dechex($border_color256_green), 2, "0", STR_PAD_LEFT) . str_pad(dechex($border_color256_blue), 2, "0", STR_PAD_LEFT);
		
		$gameViewModel->color = "#".$color;
		$gameViewModel->color256_red = $color256_red;
		$gameViewModel->color256_green = $color256_green;
		$gameViewModel->color256_blue = $color256_blue;
		$gameViewModel->color_lighter = "#".$lighter_color;
		$gameViewModel->color_border = "#".$border_color;
	
		//Mini RenderJam()
		$jamModel = $jamData->JamModels[$jamId];
		$gameViewModel->jam_number = $jamModel->JamNumber;
		$gameViewModel->jam_theme = $jamModel->Theme;
	
		//Mini RenderUser()
		$authorUserId = $gameModel->AuthorUserId;
		$authorUsername = $userData->UserModels[$authorUserId]->Username;
		$authorDisplayName = $userData->UserModels[$authorUserId]->DisplayName;
		$gameViewModel->author_username = $authorUsername;
		$gameViewModel->author_username_url_encoded = urlencode($authorUsername);
		$gameViewModel->author_display_name = $authorDisplayName;
	
		if($gameViewModel->screenshot_url != "logo.png" &&
		   $gameViewModel->screenshot_url != ""){
			$gameViewModel->has_screenshot = 1;
		}
	
		if(trim($gameViewModel->title) != ""){
			$gameViewModel->has_title = 1;
		}
	
		if(trim($gameViewModel->description) != ""){
			$gameViewModel->has_description = 1;
		}
	
		StopTimer("RenderGame");
		return $gameViewModel;
	}
}

?>