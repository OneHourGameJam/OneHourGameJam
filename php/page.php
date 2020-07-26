<?php

$templateBasePath = "template/";

function ValidatePage($page, &$loggedInUser){
    global $pageSettings;
	AddActionLog("ValidatePage");
	StartTimer("ValidatePage");

    if(!isset($pageSettings[$page])){
        StopTimer("ValidatePage");
        return "main";
    }

    if($pageSettings[$page]["authorization_level"] == "USER" && $loggedInUser === false){
        StopTimer("ValidatePage");
        return "main";
    }

    if($pageSettings[$page]["authorization_level"] == "ADMIN" && !IsAdmin($loggedInUser)){
        StopTimer("ValidatePage");
        return "main";
    }

	StopTimer("ValidatePage");
    return $page;
}

function RenderPageSpecific($page, &$configData, &$userData, &$gameData, &$jamData, &$themeData, &$themeIdeasData, &$platformData, &$platformGameData, &$pollData, &$satisfactionData, &$loggedInUser, &$assetData, &$cookieData, &$adminVoteData, &$nextSuggestedJamDateTime, &$adminLogData){
    global $_GET, $templateBasePath, $pageSettings;
	AddActionLog("RenderPageSpecific");
	StartTimer("RenderPageSpecific");

    $render = Array();
    
    //$render["CURRENT_TIME"] = gmdate("d M Y H:i", time());
    $render["page_title"] = $pageSettings[$page]["page_title"];
    $render["template_path"] = $templateBasePath;

    //Special processing for specific pages
    switch($page){
        case "edituser":
            if(IsAdmin($loggedInUser) !== false){
                $editingUserId = $_GET["user_id"];
                if(!isset($userData->UserModels[$editingUserId])){
                    die("no user selected");
                }
                $render["editinguser"] = UserPresenter::RenderUser($configData, $cookieData, $userData->UserModels[$editingUserId], $userData, $gameData, $jamData, $platformData, $platformGameData, $adminVoteData, RENDER_DEPTH_NONE);
            }
        break;
        case "editjam":
            if(IsAdmin($loggedInUser) !== false){
                $jamID = intval($_GET["jam_id"]);
                $jamFound = false;
                foreach($jamData->JamModels as $i => $jamModel){
                    if(intval($jamModel->Id) == $jamID){
                        $render["editingjam"] = JamPresenter::RenderJam($configData, $userData, $gameData, $jamModel, $jamData, $platformData, $platformGameData, $satisfactionData, $loggedInUser, 0, RENDER_DEPTH_JAMS);
                        $jamFound = true;
                        break;
                    }
                }
                if(!$jamFound){
                    die("no jam selected");
                }
                $editingJamDate = date("Y-m-d", strtotime($render["editingjam"]->date));
                $render["editingjam"]->html_startdate = $editingJamDate;
            }
        break;
        case "editasset":
            if(IsAdmin($loggedInUser) !== false){
                if(isset($_GET["asset_id"])){
                    $assetID = intval($_GET["asset_id"]);
                    $render["editingasset"] = ((isset($assetData->AssetModels[$assetID])) ? AssetPresenter::RenderAsset($assetData->AssetModels[$assetID], $userData) : Array());
                }
            }
        break;
        case "editentry":
            if(IsAdmin($loggedInUser) !== false){
                $entryID = intval($_GET["entry_id"]);
                $render["editingentry"] = Array();
                foreach($gameData->GameModels as $i => $gameModel){
                    if($gameModel->Id == $entryID){
                        $render["editingentry"] = GamePresenter::RenderGame($userData, $gameModel, $jamData, $platformData, $platformGameData, RENDER_DEPTH_GAMES);
                        break;
                    }
                }
                if(count($render["editingentry"]) == 0){
                    die("no entry selected");
                }
            }
        break;
        case "jam":
            $viewingJamNumber = ((isset($_GET["jam"])) ? intval($_GET["jam"]) : 0);
            if($viewingJamNumber == 0){
                die("invalid jam number");
            }

            $pass = FALSE;
            foreach($jamData->JamModels as $i => $jamModel){
                if($jamModel->JamNumber != $viewingJamNumber){
                    continue;
                }

                if($jamModel->Deleted == 1){
                    continue;
                }

                $render["viewing_jam"] = JamPresenter::RenderJam($configData, $userData, $gameData, $jamModel, $jamData, $platformData, $platformGameData, $satisfactionData, $loggedInUser, 0, RENDER_DEPTH_JAMS_GAMES);
                $pass = TRUE;
                break;
            }

            if($pass == FALSE){
                die("jam does not exist");
            }

            $render["page_title"] = "Jam #" . $viewingJamNumber . ": ".$render["viewing_jam"]->theme;
        break;
        case "author":
            $viewingAuthor = ((isset($_GET["author"])) ? ("".$_GET["author"]) : "");
            if($viewingAuthor == ""){
                die("invalid author name");
            }

            $viewingAuthorId = $userData->UsernameToId[$viewingAuthor];

            $render['show_edit_link'] = $viewingAuthor == $loggedInUser->Username;
            if($viewingAuthorId != null){
                $render["author_bio"] = $userData->LoadBio($viewingAuthorId);
            }
            $render["viewing_author"] = UserPresenter::RenderUser($configData, $cookieData, $userData->UserModels[$viewingAuthorId], $userData, $gameData, $jamData, $platformData, $platformGameData, $adminVoteData, RENDER_DEPTH_USERS_GAMES);
            $render["page_title"] = $viewingAuthor;
        break;
        case "submit":
            $currentJam = GetCurrentJamNumberAndID();
            if($currentJam["NUMBER"] == 0){
                die("no jam to submit to");
            }
            $jamNumber = (isset($_GET["jam_number"])) ? intval($_GET["jam_number"]) : $currentJam["NUMBER"];
            $jamModel = $jamData->GetJamByNumber($jamNumber);
            if (!$jamModel) {
                die('jam not found');
            }

            $render["submit_jam"] = JamPresenter::RenderSubmitJam($configData, $userData, $gameData, $jamModel, $jamData, $platformData, $platformGameData, $satisfactionData, $loggedInUser, RENDER_DEPTH_JAMS);
            $colorNumber = rand(0, count($jamModel->Colors) - 1);
            $render["user_entry_background_color"] = $jamModel->Colors[$colorNumber];
            $render["user_entry_text_color"] = "#000000";

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

            foreach($gameData->GameModels as $i => $gameModel){
                if($gameModel->AuthorUserId != $loggedInUser->Id){
                    continue;
                }

                if($gameModel->JamNumber != $jamNumber){
                    continue;
                }

                if($gameModel->Deleted == 1){
                    continue;
                }

                $render["user_entry_background_color"] = $gameModel->BackgroundColor;
                $render["user_entry_text_color"] = $gameModel->TextColor;

                $render["user_submitted_to_this_jam"] = true;
                $render["user_entry_name"] = $gameModel->Title;
                if($gameModel->UrlScreenshot != "logo.png"){
                    $render["user_entry_screenshot"] = $gameModel->UrlScreenshot;
                }
            
                foreach($platformGameData->GameIdToPlatformGameIds[$gameModel->Id] as $i => $platformGameId){
                    $platformGameModel = $platformGameData->PlatformGameModels[$platformGameId];
                    $platformId = $platformGameModel->PlatformId;
                    $url = $platformGameModel->Url;
                    
                    $platforms[$platformId]["platform_entry_url"] = $url;
                }
                
                $render["user_entry_desc"] = $gameModel->Description;
                break;
            }

            foreach($platforms as $i => $platform){
                $render["platforms"][] = $platform;
            }

            if (!isset($render["user_submitted_to_this_jam"]) && $jamNumber != $currentJam["NUMBER"]) {
                die('Cannot make a new submission to a past jam');
            }
        break;
        case "userdata":
            $render["userdata_assets"] = $assetData->GetAssetsOfUserFormatted($loggedInUser->Id);
            $render["userdata_entries"] = $gameData->GetEntriesOfUserFormatted($loggedInUser->Id);
            $render["userdata_poll_votes"] = $pollData->GetPollVotesOfUserFormatted($loggedInUser->Id);
            $render["userdata_themes"] = $themeData->GetThemesOfUserFormatted($loggedInUser->Id);
            $render["userdata_theme_votes"] = $themeData->GetThemeVotesOfUserFormatted($loggedInUser->Id);
            $render["userdata_theme_ideas"] = $themeIdeasData->GetThemeIdeasOfUserFormatted($loggedInUser->Id);
            $render["userdata_users"] = $userData->GetUsersOfUserFormatted($loggedInUser->Id);
            $render["userdata_jams"] = $jamData->GetJamsOfUserFormatted($loggedInUser->Id);
            $render["userdata_satisfaction"] = $satisfactionData->GetSatisfactionVotesOfUserFormatted($loggedInUser->Id);
            $render["userdata_sessions"] = $userData->GetSessionsOfUserFormatted($loggedInUser->Id);
            $render["userdata_adminlog_admin"] = $adminLogData->GetAdminLogForAdminFormatted($loggedInUser->Id);
            $render["userdata_adminlog_subject"] = $adminLogData->GetAdminLogForSubjectFormatted($loggedInUser->Id);
            $render["userdata_admin_vote_voter"] = $adminVoteData->GetAdminVotesCastByUserFormatted($loggedInUser->Id);
            $render["userdata_admin_vote_subject"] = $adminVoteData->GetAdminVotesForSubjectUserFormatted($loggedInUser->Id);
        break;
        case "newjam":
            $render["next_jam_suggested_date"] = gmdate("Y-m-d", $nextSuggestedJamDateTime);
            $render["next_jam_suggested_time"] = gmdate("H:i", $nextSuggestedJamDateTime);
        break;
        case "usersettings":
            $render["user_bio"] = $userData->LoadBio($loggedInUser->Id);
        break;
        case "entries":
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
            
            foreach($platforms as $i => $platform){
                $render["platforms"][] = $platform;
            }
        break;
    }

    StopTimer("RenderPageSpecific");
    return $render;
}


?>