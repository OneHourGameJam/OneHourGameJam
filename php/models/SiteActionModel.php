<?php

define("ACTION_LOGIN", "login");
define("ACTION_REGISTER", "register");
define("ACTION_LOGOUT", "logout");
define("ACTION_SUBMIT", "submit");
define("ACTION_NEW_JAM", "newjam");
define("ACTION_DELETE_JAM", "deletejam");
define("ACTION_DELETE_ENTRY", "deleteentry");
define("ACTION_SAVE_CONFIG", "saveconfig");
define("ACTION_SAVE_ASSET_EDITS", "saveassetedits");
define("ACTION_DELETE_ASSET", "deleteasset");
define("ACTION_SAVE_JAM_EDITS", "savejamedits");
define("ACTION_SAVE_USER_EDITS", "saveuseredits");
define("ACTION_SAVE_NEW_USER_PASSWORD", "savenewuserpassword");
define("ACTION_CHANGE_PASSWORD", "changepassword");
define("ACTION_SAVE_USER_CHANGES", "saveuserchanges");
define("ACTION_SAVE_NEW_THEME", "savenewtheme");
define("ACTION_DELETE_THEME", "deletetheme");
define("ACTION_DELETE_THEMES", "deletethemes");
define("ACTION_BAN_THEME", "bantheme");
define("ACTION_UNBAN_THEME", "unbantheme");
define("ACTION_DOWNLOAD_DB", "downloaddb");
define("ACTION_ADMIN_VOTE", "adminvote");
define("ACTION_NEW_PLAYFORM", "newplatform");
define("ACTION_EDIT_PLATFORM", "editplatform");
define("ACTION_DELETE_PLATFORM", "deleteplatform");
define("ACTION_UNDELETE_PLATFORM", "undeleteplatform");

class SiteActionResultModel{
    public $RedirectUrl;
    public $MessageType;
    public $MessageText;

    function __construct($redirectUrl, $messageType, $messageText) {
        $this->RedirectUrl = $redirectUrl;
        $this->MessageType = $messageType;
        $this->MessageText = $messageText;
    }
}

class SiteActionModel{
    public $PostRequest;
    public $PhpFile;
    public $RedirectAfterExecution;
    public $ActionResult;

    function __construct($postRequest, $phpFile, $redirectAfterExecution, $actionResult) {
        $this->PostRequest = $postRequest;
        $this->PhpFile = $phpFile;
        $this->RedirectAfterExecution = $redirectAfterExecution;
        $this->ActionResult = $actionResult;
    }
}

class SiteActionData{
    public $SiteActionModels;

    function __construct(&$configData) {
        $this->SiteActionModels = $this->LoadSiteActions($configData);
    }

