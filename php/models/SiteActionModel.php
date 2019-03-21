<?php

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

    function __construct(&$config) {
        $this->SiteActionModels = $this->LoadSiteActions($config);
    }

    function LoadSiteActions(&$config){
        AddActionLog("LoadSiteActions");
        StartTimer("LoadSiteActions");
        
        //Actions data: The data in this list governs how site actions are performed
        $actions = Array(
            new SiteActionModel(
                "login",
                "php/actions/authentication/login.php",
                "?page=main",
                Array(
                    "SUCCESS" => new SiteActionResultModel("?page=main", "success", "Logged in successfully"),
                    "INVALID_PASSWORD_LENGTH" => new SiteActionResultModel("?page=login", "warning", "Incorrect password length. Must be between ".$config["MINIMUM_PASSWORD_LENGTH"]->Value." and ".$config["MAXIMUM_PASSWORD_LENGTH"]->Value." characters long."),
                    "INVALID_USERNAME_LENGTH" => new SiteActionResultModel("?page=login", "warning", "Incorrect username length. Must be between ".$config["MINIMUM_USERNAME_LENGTH"]->Value." and ".$config["MAXIMUM_USERNAME_LENGTH"]->Value." characters long."),
                    "USERNAME_ALREADY_REGISTERED" => new SiteActionResultModel("?page=login", "error", "There is already a user with that username. Please log in or choose another."),
                    "USER_DOES_NOT_EXIST" => new SiteActionResultModel("?page=login", "error", "The user does not exist."),
                    "INCORRECT_PASSWORD" => new SiteActionResultModel("?page=login", "warning", "Incorrect username/password combination."),
                )
            ),
            new SiteActionModel(
                "logout",
                "php/actions/authentication/logout.php",
                "?page=main",
                Array(
                    "SUCCESS" => new SiteActionResultModel("?page=main", "success", "Logged out successfully")
                )
            ),
            new SiteActionModel(
                "submit",
                "php/actions/games/submit.php",
                "?page=main",
                Array(
                    "SUCCESS_ENTRY_ADDED" => new SiteActionResultModel("?page=main", "success", "Game Added."),
                    "SUCCESS_ENTRY_UPDATED" => new SiteActionResultModel("?page=main", "success", "Game Updated."),
                    "NOT_LOGGED_IN" => new SiteActionResultModel("?page=login", "warning", "Not Logged In."),
                    "MISSING_GAME_NAME" => new SiteActionResultModel("?page=submit", "warning", "Missing Game Name."),
                    "INVALID_GAME_URL" => new SiteActionResultModel("?page=submit", "warning", "Invalid Game URL."),
                    "INVALID_DESCRIPTION" => new SiteActionResultModel("?page=submit", "warning", "Missing Description."),
                    "INVALID_JAM_NUMBER" => new SiteActionResultModel("?page=submit", "error", "Invalid Jam Number, please contact administrators."),
                    "NO_JAM_TO_SUBMIT_TO" => new SiteActionResultModel("?page=submit", "error", "There is no active jam to submit to, please contact administrators."),
                    "INVALID_COLOR" => new SiteActionResultModel("?page=submit", "warning", "The selected color is not valid."),
                    "SCREENSHOT_NOT_AN_IMAGE" => new SiteActionResultModel("?page=submit", "warning", "The uploaded screenshot is not an image."),
                    "SCREENSHOT_TOO_BIG" => new SiteActionResultModel("?page=submit", "warning", "The uploaded screenshot is too big."),
                    "SCREENSHOT_WRONG_FILE_TYPE" => new SiteActionResultModel("?page=submit", "warning", "Screenshot is not of a valid file type."),
                    "CANNOT_SUBMIT_TO_PAST_JAM" => new SiteActionResultModel("?page=submit", "error", "Cannot submit to a past jam, please contact administrators."),
                )
            ),
            new SiteActionModel(
                "newjam",
                "php/actions/jam/newjam.php",
                "?page=main",
                Array(
                    "SUCCESS" => new SiteActionResultModel("?page=editcontent", "success", "Jam scheduled."),
                    "INVALID_TIME" => new SiteActionResultModel("?page=newjam", "warning", "Time is not valid."),
                    "INVALID_DATE" => new SiteActionResultModel("?page=newjam", "warning", "Date is not valid."),
                    "INVALID_THEME" => new SiteActionResultModel("?page=newjam", "warning", "Theme is not valid."),
                    "INVALID_JAM_NUMBER" => new SiteActionResultModel("?page=newjam", "error", "Jam number is not valid."),
                    "NOT_AUTHORIZED" => new SiteActionResultModel("?page=main", "error", "Only admins can perform this action."),
                    "NOT_LOGGED_IN" => new SiteActionResultModel("?page=login", "warning", "Not logged in."),
                )
            ),
            new SiteActionModel(
                "deletejam",
                "php/actions/jam/deletejam.php",
                "?page=main",
                Array(
                    "SUCCESS" => new SiteActionResultModel("?page=editcontent", "success", "Jam Deleted."),
                    "NO_JAMS_EXIST" => new SiteActionResultModel("?page=editcontent", "error", "No jams exist."),
                    "INVALID_JAM_ID" => new SiteActionResultModel("?page=editcontent", "error", "Invalid jam id."),
                    "CANNOT_DELETE_JAM" => new SiteActionResultModel("?page=editcontent", "error", "Cannot delete jam."),
                    "NOT_AUTHORIZED" => new SiteActionResultModel("?page=editcontent", "error", "Only admins can perform this action."),
                )
            ),
            new SiteActionModel(
                "deleteentry",
                "php/actions/games/deleteentry.php",
                "?page=main",
                Array(
                    "SUCCESS" => new SiteActionResultModel("?page=editcontent", "success", "Entry deleted."),
                    "NO_JAMS_EXIST" => new SiteActionResultModel("?page=editcontent", "error", "No jams exist."),
                    "INVALID_JAM_ID" => new SiteActionResultModel("?page=editcontent", "error", "Invalid jam id."),
                    "CANNOT_DELETE_ENTRY" => new SiteActionResultModel("?page=editcontent", "error", "cannot delete entry."),
                    "NOT_AUTHORIZED" => new SiteActionResultModel("?page=editcontent", "error", "Only admins can perform this action."),
                )
            ),
            new SiteActionModel(
                "saveconfig",
                "php/actions/config/saveconfig.php",
                "?page=main",
                Array(
                    "SUCCESS" => new SiteActionResultModel("?page=config", "success", "Config updated."),
                    "NOT_AUTHORIZED" => new SiteActionResultModel("?page=main", "error", "Only admins can perform this action."),
                    "NO_CHANGE" => new SiteActionResultModel("?page=config", "warning", "No changes to config."),
                )
            ),
            new SiteActionModel(
                "saveassetedits",
                "php/actions/asset/saveassetedits.php",
                "?page=main",
                Array(
                    "SUCCESS_INSERTED" => new SiteActionResultModel("?page=assets", "success", "Asset added."),
                    "SUCCESS_UPDATED" => new SiteActionResultModel("?page=assets", "success", "Asset updated"),
                    "COULD_NOT_DETERMINE_URL" => new SiteActionResultModel("?page=assets", "error", "Could not find a URL for the asset. Please look at the assets folder on the web server."),
                    "UNLOADED_ASSET_TOO_BIG" => new SiteActionResultModel("?page=assets", "warning", "Asset too big."),
                    "COULD_NOT_FIND_VALID_FILE_NAME" => new SiteActionResultModel("?page=assets", "warning", "Could not find a file name for the asset. Please look at the assets folder on the web server."),
                    "INVALID_ASSET_TYPE" => new SiteActionResultModel("?page=assets", "warning", "Invalid asset type."),
                    "ASSET_TYPE_EMPTY" => new SiteActionResultModel("?page=assets", "warning", "Missing asset type."),
                    "INVALID_DESCRIPTION" => new SiteActionResultModel("?page=assets", "warning", "Invalid description."),
                    "INVALID_TITLE" => new SiteActionResultModel("?page=assets", "warning", "Invalid title."),
                    "INVALID_AUTHOR" => new SiteActionResultModel("?page=assets", "warning", "Invalid author - must match a username of a registered user."),
                    "AUTHOR_EMPTY" => new SiteActionResultModel("?page=assets", "warning", "missing author."),
                    "NOT_AUTHORIZED" => new SiteActionResultModel("?page=main", "error", "Only admins can perform this action."),
                )
            ),
            new SiteActionModel(
                "deleteasset",
                "php/actions/asset/deleteasset.php",
                "?page=main",
                Array(
                    "SUCCESS" => new SiteActionResultModel("?page=assets", "success", "Asset deleted."),
                    "ASSET_DOES_NOT_EXIST" => new SiteActionResultModel("?page=assets", "error", "asset does not exist."),
                    "NOT_AUTHORIZED" => new SiteActionResultModel("?page=assets", "error", "Only admins can perform this action."),
                )
            ),
            new SiteActionModel(
                "savejamedits",
                "php/actions/jam/savejamedits.php",
                "?page=main",
                Array(
                    "SUCCESS" => new SiteActionResultModel("?page=editcontent", "success", "Jam updated."),
                    "INVALID_TIME" => new SiteActionResultModel("?page=editcontent", "warning", "Invalid time."),
                    "INVALID_DATE" => new SiteActionResultModel("?page=editcontent", "warning", "Invalid date."),
                    "INVALID_THEME" => new SiteActionResultModel("?page=editcontent", "warning", "Invalid theme."),
                    "INVALID_JAM_NUMBER" => new SiteActionResultModel("?page=editcontent", "error", "Invalid jam number"),
                    "NOT_AUTHORIZED" => new SiteActionResultModel("?page=main", "error", "Only admins can perform this action."),
                    "NO_JAMS_EXIST" => new SiteActionResultModel("?page=editcontent", "error", "No jams exist."),
                    "INVALID_JAM_ID" => new SiteActionResultModel("?page=editcontent", "error", "Invalid jam id."),
                    "INVALID_COLOR" => new SiteActionResultModel("?page=editcontent", "warning", "Invalid colors."),
                )
            ),
            new SiteActionModel(
                "saveuseredits",
                "php/actions/user/saveuseredits.php",
                "?page=main",
                Array(
                    "SUCCESS" => new SiteActionResultModel("?page=editusers", "success", "User successfully edited"),
                    "USER_DOES_NOT_EXIST" => new SiteActionResultModel("?page=editusers", "error", "User does not exist."),
                    "INVALID_ISADMIN" => new SiteActionResultModel("?page=editusers", "error", "Invalid IsAdmin."),
                    "NOT_AUTHORIZED" => new SiteActionResultModel("?page=main", "error", "Only admins can perform this action."),
                )
            ),
            new SiteActionModel(
                "savenewuserpassword",
                "php/actions/user/savenewuserpassword.php",
                "?page=main",
                Array(
                    "SUCCESS" => new SiteActionResultModel("?page=editusers", "success", "Password Updated."),
                    "USER_DOES_NOT_EXIST" => new SiteActionResultModel("?page=editusers", "error", "User does not exist."),
                    "INVALID_PASSWORD_LENGTH" => new SiteActionResultModel("?page=editusers", "warning", "Incorrect password length. Must be between ".$config["MINIMUM_PASSWORD_LENGTH"]->Value." and ".$config["MAXIMUM_PASSWORD_LENGTH"]->Value." characters long."),
                    "PASSWORDS_DONT_MATCH" => new SiteActionResultModel("?page=editusers", "warning", "Passwords do not match."),
                    "NOT_AUTHORIZED" => new SiteActionResultModel("?page=main", "error", "Only admins can perform this action."),
                )
            ),
            new SiteActionModel(
                "changepassword",
                "php/actions/user/changepassword.php",
                "?page=main",
                Array(
                    "SUCCESS" => new SiteActionResultModel("?page=usersettings", "success", "Password Updated."),
                    "USER_DOES_NOT_EXIST" => new SiteActionResultModel("?page=usersettings", "error", "User does not exist."),
                    "INVALID_PASSWORD_LENGTH" => new SiteActionResultModel("?page=usersettings", "warning", "Incorrect password length. Must be between ".$config["MINIMUM_PASSWORD_LENGTH"]->Value." and ".$config["MAXIMUM_PASSWORD_LENGTH"]->Value." characters long."),
                    "PASSWORDS_DONT_MATCH" => new SiteActionResultModel("?page=usersettings", "warning", "Passwords do not match."),
                    "INCORRECT_PASSWORD" => new SiteActionResultModel("?page=usersettings", "warning", "Old password is not correct."),
                    "NOT_LOGGED_IN" => new SiteActionResultModel("?page=login", "warning", "Not logged in."),
                )
            ),
            new SiteActionModel(
                "saveuserchanges",
                "php/actions/user/saveuserchanges.php",
                "?page=main",
                Array(
                    "SUCCESS" => new SiteActionResultModel("?page=usersettings", "success", "User settings updated."),
                    "INVALID_EMAIL" => new SiteActionResultModel("?page=usersettings", "warning", "Email is not valid."),
                    "INVALID_DISPLAY_NAME" => new SiteActionResultModel("?page=usersettings", "warning", "Incorrect display name length. Must be between ".$config["MINIMUM_DISPLAY_NAME_LENGTH"]->Value." and ".$config["MAXIMUM_DISPLAY_NAME_LENGTH"]->Value." characters long."),
                    "NOT_LOGGED_IN" => new SiteActionResultModel("?page=login", "warning", "Not logged in."),
                )
            ),
            new SiteActionModel(
                "savenewtheme",
                "php/actions/theme/savenewtheme.php",
                "?page=main",
                Array(
                    "SUCCESS" => new SiteActionResultModel("?page=themes", "success", "Theme added."),
                    "THEME_ALREADY_SUGGESTED" => new SiteActionResultModel("?page=themes", "warning", "Theme is already suggested."),
                    "INVALID_THEME" => new SiteActionResultModel("?page=themes", "warning", "Theme is not valid."),
                    "NOT_LOGGED_IN" => new SiteActionResultModel("?page=login", "warning", "Not logged in."),
                    "THEME_RECENTLY_USED" => new SiteActionResultModel("?page=themes", "warning", "Theme has been used in a recent jam."),
                    "TOO_MANY_THEMES" => new SiteActionResultModel("?page=themes", "warning", "You can only submit ".$config["THEMES_PER_USER"]->Value." themes. Please delete past themes to submit again.")
                )
            ),
            new SiteActionModel(
                "deletetheme",
                "php/actions/theme/deletetheme.php",
                "?page=main",
                Array(
                    "SUCCESS_THEMES" => new SiteActionResultModel("?page=themes", "success", "Theme deleted."),
                    "SUCCESS_MANAGETHEMES" => new SiteActionResultModel("?page=managethemes", "success", "Theme deleted."),
                    "INVALID_THEME" => new SiteActionResultModel("?page=themes", "warning", "Theme is not valid."),
                    "THEME_DOES_NOT_EXIST" => new SiteActionResultModel("?page=themes", "warning", "Theme does not exist."),
                    "NOT_AUTHORIZED" => new SiteActionResultModel("?page=main", "error", "Only admins can perform this action."),
                    "NOT_LOGGED_IN" => new SiteActionResultModel("?page=login", "warning", "Not logged in."),
                )
            ),
            new SiteActionModel(
                "deletethemes",
                "php/actions/theme/deletethemes.php",
                "?page=main",
                Array(
                    "SUCCESS" => new SiteActionResultModel("?page=managethemes", "success", "Themes deleted."),
                    "FAILURE" => new SiteActionResultModel("?page=managethemes", "warning", "One or more themes couldn't be deleted."),
                    "NO_THEMES_SELECTED" => new SiteActionResultModel("?page=managethemes", "warning", "You must select at least one theme to delete."),
                    "NOT_AUTHORIZED" => new SiteActionResultModel("?page=main", "error", "Only admins can perform this action."),
                    "NOT_LOGGED_IN" => new SiteActionResultModel("?page=login", "warning", "Not logged in."),
                )
            ),
            new SiteActionModel(
                "bantheme",
                "php/actions/theme/bantheme.php",
                "?page=main",
                Array(
                    "SUCCESS" => new SiteActionResultModel("?page=managethemes", "success", "Theme banned."),
                    "INVALID_THEME" => new SiteActionResultModel("?page=managethemes", "warning", "Invalid theme."),
                    "THEME_DOES_NOT_EXIST" => new SiteActionResultModel("?page=managethemes", "warning", "Theme does not exist."),
                    "NOT_AUTHORIZED" => new SiteActionResultModel("?page=main", "error", "Only admins can perform this action."),
                    "NOT_LOGGED_IN" => new SiteActionResultModel("?page=login", "warning", "Not logged in."),
                )
            ),
            new SiteActionModel(
                "unbantheme",
                "php/actions/theme/unbantheme.php",
                "?page=main",
                Array(
                    "SUCCESS" => new SiteActionResultModel("?page=managethemes", "success", "Theme unbanned."),
                    "INVALID_THEME" => new SiteActionResultModel("?page=managethemes", "warning", "Invalid theme."),
                    "THEME_DOES_NOT_EXIST" => new SiteActionResultModel("?page=managethemes", "warning", "Theme does not exist"),
                    "NOT_AUTHORIZED" => new SiteActionResultModel("?page=main", "error", "Only admins can perform this action."),
                    "NOT_LOGGED_IN" => new SiteActionResultModel("?page=login", "warning", "Not logged in."),
                )
            ),
            new SiteActionModel(
                "downloaddb",
                "php/actions/db/downloaddb.php",
                "?page=main",
                Array(

                )
            ),
            new SiteActionModel(
                "adminvote",
                "php/actions/adminvote/adminvote.php",
                "?page=main",
                Array(
                    "SUCESS_UPDATE" => new SiteActionResultModel("?page=editusers", "success", "Admin vote updated."),
                    "SUCESS_INSERT" => new SiteActionResultModel("?page=editusers", "success", "Admin vote cast."),
                    "INVALID_VOTE_TYPE" => new SiteActionResultModel("?page=editusers", "warning", "Invalid vote type."),
                    "NOT_AUTHORIZED" => new SiteActionResultModel("?page=main", "error", "Only admins can perform this action."),
                    "NOT_LOGGED_IN" => new SiteActionResultModel("?page=login", "warning", "Not logged in."),
                )
            )
        );

        StopTimer("LoadSiteActions");
        return $actions;
    }
}

?>