<?php

function ValidatePage($page, &$loggedInUser){
    global $pageSettings;
	AddActionLog("ValidatePage");
	StartTimer("ValidatePage");

    if(!isset($pageSettings[$page])){
        StopTimer("ValidatePage");
        return PAGE_MAIN;
    }

    if($pageSettings[$page]["authorization_level"] == AUTHORIZATION_LEVEL_USER && $loggedInUser === false){
        StopTimer("ValidatePage");
        return PAGE_MAIN;
    }

    if($pageSettings[$page]["authorization_level"] == AUTHORIZATION_LEVEL_ADMIN && !IsAdmin($loggedInUser)){
        StopTimer("ValidatePage");
        return PAGE_MAIN;
    }

	StopTimer("ValidatePage");
    return $page;
}

function RenderPageSpecific($page, ConfigData &$configData, UserData &$userData, GameData &$gameData, JamData &$jamData, ThemeData &$themeData, ThemeIdeaData &$themeIdeaData, PlatformData &$platformData,
                            PlatformGameData &$platformGameData, PollData &$pollData, SatisfactionData &$satisfactionData, &$loggedInUser, AssetData &$assetData, CookieData &$cookieData,
                            AdminVoteData &$adminVoteData, &$nextSuggestedJamDateTime, &$plugins){
    global $_GET, $templateBasePath, $pageSettings;
	AddActionLog("RenderPageSpecific");
	StartTimer("RenderPageSpecific");

    $render = Array();
    
    //$render["CURRENT_TIME"] = gmdate("d M Y H:i", time());
    $render["page_title"] = $pageSettings[$page]["page_title"];
    $render["template_path"] = $templateBasePath;

    $render["pages"] = Array();
    foreach($pageSettings as $pageName => $pageSetting){
        $render["pages"][] = Array("page_id" => $pageName, "page_title" => $pageSetting["page_title"]);
    }

    //Special processing for specific pages
    switch($page){
        case PAGE_EDIT_USER:
            if(IsAdmin($loggedInUser) !== false){
                $editingUserId = $_GET[GET_EDITUSER_USER_ID];
                if(!isset($userData->UserModels[$editingUserId])){
                    die("no user selected");
                }
                $render["editinguser"] = UserPresenter::RenderUser($configData, $cookieData, $userData->UserModels[$editingUserId], $userData, $gameData, $jamData, $platformData, $platformGameData, $adminVoteData, $loggedInUser, RENDER_DEPTH_NONE);
                $render["user_bio"] = $userData->LoadBio($editingUserId);
            }
        break;
        case PAGE_EDIT_JAM:
            if(IsAdmin($loggedInUser) !== false){
                $jamID = intval($_GET[GET_EDITJAM_JAM_Id]);
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
        case PAGE_EDIT_ASSET:
            if(IsAdmin($loggedInUser) !== false){
                if(isset($_GET[GET_EDITASSET_ASSET_ID])){
                    $assetID = intval($_GET[GET_EDITASSET_ASSET_ID]);
                    $render["editingasset"] = ((isset($assetData->AssetModels[$assetID])) ? AssetPresenter::RenderAsset($assetData->AssetModels[$assetID], $userData) : Array());
                }
            }
        break;
        case PAGE_EDIT_ENTRY:
            if(IsAdmin($loggedInUser) !== false){
                $entryID = intval($_GET[GET_EDITENTRY_ENTRY_ID]);
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
        case PAGE_MANAGE_THEMES:
            if(isset($_GET[GET_LOAD_ALL])){
                if(IsAdmin($loggedInUser) !== false){
                    $themeData->AllThemeModels = $themeData->LoadAllThemes();
                    $render["all_themes"] = ThemePresenter::RenderAllThemes($configData, $userData, $themeData);
                    $render["loading_all_themes"] = 1;
                }
            }
            break;
        case PAGE_JAM:
            $viewingJamNumber = ((isset($_GET[GET_JAM_JAM_NUMBER])) ? intval($_GET[GET_JAM_JAM_NUMBER]) : 0);
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
        case PAGE_AUTHOR:
            $viewingAuthor = ((isset($_GET[GET_AUTHOR_USERNAME])) ? ("".$_GET[GET_AUTHOR_USERNAME]) : "");
            if($viewingAuthor == ""){
                die("invalid author name");
            }

            $viewingAuthorId = $userData->UsernameToId[$viewingAuthor];

            $render['show_edit_link'] = $viewingAuthor == $loggedInUser->Username;
            if($viewingAuthorId != null){
                $render["author_bio"] = $userData->LoadBio($viewingAuthorId);
            }
            $render["viewing_author"] = UserPresenter::RenderUser($configData, $cookieData, $userData->UserModels[$viewingAuthorId], $userData, $gameData, $jamData, $platformData, $platformGameData, $adminVoteData, $loggedInUser, RENDER_DEPTH_USERS_GAMES);
            $render["page_title"] = $viewingAuthor;
        break;
        case PAGE_SUBMIT:
            $currentJam = GetCurrentJamNumberAndId();
            if($currentJam["NUMBER"] == 0){
                die("no jam to submit to");
            }
            $jamNumber = (isset($_GET[GET_SUBMIT_JAM_NUMBER])) ? intval($_GET[GET_SUBMIT_JAM_NUMBER]) : $currentJam["NUMBER"];
            $jamModel = $jamData->GetJamByNumber($jamNumber);
            if (!$jamModel) {
                die("jam not found");
            }
            if (strtotime($jamModel->StartTime . " UTC") > time()) {
                die("jam not yet started");
            }

            $render["submit_jam"] = JamPresenter::RenderSubmitJam($configData, $userData, $gameData, $jamModel, $jamData, $platformData, $platformGameData, $satisfactionData, $loggedInUser, RENDER_DEPTH_JAMS);
            $colorNumber = rand(0, count($jamModel->Colors) - 1);
            $render["user_entry_background_color"] = $jamModel->Colors[$colorNumber];
            $render["user_entry_text_color"] = "000000";

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
                if($gameModel->UrlScreenshot){
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

            if($configData->ConfigModels[CONFIG_CAN_SUBMIT_TO_PAST_JAMS]->Value == 0){
                if (!isset($render["user_submitted_to_this_jam"]) && $jamNumber != $currentJam["NUMBER"]) {
                    die('Cannot make a new submission to a past jam');
                }
            }
        break;
        case PAGE_USER_DATA:
            $render["userdata_assets"] = $assetData->GetAssetsOfUserFormatted($loggedInUser->Id);
            $render["userdata_entries"] = $gameData->GetEntriesOfUserFormatted($loggedInUser->Id);
            $render["userdata_poll_votes"] = $pollData->GetPollVotesOfUserFormatted($loggedInUser->Id);
            $render["userdata_themes"] = $themeData->GetThemesOfUserFormatted($loggedInUser->Id);
            $render["userdata_theme_votes"] = $themeData->GetThemeVotesOfUserFormatted($loggedInUser->Id);
            $render["userdata_theme_ideas"] = $themeIdeaData->GetThemeIdeasOfUserFormatted($loggedInUser->Id);
            $render["userdata_users"] = $userData->GetUsersOfUserFormatted($loggedInUser->Id);
            $render["userdata_jams"] = $jamData->GetJamsOfUserFormatted($loggedInUser->Id);
            $render["userdata_satisfaction"] = $satisfactionData->GetSatisfactionVotesOfUserFormatted($loggedInUser->Id);
            $render["userdata_sessions"] = $userData->GetSessionsOfUserFormatted($loggedInUser->Id);
            $render["userdata_admin_vote_voter"] = $adminVoteData->GetAdminVotesCastByUserFormatted($loggedInUser->Id);
            $render["userdata_admin_vote_subject"] = $adminVoteData->GetAdminVotesForSubjectUserFormatted($loggedInUser->Id);

            $render["user_data"] = Array();
            foreach($plugins as $plugin){
                $userDataSegments = $plugin->GetUserDataExport($loggedInUser->Id);
                foreach($userDataSegments as $userDataSegmentTitle => $userDataSegment){
                    $render["user_data"][] = Array("segment_title" => $userDataSegmentTitle, "segment_data" => ArrayToHTML($userDataSegment));
                }
            }
        break;
        case PAGE_NEW_JAM:
            $render["next_jam_suggested_date"] = gmdate("Y-m-d", $nextSuggestedJamDateTime);
            $render["next_jam_suggested_time"] = gmdate("H:i", $nextSuggestedJamDateTime);
        break;
        case PAGE_USER_SETTINGS:
            $render["user_bio"] = $userData->LoadBio($loggedInUser->Id);
        break;
        case PAGE_ENTRIES:
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