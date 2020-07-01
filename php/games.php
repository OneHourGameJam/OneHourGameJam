<?php

function RenderGames(&$userData, &$gameData, &$jamData, $renderDepth){
	AddActionLog("RenderGames");
	StartTimer("RenderGames");

	$render = Array("LIST" => Array());
    $nonDeletedGamesCounter = 0;
	foreach($gameData->GameModels as $i => $gameModel){
		if(($renderDepth & RENDER_DEPTH_GAMES) > 0){
			$render["LIST"][] = RenderGame($userData, $gameModel, $jamData, $renderDepth);
		}
        if($gameModel->Deleted != 1){
            $nonDeletedGamesCounter += 1;
        }
    }
    $render["all_entries_count"] = $nonDeletedGamesCounter;

	StopTimer("RenderGames");
	return $render;
}

function RenderGame(&$userData, &$game, &$jamData, $renderDepth){
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
	$render["url"] = str_replace("'", "\\'", $game->Url);
	$render["url_web"] = str_replace("'", "\\'", $game->UrlWeb);
	$render["url_windows"] = str_replace("'", "\\'", $game->UrlWindows);
	$render["url_mac"] = str_replace("'", "\\'", $game->UrlMac);
	$render["url_linux"] = str_replace("'", "\\'", $game->UrlLinux);
	$render["url_ios"] = str_replace("'", "\\'", $game->UrliOs);
	$render["url_android"] = str_replace("'", "\\'", $game->UrlAndroid);
	$render["url_source"] = str_replace("'", "\\'", $game->UrlSource);
	$render["screenshot_url"] = str_replace("'", "\\'", $game->UrlScreenshot);
	$render["entry_deleted"] = $game->Deleted;
	$render["title_url_encoded"] = urlencode($title);

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
	$authorUsername = "";
	$authorDisplayName = "";
	foreach($userData->UserModels as $i => $userModel){
		if($userModel->Id == $authorUserId){
			$authorUsername = $userModel->Username;
			$authorDisplayName = $userModel->DisplayName;
		}
	}
	$render["author_username"] = $authorUsername;
	$render["author_username_url_encoded"] = urlencode($authorUsername);
	$render["author_display_name"] = $authorDisplayName;

	$render["has_url"] = ($game->Url != "") ? 1 : 0;
	$render["has_url_web"] = ($game->UrlWeb != "") ? 1 : 0;
	$render["has_url_windows"] = ($game->UrlWindows != "") ? 1 : 0;
	$render["has_url_mac"] = ($game->UrlMac != "") ? 1 : 0;
	$render["has_url_linux"] = ($game->UrlLinux != "") ? 1 : 0;
	$render["has_url_ios"] = ($game->UrliOs != "") ? 1 : 0;
	$render["has_url_android"] = ($game->UrlAndroid != "") ? 1 : 0;
	$render["has_url_source"] = ($game->UrlSource != "") ? 1 : 0;

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