<?php

class FormPresenter{
	public static function RenderForms(&$plugins){
		AddActionLog("RenderForms");
		StartTimer("RenderForms");
	
        $formViewModel = new FormViewModel();
        
        $formViewModel->get["page"] = GET_PAGE;
        $formViewModel->get["load_all"] = GET_LOAD_ALL;
        $formViewModel->get["dark_mode"] = GET_DARK_MODE;
        $formViewModel->get["streaming_mode"] = GET_STREAMING_MODE;
        $formViewModel->get["edituser"]["user_id"] = GET_EDITUSER_USER_ID;
        $formViewModel->get["editjam"]["jam_id"] = GET_EDITJAM_JAM_Id;
        $formViewModel->get["editasset"]["asset_id"] = GET_EDITASSET_ASSET_ID;
        $formViewModel->get["editentry"]["entry_id"] = GET_EDITENTRY_ENTRY_ID;
        $formViewModel->get["jam"]["number"] = GET_JAM_JAM_NUMBER;
        $formViewModel->get["author"]["username"] = GET_AUTHOR_USERNAME;
        $formViewModel->get["submit"]["jam_number"] = GET_SUBMIT_JAM_NUMBER;

        $formViewModel->form["action"] = FORM_POST_ACTION;
        $formViewModel->form["csrf_token"] = FORM_CSRF_TOKEN;
        $formViewModel->form["install"]["db_host"] = FORM_INSTALL_DB_HOST;
        $formViewModel->form["install"]["db_username"] = FORM_INSTALL_DB_USERNAME;
        $formViewModel->form["install"]["db_password"] = FORM_INSTALL_DB_PASSWORD;
        $formViewModel->form["install"]["db_name"] = FORM_INSTALL_DB_NAME;
        $formViewModel->form["install"]["init_database"] = FORM_INSTALL_INIT_DATABASE;
        $formViewModel->form["adminvote"]["subject_user_id"] = FORM_ADMINVOTE_SUBJECT_USER_ID;
        $formViewModel->form["adminvote"]["vote_type"] = FORM_ADMINVOTE_VOTE_TYPE;
        $formViewModel->form["deleteasset"]["asset_id"] = FORM_DELETEASSET_ASSET_ID;
        $formViewModel->form["saveasset"]["asset_id"] = FORM_SAVEASSET_ASSET_ID;
        $formViewModel->form["saveasset"]["author"] = FORM_SAVEASSET_AUTHOR;
        $formViewModel->form["saveasset"]["title"] = FORM_SAVEASSET_TITLE;
        $formViewModel->form["saveasset"]["description"] = FORM_SAVEASSET_DESCRIPTION;
        $formViewModel->form["saveasset"]["type"] = FORM_SAVEASSET_TYPE;
        $formViewModel->form["login"]["username"] = FORM_LOGIN_USERNAME;
        $formViewModel->form["login"]["password"] = FORM_LOGIN_PASSWORD;
        $formViewModel->form["register"]["username"] = FORM_REGISTER_USERNAME;
        $formViewModel->form["register"]["password"] = FORM_REGISTER_PASSWORD;
        $formViewModel->form["deleteentry"]["entry_id"] = FORM_DELETEENTRY_ENTRY_ID;
        $formViewModel->form["submit"]["name"] = FORM_SUBMIT_NAME;
        $formViewModel->form["submit"]["screenshot"] = "screenshotfile";
        $formViewModel->form["submit"]["description"] = FORM_SUBMIT_DESCRIPTION;
        $formViewModel->form["submit"]["jam_number"] = FORM_SUBMIT_JAM_NUMBER;
        $formViewModel->form["submit"]["background_color"] = FORM_SUBMIT_BACKGROUND_COLOR;
        $formViewModel->form["submit"]["text_color"] = FORM_SUBMIT_TEXT_COLOR;
        $formViewModel->form["submit"]["url"] = FORM_SUBMIT_URL;
        $formViewModel->form["submit"]["satisfaction"] = FORM_SUBMIT_SATISFACTION;
        $formViewModel->form["deletejam"]["jam_id"] = FORM_DELETEJAM_JAM_ID;
        $formViewModel->form["newjam"]["theme"] = FORM_NEWJAM_THEME;
        $formViewModel->form["newjam"]["date"] = FORM_NEWJAM_DATE;
        $formViewModel->form["newjam"]["time"] = FORM_NEWJAM_TIME;
        $formViewModel->form["newjam"]["jam_color"] = FORM_NEWJAM_JAM_COLOR;
        $formViewModel->form["newjam"]["default_icon_url"] = FORM_NEWJAM_DEFAULT_ICON_URL;
        $formViewModel->form["newjam"]["event_name"] = FORM_NEWJAM_EVENT_NAME;
        $formViewModel->form["editjam"]["jam_id"] = FORM_EDITJAM_JAM_ID;
        $formViewModel->form["editjam"]["number"] = "jam_number";
        $formViewModel->form["editjam"]["theme"] = FORM_EDITJAM_THEME;
        $formViewModel->form["editjam"]["date"] = FORM_EDITJAM_DATE;
        $formViewModel->form["editjam"]["time"] = FORM_EDITJAM_TIME;
        $formViewModel->form["editjam"]["streamer_username"] = FORM_EDITJAM_STREAMER_USERNAME;
        $formViewModel->form["editjam"]["streamer_twitch_username"] = FORM_EDITJAM_STREAMER_TWITCH_USERNAME;
        $formViewModel->form["editjam"]["jam_color"] = FORM_EDITJAM_JAM_COLORS;
        $formViewModel->form["editjam"]["default_icon_url"] = FORM_EDITJAM_DEFAULT_ICON_URL;
        $formViewModel->form["editjam"]["event_name"] = FORM_EDITJAM_EVENT_NAME;
        $formViewModel->form["deleteplatform"]["platform_id"] = FORM_DELETEPLATFORM_PLATFORM_ID;
        $formViewModel->form["editplatform"]["platform_id"] = FORM_EDITPLATFORM_PLATFORM_ID;
        $formViewModel->form["editplatform"]["name"] = FORM_EDITPLATFORM_NAME;
        $formViewModel->form["editplatform"]["file"] = "platformFile"; //move $_file
        $formViewModel->form["newplatform"]["name"] = FORM_NEWPLATFORM_NAME;
        $formViewModel->form["undeleteplatform"]["name"] = FORM_UNDELETEPLATFORM_NAME;
        $formViewModel->form["bantheme"]["theme_id"] = FORM_BANTHEME_THEME_ID;
        $formViewModel->form["deletetheme"]["theme_id"] = FORM_DELETETHEME_THEME_ID;
        $formViewModel->form["deletetheme"]["page"] = FORM_DELETETHEME_PAGE;
        $formViewModel->form["deletethemes"]["theme_id"] = FORM_DELETETHEMES_THEME_ID;
        $formViewModel->form["newtheme"]["theme"] = FORM_NEWTHEME_THEME;
        $formViewModel->form["unbantheme"]["theme_id"] = FORM_UNBANTHEME_THEME_ID;
        $formViewModel->form["changepassword"]["old_password"] = FORM_CHANGEPASSWORD_OLD_PASSWORD;
        $formViewModel->form["changepassword"]["password_1"] = FORM_CHANGEPASSWORD_PASSWORD_1;
        $formViewModel->form["changepassword"]["password_2"] = FORM_CHANGEPASSWORD_PASSWORD_2;
        $formViewModel->form["savenewuserpassword"]["user_id"] = FORM_SAVENEWUSERPASSWORD_USER_ID;
        $formViewModel->form["savenewuserpassword"]["password_1"] = FORM_SAVENEWUSERPASSWORD_PASSWORD_1;
        $formViewModel->form["savenewuserpassword"]["password_2"] = FORM_SAVENEWUSERPASSWORD_PASSWORD_2;
        $formViewModel->form["usersettings"]["display_name"] = FORM_SAVEUSERCHANGES_DISPLAY_NAME;
        $formViewModel->form["usersettings"]["twitter_handle"] = FORM_SAVEUSERCHANGES_TWITTER_HANDLE;
        $formViewModel->form["usersettings"]["twitch_username"] = FORM_SAVEUSERCHANGES_TWITCH_USERNAME;
        $formViewModel->form["usersettings"]["email_address"] = FORM_SAVEUSERCHANGES_EMAIL_ADDRESS;
        $formViewModel->form["usersettings"]["bio"] = FORM_SAVEUSERCHANGES_BIO;
        $formViewModel->form["edituser"]["user_id"] = FORM_EDITUSER_USER_ID;
        $formViewModel->form["edituser"]["username"] = "username";
        $formViewModel->form["edituser"]["display_name"] = FORM_EDITUSER_DISPLAY_NAME;
        $formViewModel->form["edituser"]["twitter_handle"] = FORM_EDITUSER_TWITTER_HANDLE;
        $formViewModel->form["edituser"]["twitch_username"] = FORM_EDITUSER_TWITCH_USERNAME;
        $formViewModel->form["edituser"]["email_address"] = FORM_EDITUSER_EMAIL_ADDRESS;
        $formViewModel->form["edituser"]["bio"] = FORM_EDITUSER_BIO;
        $formViewModel->form["edituser"]["permission_level"] = FORM_EDITUSER_PERMISSION_LEVEL;
        $formViewModel->form["cookienotice"]["accept"] = FORM_COOKIENOTICE_ACCEPT;
        $formViewModel->form["pollvote"]["poll_id"] = FORM_POLLVOTE_POLL_ID;
        $formViewModel->form["pollvote"]["option_id"] = FORM_POLLVOTE_OPTION_ID;
        $formViewModel->form["submitideas"]["theme_id"] = FORM_SUBMITIDEAS_THEME_ID;
        $formViewModel->form["submitideas"]["ideas"] = FORM_SUBMITIDEAS_IDEAS;
        $formViewModel->form["themevote"]["theme_id"] = FORM_THEMEVOTE_THEME_ID;
        $formViewModel->form["themevote"]["vote"] = FORM_THEMEVOTE_VOTE;
        $formViewModel->form["editasset"]["asset_id"] = "asset_id";
        $formViewModel->form["editasset"]["author"] = "author";
        $formViewModel->form["editasset"]["title"] = "title";
        $formViewModel->form["editasset"]["description"] = "description";
        $formViewModel->form["editasset"]["type"] = "type";
        $formViewModel->form["editasset"]["assetfile"] = "assetfile";
        $formViewModel->form["editentry"]["entry_id"] = "entry_id";
        $formViewModel->form["editentry"]["jam_number"] = "jam_number";
        $formViewModel->form["editentry"]["author_user_id"] = "author_user_id";
        $formViewModel->form["editentry"]["title"] = "title";
        $formViewModel->form["editentry"]["description"] = "description";
        $formViewModel->form["editentry"]["screenshot_url"] = "screenshot_url";
        $formViewModel->form["editentry"]["platform_game_id"] = "platform_game_id_";
        $formViewModel->form["editentry"]["url"] = "url_";
        $formViewModel->form["editentry"]["entry_id"] = "entry_id";
        $formViewModel->form["editentry"]["entry_id"] = "entry_id";
        $formViewModel->form["setstreamer"]["jam_number"] = FORM_SETSTREAMER_JAM_NUMBER;
        $formViewModel->form["unsetstreamer"]["jam_number"] = FORM_UNSETSTREAMER_JAM_NUMBER;
        
        $formViewModel->pages["main"] = PAGE_MAIN;
        $formViewModel->pages["login"] = PAGE_LOGIN;
        $formViewModel->pages["register"] = PAGE_REGISTER;
        $formViewModel->pages["forgotpassword"] = PAGE_FORGOT_PASSWORD;
        $formViewModel->pages["submit"] = PAGE_SUBMIT;
        $formViewModel->pages["newjam"] = PAGE_NEW_JAM;
        $formViewModel->pages["assets"] = PAGE_ASSETS;
        $formViewModel->pages["editasset"] = PAGE_EDIT_ASSET;
        $formViewModel->pages["rules"] = PAGE_RULES;
        $formViewModel->pages["config"] = PAGE_CONFIG;
        $formViewModel->pages["editcontent"] = PAGE_EDIT_CONTENT;
        $formViewModel->pages["editjam"] = PAGE_EDIT_JAM;
        $formViewModel->pages["editentry"] = PAGE_EDIT_ENTRY;
        $formViewModel->pages["editusers"] = PAGE_EDIT_USERS;
        $formViewModel->pages["edituser"] = PAGE_EDIT_USER;
        $formViewModel->pages["themes"] = PAGE_THEMES;
        $formViewModel->pages["managethemes"] = PAGE_MANAGE_THEMES;
        $formViewModel->pages["usersettings"] = PAGE_USER_SETTINGS;
        $formViewModel->pages["entries"] = PAGE_ENTRIES;
        $formViewModel->pages["jam"] = PAGE_JAM;
        $formViewModel->pages["jams"] = PAGE_JAMS;
        $formViewModel->pages["author"] = PAGE_AUTHOR;
        $formViewModel->pages["authors"] = PAGE_AUTHORS;
        $formViewModel->pages["privacy"] = PAGE_PRIVACY;
        $formViewModel->pages["userdata"] = PAGE_USER_DATA;
        $formViewModel->pages["polls"] = PAGE_POLLS;
        $formViewModel->pages["editplatforms"] = PAGE_EDIT_PLATFORMS;
        $formViewModel->pages["editassets"] = PAGE_EDIT_ASSETS;

        foreach($plugins as $i => $plugin){
            foreach($plugin->PageSettings() as $pageName => $pageSetting){
                $formViewModel->pages[$plugin->NameInTemplate][$pageName] = $pageName;
            }
        }
        
        $formViewModel->action["login"] = ACTION_LOGIN;
        $formViewModel->action["register"] = ACTION_REGISTER;
        $formViewModel->action["logout"] = ACTION_LOGOUT;
        $formViewModel->action["submit"] = ACTION_SUBMIT;
        $formViewModel->action["newjam"] = ACTION_NEW_JAM;
        $formViewModel->action["deletejam"] = ACTION_DELETE_JAM;
        $formViewModel->action["deleteentry"] = ACTION_DELETE_ENTRY;
        $formViewModel->action["saveconfig"] = ACTION_SAVE_CONFIG;
        $formViewModel->action["saveassetedits"] = ACTION_SAVE_ASSET_EDITS;
        $formViewModel->action["deleteasset"] = ACTION_DELETE_ASSET;
        $formViewModel->action["savejamedits"] = ACTION_SAVE_JAM_EDITS;
        $formViewModel->action["saveuseredits"] = ACTION_SAVE_USER_EDITS;
        $formViewModel->action["savenewuserpassword"] = ACTION_SAVE_NEW_USER_PASSWORD;
        $formViewModel->action["changepassword"] = ACTION_CHANGE_PASSWORD;
        $formViewModel->action["saveuserchanges"] = ACTION_SAVE_USER_CHANGES;
        $formViewModel->action["savenewtheme"] = ACTION_SAVE_NEW_THEME;
        $formViewModel->action["deletetheme"] = ACTION_DELETE_THEME;
        $formViewModel->action["deletethemes"] = ACTION_DELETE_THEMES;
        $formViewModel->action["bantheme"] = ACTION_BAN_THEME;
        $formViewModel->action["unbantheme"] = ACTION_UNBAN_THEME;
        $formViewModel->action["downloaddb"] = ACTION_DOWNLOAD_DB;
        $formViewModel->action["adminvote"] = ACTION_ADMIN_VOTE;
        $formViewModel->action["newplatform"] = ACTION_NEW_PLAYFORM;
        $formViewModel->action["editplatform"] = ACTION_EDIT_PLATFORM;
        $formViewModel->action["deleteplatform"] = ACTION_DELETE_PLATFORM;
        $formViewModel->action["undeleteplatform"] = ACTION_UNDELETE_PLATFORM;
        $formViewModel->action["setup_database"] = "setup";
        $formViewModel->action["setstreamer"] = ACTION_SET_STREAMER;
        $formViewModel->action["unsetstreamer"] = ACTION_UNSET_STREAMER;
        
        $formViewModel->constant["adminvote"]["for"] = ADMINVOTE_FOR;
        $formViewModel->constant["adminvote"]["neutral"] = ADMINVOTE_NEUTRAL;
        $formViewModel->constant["adminvote"]["against"] = ADMINVOTE_AGAINST;
        $formViewModel->constant["adminvote"]["sponsor"] = ADMINVOTE_SPONSOR;
        $formViewModel->constant["adminvote"]["veto"] = ADMINVOTE_VETO;
    
        $formViewModel->preference["disable_themes_notification"] = PREFERENCE_DISABLE_THEMES_NOTIFICATION;
        
        foreach($plugins as $i => $plugin){
            $pluginFormSettings = $plugin->FormSettings();
            foreach($pluginFormSettings["get"] as $pageName => $getParameterName){
                $formViewModel->get[$plugin->NameInTemplate][$pageName][$getParameterName] = $getParameterName;
            }
            foreach($pluginFormSettings["form"] as $formName => $formParameters){
                $formViewModel->form[$plugin->NameInTemplate][$formName] = $formParameters;
            }
            foreach($pluginFormSettings["action"] as $actionKey => $actionName){
                $formViewModel->action[$plugin->NameInTemplate][$actionKey] = $actionName;
            }
            foreach($pluginFormSettings["constant"] as $constantUse => $constantValue){
                $formViewModel->constant[$plugin->NameInTemplate][$constantUse] = $constantValue;
            }
            foreach($pluginFormSettings["preference"] as $preferenceKey => $preferenceValue){
                $formViewModel->preference[$plugin->NameInTemplate][$preferenceKey] = $preferenceValue;
            }
        }
        
		StopTimer("RenderForms");
		return $formViewModel;
	}
}

?>