    function LoadSiteActions(&$configData){
        AddActionLog("LoadSiteActions");
        StartTimer("LoadSiteActions");
        
        //Actions data: The data in this list governs how site actions are performed
        $actions = Array(
            new SiteActionModel(
                ACTION_LOGIN,
                "php/actions/authentication/login.php",
                "?".GET_PAGE."=".PAGE_MAIN,
                Array(
                    "SUCCESS" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_MAIN, MESSAGE_SUCCESS, "Logged in successfully"),
                    "INVALID_PASSWORD_LENGTH" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_LOGIN, MESSAGE_WARNING, "Incorrect password length. Must be between ".$configData->ConfigModels[CONFIG_MINIMUM_PASSWORD_LENGTH]->Value." and ".$configData->ConfigModels[CONFIG_MAXIMUM_PASSWORD_LENGTH]->Value." characters long."),
                    "INVALID_USERNAME_LENGTH" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_LOGIN, MESSAGE_WARNING, "Incorrect username length. Must be between ".$configData->ConfigModels[CONFIG_MINIMUM_USERNAME_LENGTH]->Value." and ".$configData->ConfigModels[CONFIG_MAXIMUM_USERNAME_LENGTH]->Value." characters long."),
                    "USER_DOES_NOT_EXIST" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_LOGIN, MESSAGE_WARNING, "That username doesn't exist.<br>Do you want to <a href='?".GET_PAGE."=".PAGE_REGISTER."'>create an account</a>?"),
                    "INCORRECT_PASSWORD" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_LOGIN, MESSAGE_WARNING, "Incorrect username/password combination."),
                )
            ),
            new SiteActionModel(
                ACTION_REGISTER,
                "php/actions/authentication/register.php",
                "?".GET_PAGE."=".PAGE_MAIN,
                Array(
                    "SUCCESS" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_MAIN, MESSAGE_SUCCESS, "Logged in successfully"),
                    "INVALID_PASSWORD_LENGTH" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_REGISTER, MESSAGE_WARNING, "Incorrect password length. Must be between ".$configData->ConfigModels[CONFIG_MINIMUM_PASSWORD_LENGTH]->Value." and ".$configData->ConfigModels[CONFIG_MAXIMUM_PASSWORD_LENGTH]->Value." characters long."),
                    "INVALID_USERNAME_LENGTH" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_REGISTER, MESSAGE_WARNING, "Incorrect username length. Must be between ".$configData->ConfigModels[CONFIG_MINIMUM_USERNAME_LENGTH]->Value." and ".$configData->ConfigModels[CONFIG_MAXIMUM_USERNAME_LENGTH]->Value." characters long."),
                    "USERNAME_ALREADY_REGISTERED" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_REGISTER, MESSAGE_WARNING, "That username already exists.<br>Did you want to <a href='?".GET_PAGE."=".PAGE_LOGIN."'>log in</a>?"),
                )
            ),
            new SiteActionModel(
                ACTION_LOGOUT,
                "php/actions/authentication/logout.php",
                "?".GET_PAGE."=".PAGE_MAIN,
                Array(
                    "SUCCESS" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_MAIN, MESSAGE_SUCCESS, "Logged out successfully")
                )
            ),
            new SiteActionModel(
                ACTION_SUBMIT,
                "php/actions/games/submit.php",
                "?".GET_PAGE."=".PAGE_MAIN,
                Array(
                    "SUCCESS_ENTRY_ADDED" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_MAIN, MESSAGE_SUCCESS, "Game Added."),
                    "SUCCESS_ENTRY_UPDATED" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_MAIN, MESSAGE_SUCCESS, "Game Updated."),
                    "NOT_LOGGED_IN" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_LOGIN, MESSAGE_WARNING, "Not Logged In."),
                    "MISSING_GAME_NAME" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_SUBMIT, MESSAGE_WARNING, "Missing Game Name."),
                    "INVALID_GAME_URL" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_SUBMIT, MESSAGE_WARNING, "Invalid Game URL."),
                    "INVALID_DESCRIPTION" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_SUBMIT, MESSAGE_WARNING, "Missing Description."),
                    "INVALID_JAM_NUMBER" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_SUBMIT, MESSAGE_ERROR, "Invalid Jam Number, please contact administrators."),
                    "NO_JAM_TO_SUBMIT_TO" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_SUBMIT, MESSAGE_ERROR, "There is no active jam to submit to, please contact administrators."),
                    "JAM_NOT_STARTED" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_MAIN, MESSAGE_ERROR, "That jam hasn't yet started."),
                    "INVALID_COLOR" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_SUBMIT, MESSAGE_WARNING, "The selected color is not valid."),
                    "SCREENSHOT_NOT_AN_IMAGE" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_SUBMIT, MESSAGE_WARNING, "The uploaded screenshot is not an image."),
                    "SCREENSHOT_TOO_BIG" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_SUBMIT, MESSAGE_WARNING, "The uploaded screenshot is too big."),
                    "SCREENSHOT_WRONG_FILE_TYPE" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_SUBMIT, MESSAGE_WARNING, "Screenshot is not of a valid file type."),
                    "CANNOT_SUBMIT_TO_PAST_JAM" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_SUBMIT, MESSAGE_ERROR, "Cannot submit to a past jam, please contact administrators."),
                    "ENTRY_NOT_ADDED" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_SUBMIT, MESSAGE_ERROR, "An internal error stopped the entry from being added, please contact an administrator."),
                )
            ),
            new SiteActionModel(
                ACTION_NEW_JAM,
                "php/actions/jam/newjam.php",
                "?".GET_PAGE."=".PAGE_MAIN,
                Array(
                    "SUCCESS" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_EDIT_CONTENT, MESSAGE_SUCCESS, "Jam scheduled."),
                    "INVALID_TIME" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_NEW_JAM, MESSAGE_WARNING, "Time is not valid."),
                    "INVALID_DATE" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_NEW_JAM, MESSAGE_WARNING, "Date is not valid."),
                    "INVALID_THEME" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_NEW_JAM, MESSAGE_WARNING, "Theme is not valid."),
                    "INVALID_JAM_NUMBER" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_NEW_JAM, MESSAGE_ERROR, "Jam number is not valid."),
                    "NOT_AUTHORIZED" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_MAIN, MESSAGE_ERROR, "Only admins can perform this action."),
                    "NOT_LOGGED_IN" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_LOGIN, MESSAGE_WARNING, "Not logged in."),
                )
            ),
            new SiteActionModel(
                ACTION_DELETE_JAM,
                "php/actions/jam/deletejam.php",
                "?".GET_PAGE."=".PAGE_MAIN,
                Array(
                    "SUCCESS" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_EDIT_CONTENT, MESSAGE_SUCCESS, "Jam Deleted."),
                    "NO_JAMS_EXIST" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_EDIT_CONTENT, MESSAGE_ERROR, "No jams exist."),
                    "INVALID_JAM_ID" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_EDIT_CONTENT, MESSAGE_ERROR, "Invalid jam id."),
                    "CANNOT_DELETE_JAM" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_EDIT_CONTENT, MESSAGE_ERROR, "Cannot delete jam."),
                    "NOT_AUTHORIZED" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_EDIT_CONTENT, MESSAGE_ERROR, "Only admins can perform this action."),
                )
            ),
            new SiteActionModel(
                ACTION_DELETE_ENTRY,
                "php/actions/games/deleteentry.php",
                "?".GET_PAGE."=".PAGE_MAIN,
                Array(
                    "SUCCESS" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_EDIT_CONTENT, MESSAGE_SUCCESS, "Entry deleted."),
                    "NO_JAMS_EXIST" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_EDIT_CONTENT, MESSAGE_ERROR, "No jams exist."),
                    "INVALID_JAM_ID" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_EDIT_CONTENT, MESSAGE_ERROR, "Invalid jam id."),
                    "CANNOT_DELETE_ENTRY" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_EDIT_CONTENT, MESSAGE_ERROR, "cannot delete entry."),
                    "NOT_AUTHORIZED" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_EDIT_CONTENT, MESSAGE_ERROR, "Only admins can perform this action."),
                )
            ),
            new SiteActionModel(
                ACTION_SAVE_CONFIG,
                "php/actions/config/saveconfig.php",
                "?".GET_PAGE."=".PAGE_MAIN,
                Array(
                    "SUCCESS" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_CONFIG, MESSAGE_SUCCESS, "Config updated."),
                    "NOT_AUTHORIZED" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_MAIN, MESSAGE_ERROR, "Only admins can perform this action."),
                    "NO_CHANGE" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_CONFIG, MESSAGE_WARNING, "No changes to config."),
                )
            ),
            new SiteActionModel(
                ACTION_SAVE_ASSET_EDITS,
                "php/actions/asset/saveassetedits.php",
                "?".GET_PAGE."=".PAGE_MAIN,
                Array(
                    "SUCCESS_INSERTED" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_EDIT_ASSETS, MESSAGE_SUCCESS, "Asset added."),
                    "SUCCESS_UPDATED" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_EDIT_ASSETS, MESSAGE_SUCCESS, "Asset updated"),
                    "COULD_NOT_DETERMINE_URL" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_EDIT_ASSETS, MESSAGE_ERROR, "Could not find a URL for the asset. Please look at the assets folder on the web server."),
                    "UNLOADED_ASSET_TOO_BIG" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_EDIT_ASSETS, MESSAGE_WARNING, "Asset too big."),
                    "COULD_NOT_FIND_VALID_FILE_NAME" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_ASSETS, MESSAGE_WARNING, "Could not find a file name for the asset. Please look at the assets folder on the web server."),
                    "INVALID_ASSET_TYPE" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_EDIT_ASSETS, MESSAGE_WARNING, "Invalid asset type."),
                    "ASSET_TYPE_EMPTY" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_EDIT_ASSETS, MESSAGE_WARNING, "Missing asset type."),
                    "INVALID_DESCRIPTION" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_EDIT_ASSETS, MESSAGE_WARNING, "Invalid description."),
                    "INVALID_TITLE" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_EDIT_ASSETS, MESSAGE_WARNING, "Invalid title."),
                    "INVALID_AUTHOR" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_EDIT_ASSETS, MESSAGE_WARNING, "Invalid author - must match a username of a registered user."),
                    "AUTHOR_EMPTY" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_EDIT_ASSETS, MESSAGE_WARNING, "missing author."),
                    "NOT_AUTHORIZED" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_MAIN, MESSAGE_ERROR, "Only admins can perform this action."),
                )
            ),
            new SiteActionModel(
                ACTION_DELETE_ASSET,
                "php/actions/asset/deleteasset.php",
                "?".GET_PAGE."=".PAGE_MAIN,
                Array(
                    "SUCCESS" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_EDIT_ASSETS, MESSAGE_SUCCESS, "Asset deleted."),
                    "ASSET_DOES_NOT_EXIST" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_EDIT_ASSETS, MESSAGE_ERROR, "asset does not exist."),
                    "NOT_AUTHORIZED" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_MAIN, MESSAGE_ERROR, "Only admins can perform this action."),
                )
            ),
            new SiteActionModel(
                ACTION_SAVE_JAM_EDITS,
                "php/actions/jam/savejamedits.php",
                "?".GET_PAGE."=".PAGE_MAIN,
                Array(
                    "SUCCESS" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_EDIT_CONTENT, MESSAGE_SUCCESS, "Jam updated."),
                    "INVALID_TIME" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_EDIT_CONTENT, MESSAGE_WARNING, "Invalid time."),
                    "INVALID_DATE" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_EDIT_CONTENT, MESSAGE_WARNING, "Invalid date."),
                    "INVALID_THEME" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_EDIT_CONTENT, MESSAGE_WARNING, "Invalid theme."),
                    "INVALID_JAM_NUMBER" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_EDIT_CONTENT, MESSAGE_ERROR, "Invalid jam number"),
                    "NOT_AUTHORIZED" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_MAIN, MESSAGE_ERROR, "Only admins can perform this action."),
                    "NO_JAMS_EXIST" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_EDIT_CONTENT, MESSAGE_ERROR, "No jams exist."),
                    "INVALID_JAM_ID" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_EDIT_CONTENT, MESSAGE_ERROR, "Invalid jam id."),
                    "INVALID_COLOR" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_EDIT_CONTENT, MESSAGE_WARNING, "Invalid colors."),
                )
            ),
            new SiteActionModel(
                ACTION_SAVE_USER_EDITS,
                "php/actions/user/saveuseredits.php",
                "?".GET_PAGE."=".PAGE_MAIN,
                Array(
                    "SUCCESS" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_EDIT_USERS, MESSAGE_SUCCESS, "User successfully edited"),
                    "USER_DOES_NOT_EXIST" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_EDIT_USERS, MESSAGE_ERROR, "User does not exist."),
                    "INVALID_ISADMIN" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_EDIT_USERS, MESSAGE_ERROR, "Invalid IsAdmin."),
                    "NOT_AUTHORIZED" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_MAIN, MESSAGE_ERROR, "Only admins can perform this action."),
                )
            ),
            new SiteActionModel(
                ACTION_SAVE_NEW_USER_PASSWORD,
                "php/actions/user/savenewuserpassword.php",
                "?".GET_PAGE."=".PAGE_MAIN,
                Array(
                    "SUCCESS" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_EDIT_USERS, MESSAGE_SUCCESS, "Password Updated."),
                    "USER_DOES_NOT_EXIST" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_EDIT_USERS, MESSAGE_ERROR, "User does not exist."),
                    "INVALID_PASSWORD_LENGTH" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_EDIT_USERS, MESSAGE_WARNING, "Incorrect password length. Must be between ".$configData->ConfigModels[CONFIG_MINIMUM_PASSWORD_LENGTH]->Value." and ".$configData->ConfigModels[CONFIG_MAXIMUM_PASSWORD_LENGTH]->Value." characters long."),
                    "PASSWORDS_DONT_MATCH" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_EDIT_USERS, MESSAGE_WARNING, "Passwords do not match."),
                    "NOT_AUTHORIZED" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_MAIN, MESSAGE_ERROR, "Only admins can perform this action."),
                )
            ),
            new SiteActionModel(
                ACTION_CHANGE_PASSWORD,
                "php/actions/user/changepassword.php",
                "?".GET_PAGE."=".PAGE_MAIN,
                Array(
                    "SUCCESS" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_USER_SETTINGS, MESSAGE_SUCCESS, "Password Updated."),
                    "USER_DOES_NOT_EXIST" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_USER_SETTINGS, MESSAGE_ERROR, "User does not exist."),
                    "INVALID_PASSWORD_LENGTH" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_USER_SETTINGS, MESSAGE_WARNING, "Incorrect password length. Must be between ".$configData->ConfigModels[CONFIG_MINIMUM_PASSWORD_LENGTH]->Value." and ".$configData->ConfigModels[CONFIG_MAXIMUM_PASSWORD_LENGTH]->Value." characters long."),
                    "PASSWORDS_DONT_MATCH" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_USER_SETTINGS, MESSAGE_WARNING, "Passwords do not match."),
                    "INCORRECT_PASSWORD" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_USER_SETTINGS, MESSAGE_WARNING, "Old password is not correct."),
                    "NOT_LOGGED_IN" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_LOGIN, MESSAGE_WARNING, "Not logged in."),
                )
            ),
            new SiteActionModel(
                ACTION_SAVE_USER_CHANGES,
                "php/actions/user/saveuserchanges.php",
                "?".GET_PAGE."=".PAGE_MAIN,
                Array(
                    "SUCCESS" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_USER_SETTINGS, MESSAGE_SUCCESS, "User settings updated."),
                    "INVALID_EMAIL" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_USER_SETTINGS, MESSAGE_WARNING, "Email is not valid."),
                    "INVALID_DISPLAY_NAME" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_USER_SETTINGS, MESSAGE_WARNING, "Incorrect display name length. Must be between ".$configData->ConfigModels[CONFIG_MINIMUM_DISPLAY_NAME_LENGTH]->Value." and ".$configData->ConfigModels[CONFIG_MAXIMUM_DISPLAY_NAME_LENGTH]->Value." characters long."),
                    "NOT_LOGGED_IN" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_LOGIN, MESSAGE_WARNING, "Not logged in."),
                )
            ),
            new SiteActionModel(
                ACTION_SAVE_NEW_THEME,
                "php/actions/theme/savenewtheme.php",
                "?".GET_PAGE."=".PAGE_MAIN,
                Array(
                    "SUCCESS" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_THEMES, MESSAGE_SUCCESS, "Theme added."),
                    "THEME_ALREADY_SUGGESTED" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_THEMES, MESSAGE_WARNING, "Theme is already suggested."),
                    "INVALID_THEME" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_THEMES, MESSAGE_WARNING, "Theme is not valid."),
                    "NOT_LOGGED_IN" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_LOGIN, MESSAGE_WARNING, "Not logged in."),
                    "THEME_RECENTLY_USED" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_THEMES, MESSAGE_WARNING, "Theme has been used in a recent jam."),
                    "TOO_MANY_THEMES" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_THEMES, MESSAGE_WARNING, "You can only submit ".$configData->ConfigModels[CONFIG_THEMES_PER_USER]->Value." themes. Please delete past themes to submit again."),
                    "THEME_BANNED" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_THEMES, MESSAGE_WARNING, "This theme's been banned.")
                )
            ),
            new SiteActionModel(
                ACTION_DELETE_THEME,
                "php/actions/theme/deletetheme.php",
                "?".GET_PAGE."=".PAGE_MAIN,
                Array(
                    "SUCCESS_THEMES" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_THEMES, MESSAGE_SUCCESS, "Theme deleted."),
                    "SUCCESS_MANAGETHEMES" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_MANAGE_THEMES, MESSAGE_SUCCESS, "Theme deleted."),
                    "INVALID_THEME" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_THEMES, MESSAGE_WARNING, "Theme is not valid."),
                    "THEME_DOES_NOT_EXIST" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_THEMES, MESSAGE_WARNING, "Theme does not exist."),
                    "NOT_AUTHORIZED" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_MAIN, MESSAGE_ERROR, "Only admins can perform this action."),
                    "NOT_LOGGED_IN" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_LOGIN, MESSAGE_WARNING, "Not logged in."),
                )
            ),
            new SiteActionModel(
                ACTION_DELETE_THEMES,
                "php/actions/theme/deletethemes.php",
                "?".GET_PAGE."=".PAGE_MAIN,
                Array(
                    "SUCCESS" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_MANAGE_THEMES, MESSAGE_SUCCESS, "Themes deleted."),
                    "FAILURE" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_MANAGE_THEMES, MESSAGE_WARNING, "One or more themes couldn't be deleted."),
                    "NO_THEMES_SELECTED" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_MANAGE_THEMES, MESSAGE_WARNING, "You must select at least one theme to delete."),
                    "NOT_AUTHORIZED" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_MAIN, MESSAGE_ERROR, "Only admins can perform this action."),
                    "NOT_LOGGED_IN" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_LOGIN, MESSAGE_WARNING, "Not logged in."),
                )
            ),
            new SiteActionModel(
                ACTION_BAN_THEME,
                "php/actions/theme/bantheme.php",
                "?".GET_PAGE."=".PAGE_MAIN,
                Array(
                    "SUCCESS" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_MANAGE_THEMES, MESSAGE_SUCCESS, "Theme banned."),
                    "INVALID_THEME" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_MANAGE_THEMES, MESSAGE_WARNING, "Invalid theme."),
                    "THEME_DOES_NOT_EXIST" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_MANAGE_THEMES, MESSAGE_WARNING, "Theme does not exist."),
                    "NOT_AUTHORIZED" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_MAIN, MESSAGE_ERROR, "Only admins can perform this action."),
                    "NOT_LOGGED_IN" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_LOGIN, MESSAGE_WARNING, "Not logged in."),
                )
            ),
            new SiteActionModel(
                ACTION_UNBAN_THEME,
                "php/actions/theme/unbantheme.php",
                "?".GET_PAGE."=".PAGE_MAIN,
                Array(
                    "SUCCESS" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_MANAGE_THEMES, MESSAGE_SUCCESS, "Theme unbanned."),
                    "INVALID_THEME" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_MANAGE_THEMES, MESSAGE_WARNING, "Invalid theme."),
                    "THEME_DOES_NOT_EXIST" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_MANAGE_THEMES, MESSAGE_WARNING, "Theme does not exist"),
                    "NOT_AUTHORIZED" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_MAIN, MESSAGE_ERROR, "Only admins can perform this action."),
                    "NOT_LOGGED_IN" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_LOGIN, MESSAGE_WARNING, "Not logged in."),
                )
            ),
            new SiteActionModel(
                ACTION_DOWNLOAD_DB,
                "php/actions/db/downloaddb.php",
                "?".GET_PAGE."=".PAGE_MAIN,
                Array(

                )
            ),
            new SiteActionModel(
                ACTION_ADMIN_VOTE,
                "php/actions/adminvote/adminvote.php",
                "?".GET_PAGE."=".PAGE_MAIN,
                Array(
                    "SUCESS_UPDATE" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_EDIT_USERS, MESSAGE_SUCCESS, "Admin vote updated."),
                    "SUCESS_INSERT" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_EDIT_USERS, MESSAGE_SUCCESS, "Admin vote cast."),
                    "INVALID_VOTE_TYPE" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_EDIT_USERS, MESSAGE_WARNING, "Invalid vote type."),
                    "NOT_AUTHORIZED" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_MAIN, MESSAGE_ERROR, "Only admins can perform this action."),
                    "NOT_LOGGED_IN" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_LOGIN, MESSAGE_WARNING, "Not logged in."),
                )
            ),
            new SiteActionModel(
                ACTION_NEW_PLAYFORM,
                "php/actions/platform/newplatform.php",
                "?".GET_PAGE."=".PAGE_EDIT_PLATFORMS,
                Array(
                    "SUCCESS_PLATFORM_ADDED" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_EDIT_PLATFORMS, MESSAGE_SUCCESS, "Platform added."),
                    "ICON_FAILED_TO_UPLOAD" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_EDIT_PLATFORMS, MESSAGE_ERROR, "Icon failed to uplaod."),
                    "ICON_WRONG_FILE_TYPE" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_EDIT_PLATFORMS, MESSAGE_WARNING, "Icon file type incorrect: Must be a png."),
                    "ICON_TOO_BIG" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_EDIT_PLATFORMS, MESSAGE_WARNING, "Uploaded image is too big (max 20kB)."),
                    "ICON_NOT_AN_IMAGE" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_EDIT_PLATFORMS, MESSAGE_WARNING, "Uploaded icon is not an image."),
                    "DUPLICATE_PLATFORM_NAME" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_EDIT_PLATFORMS, MESSAGE_WARNING, "Platform already exists."),
                    "MISSING_PLATFORM_NAME" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_EDIT_PLATFORMS, MESSAGE_WARNING, "Platform name must not be blank."),
                    "NOT_AUTHORIZED" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_MAIN, MESSAGE_ERROR, "Only admins can perform this action."),
                    "NOT_LOGGED_IN" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_LOGIN, MESSAGE_WARNING, "Not logged in."),
                )
            ),
            new SiteActionModel(
                ACTION_EDIT_PLATFORM,
                "php/actions/platform/editplatform.php",
                "?".GET_PAGE."=".PAGE_EDIT_PLATFORMS,
                Array(
                    "SUCCESS_PLATFORM_EDITED" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_EDIT_PLATFORMS, MESSAGE_SUCCESS, "Platform edited."),
                    "ICON_WRONG_FILE_TYPE" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_EDIT_PLATFORMS, MESSAGE_WARNING, "Icon file type incorrect: Must be a png."),
                    "ICON_TOO_BIG" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_EDIT_PLATFORMS, MESSAGE_WARNING, "Uploaded image is too big (max 20kB)."),
                    "ICON_NOT_AN_IMAGE" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_EDIT_PLATFORMS, MESSAGE_WARNING, "Uploaded icon is not an image."),
                    "UNKNOWN_PLATFORM" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_EDIT_PLATFORMS, MESSAGE_WARNING, "Unknown platform."),
                    "DUPLICATE_PLATFORM_NAME" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_EDIT_PLATFORMS, MESSAGE_WARNING, "Another platform with that name already exists."),
                    "MISSING_PLATFORM_NAME" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_EDIT_PLATFORMS, MESSAGE_WARNING, "Platform name must not be blank."),
                    "NOT_AUTHORIZED" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_MAIN, MESSAGE_ERROR, "Only admins can perform this action."),
                    "NOT_LOGGED_IN" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_LOGIN, MESSAGE_WARNING, "Not logged in."),
                )
            ),
            new SiteActionModel(
                ACTION_DELETE_PLATFORM,
                "php/actions/platform/deleteplatform.php",
                "?".GET_PAGE."=".PAGE_EDIT_PLATFORMS,
                Array(
                    "SUCCESS_PLATFORM_SOFT_DELETED" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_EDIT_PLATFORMS, MESSAGE_SUCCESS, "Platform soft deleted."),
                    "UNKNOWN_PLATFORM" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_EDIT_PLATFORMS, MESSAGE_WARNING, "Unknown platform."),
                    "NOT_AUTHORIZED" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_MAIN, MESSAGE_ERROR, "Only admins can perform this action."),
                    "NOT_LOGGED_IN" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_LOGIN, MESSAGE_WARNING, "Not logged in."),
                )
            ),
            new SiteActionModel(
                ACTION_UNDELETE_PLATFORM,
                "php/actions/platform/undeleteplatform.php",
                "?".GET_PAGE."=".PAGE_EDIT_PLATFORMS,
                Array(
                    "SUCCESS_PLATFORM_RESTORED" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_EDIT_PLATFORMS, MESSAGE_SUCCESS, "Platform restored."),
                    "UNKNOWN_PLATFORM" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_EDIT_PLATFORMS, MESSAGE_WARNING, "Unknown platform."),
                    "NOT_AUTHORIZED" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_MAIN, MESSAGE_ERROR, "Only admins can perform this action."),
                    "NOT_LOGGED_IN" => new SiteActionResultModel("?".GET_PAGE."=".PAGE_LOGIN, MESSAGE_WARNING, "Not logged in."),
                )
            )
        );

        StopTimer("LoadSiteActions");
        return $actions;
    }
}

?>