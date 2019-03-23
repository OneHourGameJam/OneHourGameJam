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

function RenderPageSpecific($page, &$configData, &$userData, &$gameData, &$jamData, &$themeData, &$pollData, &$satisfactionData, &$loggedInUser, &$assetData, &$cookieData, &$adminVoteData, &$nextSuggestedJamDateTime, &$adminLogData){
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
                $editingUsername = $_GET["username"];
                $editingUsername = trim(strtolower($editingUsername));
                if(!isset($userData->UserModels[$editingUsername])){
                    die("no user selected");
                }
                $render["editinguser"] = RenderUser($configData, $cookieData, $userData->UserModels[$editingUsername], $userData, $gameData, $jamData, $adminVoteData, RENDER_DEPTH_NONE);
            }
        break;
        case "editjam":
            if(IsAdmin($loggedInUser) !== false){
                $jamID = intval($_GET["jam_id"]);
                $jamFound = false;
                foreach($jamData->JamModels as $i => $jamModel){
                    if(intval($jamModel->Id) == $jamID){
                        $render["editingjam"] = RenderJam($configData, $userData, $gameData, $jamModel, $jamData, $satisfactionData, $loggedInUser, 0, RENDER_DEPTH_JAMS);
                        $jamFound = true;
                        break;
                    }
                }
                if(!$jamFound){
                    die("no jam selected");
                }
                $editingJamDate = date("Y-m-d", strtotime($render["editingjam"]["date"]));
                $render["editingjam"]["html_startdate"] = $editingJamDate;
            }
        break;
        case "editasset":
            if(IsAdmin($loggedInUser) !== false){
                if(isset($_GET["asset_id"])){
                    $assetID = intval($_GET["asset_id"]);
                    $render["editingasset"] = ((isset($assetData->AssetModels[$assetID])) ? RenderAsset($assetData->AssetModels[$assetID]) : Array());
                }
            }
        break;
        case "editentry":
            if(IsAdmin($loggedInUser) !== false){
                $entryID = intval($_GET["entry_id"]);
                $render["editingentry"] = Array();
                foreach($gameData->GameModels as $i => $gameModel){
                    if($gameModel->Id == $entryID){
                        $render["editingentry"] = RenderGame($userData, $gameModel, $jamData, RENDER_DEPTH_GAMES);
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

                $render["viewing_jam"] = RenderJam($configData, $userData, $gameData, $jamModel, $jamData, $satisfactionData, $loggedInUser, 0, RENDER_DEPTH_JAMS_GAMES);
                $pass = TRUE;
                break;
            }

            if($pass == FALSE){
                die("jam does not exist");
            }

            $render["page_title"] = "Jam #" . $viewingJamNumber . ": ".$render["viewing_jam"]["theme"];
        break;
        case "author":
            $viewingAuthor = ((isset($_GET["author"])) ? ("".$_GET["author"]) : "");
            if($viewingAuthor == ""){
                die("invalid author name");
            }

            $render['show_edit_link'] = $viewingAuthor == $loggedInUser->Username;
            $render["author_bio"] = LoadBio($viewingAuthor);
            $render["viewing_author"] = RenderUser($configData, $cookieData, $userData->UserModels[$viewingAuthor], $userData, $gameData, $jamData, $adminVoteData, RENDER_DEPTH_USERS_GAMES);
            $render["page_title"] = $viewingAuthor;
        break;
        case "submit":
            $currentJam = GetCurrentJamNumberAndID();
            if($currentJam["NUMBER"] == 0){
                die("no jam to submit to");
            }
            $jamNumber = (isset($_GET["jam_number"])) ? intval($_GET["jam_number"]) : $currentJam["NUMBER"];
            $jamModel = GetJamByNumber($jamData, $jamNumber);
            if (!$jamModel) {
                die('jam not found');
            }

            $render["submit_jam"] = RenderSubmitJam($configData, $userData, $gameData, $jamModel, $jamData, $satisfactionData, $loggedInUser, RENDER_DEPTH_JAMS);
            $colorNumber = rand(0, count($jamModel->Colors) - 1);
            $render["user_entry_color"] = $jamModel->Colors[$colorNumber];

            foreach($gameData->GameModels as $i => $gameModel){
                if($gameModel->Author != $loggedInUser->Username){
                    continue;
                }

                if($gameModel->JamNumber != $jamNumber){
                    continue;
                }

                if($gameModel->Deleted == 1){
                    continue;
                }

                //Determine entry color number
                foreach($jamModel->Colors as $colorIndex => $color){
                    if($color == $gameModel->Color){
                        $colorNumber = $colorIndex;
                        break;
                    }
                }

                $render["user_entry_color_number"] = $colorNumber;
                $render["user_entry_color"] = $jamModel->Colors[$colorNumber];

                $render["user_submitted_to_this_jam"] = true;
                $render["user_entry_name"] = $gameModel->Title;
                if($gameModel->UrlScreenshot != "logo.png"){
                    $render["user_entry_screenshot"] = $gameModel->UrlScreenshot;
                }
                $render["user_entry_url"] = $gameModel->Url;
                $render["user_entry_url_web"] = $gameModel->UrlWeb;
                $render["user_entry_url_windows"] = $gameModel->UrlWindows;
                $render["user_entry_url_mac"] = $gameModel->UrlMac;
                $render["user_entry_url_linux"] = $gameModel->UrlLinux;
                $render["user_entry_url_ios"] = $gameModel->UrliOs;
                $render["user_entry_url_android"] = $gameModel->UrlAndroid;
                $render["user_entry_url_source"] = $gameModel->UrlSource;
                $render["user_entry_desc"] = $gameModel->Description;
                //$dictionary["user_entry_color"] = $gameModel["color"];
                //$dictionary["user_entry_color_number"] = $gameModel["color_number"];

                $render["user_has_url"] = ($gameModel->Url) ? 1 : 0;
                $render["user_has_url_web"] = ($gameModel->UrlWeb) ? 1 : 0;
                $render["user_has_url_windows"] = ($gameModel->UrlWindows) ? 1 : 0;
                $render["user_has_url_mac"] = ($gameModel->UrlMac) ? 1 : 0;
                $render["user_has_url_linux"] = ($gameModel->UrlLinux) ? 1 : 0;
                $render["user_has_url_ios"] = ($gameModel->UrliOs) ? 1 : 0;
                $render["user_has_url_android"] = ($gameModel->UrlAndroid) ? 1 : 0;
                $render["user_has_url_source"] = ($gameModel->UrlSource) ? 1 : 0;
                break;
            }

            if (!isset($render["user_submitted_to_this_jam"]) && $jamNumber != $currentJam["NUMBER"]) {
                die('Cannot make a new submission to a past jam');
            }
        break;
        case "userdata":
            $render["userdata_assets"] = $assetData->GetAssetsOfUserFormatted($loggedInUser->Username);
            $render["userdata_entries"] = $gameData->GetEntriesOfUserFormatted($loggedInUser->Username);
            $render["userdata_poll_votes"] = $pollData->GetPollVotesOfUserFormatted($loggedInUser->Username);
            $render["userdata_themes"] = $themeData->GetThemesOfUserFormatted($loggedInUser->Username);
            $render["userdata_theme_votes"] = $themeData->GetThemeVotesOfUserFormatted($loggedInUser->Username);
            $render["userdata_users"] = GetUsersOfUserFormatted($loggedInUser->Username);
            $render["userdata_jams"] = $jamData->GetJamsOfUserFormatted($loggedInUser->Username);
            $render["userdata_satisfaction"] = $satisfactionData->GetSatisfactionVotesOfUserFormatted($loggedInUser->Username);
            $render["userdata_sessions"] = GetSessionsOfUserFormatted($loggedInUser->Id);
            $render["userdata_adminlog_admin"] = $adminLogData->GetAdminLogForAdminFormatted($loggedInUser->Username);
            $render["userdata_adminlog_subject"] = $adminLogData->GetAdminLogForSubjectFormatted($loggedInUser->Username);
            $render["userdata_admin_vote_voter"] = $adminVoteData->GetAdminVotesCastByUserFormatted($loggedInUser->Username);
            $render["userdata_admin_vote_subject"] = $adminVoteData->GetAdminVotesForSubjectUserFormatted($loggedInUser->Username);
        break;
        case "newjam":
            $render["next_jam_suggested_date"] = gmdate("Y-m-d", $nextSuggestedJamDateTime);
            $render["next_jam_suggested_time"] = gmdate("H:i", $nextSuggestedJamDateTime);
        break;
        case "usersettings":
            $render["user_bio"] = LoadBio($loggedInUser->Username);
        break;
    }

    StopTimer("RenderPageSpecific");
    return $render;
}


?>