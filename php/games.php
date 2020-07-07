<?php

function RenderGames(&$userData, &$gameData, &$jamData, &$platformData, &$platformGameData, $renderDepth){
	AddActionLog("RenderGames");
	StartTimer("RenderGames");

	$render = Array("LIST" => Array());
    $nonDeletedGamesCounter = 0;
	foreach($gameData->GameModels as $i => $gameModel){
		if(($renderDepth & RENDER_DEPTH_GAMES) > 0){
			$render["LIST"][] = RenderGame($userData, $gameModel, $jamData, $platformData, $platformGameData, $renderDepth);
		}
        if($gameModel->Deleted != 1){
            $nonDeletedGamesCounter += 1;
        }
    }
    $render["all_entries_count"] = $nonDeletedGamesCounter;

	StopTimer("RenderGames");
	return $render;
}

function RenderGame(&$userData, &$game, &$jamData, &$platformData, &$platformGameData, $renderDepth){
	AddActionLog("RenderGame");
	StartTimer("RenderGame");
	
	$jamId = intval($game->JamId);
	$title = $game->Title;

	$render = Array();
	$render["id"] = $game->Id;
	$render["jam_id"] = $jamId;
	$render["jam_number"] = intval($game->JamNumber);
	$render["title"] = $title;
	$render["description"] = $game->Description;
	$render["author_user_id"] = $game->AuthorUserId;
	$render["screenshot_url"] = str_replace("'", "\\'", $game->UrlScreenshot);
	$render["entry_deleted"] = $game->Deleted;
	$render["title_url_encoded"] = urlencode($title);

	$platforms = Array();
	
	foreach($platformData->PlatformModels as $i => $platformModel){
		if($platformModel->Deleted != 0){
			continue;
		}
		
		$platformRender = Array();
		
		$platformRender["platform_id"] = $platformModel->Id;
		$platformRender["platform_name"] = $platformModel->Name;
		$platformRender["platform_icon_url"] = $platformModel->IconUrl;
		
		$platforms[$platformModel->Id] = $platformRender;
	}
	
	foreach($platformGameData->GameIdToPlatformGameIds[$game->Id] as $i => $platformGameId){
		$platformGameModel = $platformGameData->PlatformGameModels[$platformGameId];
		$platformId = $platformGameModel->PlatformId;
		$url = $platformGameModel->Url;
		
		$platforms[$platformId]["url"] = $url;
		$platforms[$platformId]["platform_game_id"] = $platformGameModel->Id;
		$platforms[$platformId]["platform_name"] = $platformData->PlatformModels[$platformId]->Name;
	}
	
	foreach($platforms as $i => $platform){
		$render["platforms"][] = $platform;
	}

	//Entry color
	$render["color"] = "#".$game->Color;
	$render["color256_red"] = hexdec(substr($game->Color, 0, 2));
	$render["color256_green"] = hexdec(substr($game->Color, 2, 2));
	$render["color256_blue"] = hexdec(substr($game->Color, 4, 2));
	$render["color_lighter"] = "#".str_pad(dechex( ($render["color256_red"] + 255) / 2 ), 2, "0", STR_PAD_LEFT).str_pad(dechex( ($render["color256_green"] + 255) / 2 ), 2, "0", STR_PAD_LEFT).str_pad(dechex( ($render["color256_blue"] + 255) / 2 ), 2, "0", STR_PAD_LEFT);
	$render["color_non_white"] = "#".str_pad(dechex(min($render["color256_red"], 0xDD)), 2, "0", STR_PAD_LEFT).str_pad(dechex(min($render["color256_green"], 0xDD)), 2, "0", STR_PAD_LEFT).str_pad(dechex(min($render["color256_blue"], 0xDD)), 2, "0", STR_PAD_LEFT);

	//Mini RenderJam()
	$jamModel = $jamData->JamModels[$jamId];
	$render["jam_number"] = $jamModel->JamNumber;
	$render["jam_theme"] = $jamModel->Theme;

	//Mini RenderUser()
	$authorUserId = $game->AuthorUserId;
	$authorUsername = $userData->UserModels[$authorUserId]->Username;
	$authorDisplayName = $userData->UserModels[$authorUserId]->DisplayName;
	$render["author_username"] = $authorUsername;
	$render["author_username_url_encoded"] = urlencode($authorUsername);
	$render["author_display_name"] = $authorDisplayName;

	if($render["screenshot_url"] != "logo.png" &&
	   $render["screenshot_url"] != ""){
		$render["has_screenshot"] = 1;
	}

	if(trim($render["title"]) != ""){
		$render["has_title"] = 1;
	}

	if(trim($render["description"]) != ""){
		$render["has_description"] = 1;
	}

	StopTimer("RenderGame");
	return $render;
}

?>