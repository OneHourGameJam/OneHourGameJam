<?php

//Changes data about the logged in user
function ChangeUserData($displayName, $twitterHandle, $emailAddress, $bio){
	global $users, $loggedInUser, $dbConn;

	$loggedInUser = IsLoggedIn();

	//Authorize user (is admin)
	if($loggedInUser === false){
		AddAuthorizationWarning("Not logged in.", false);
		return;
	}

	//Validate values
	if(!$displayName || strlen($displayName) <= 0 || strlen($displayName) > 50){
		AddDataWarning("Display name must be between 0 and 50 characters long", false);
		return;
	}

	//Validate email address
	if($emailAddress != "" && !filter_var($emailAddress, FILTER_VALIDATE_EMAIL)) {
		AddDataWarning("Provided email address is not valid", false);
		return;
	}

	$displayNameClean = mysqli_real_escape_string($dbConn, $displayName);
	$twitterHandleClean = mysqli_real_escape_string($dbConn, $twitterHandle);
	$emailAddressClean = mysqli_real_escape_string($dbConn, $emailAddress);
	$bioClean = mysqli_real_escape_string($dbConn, CleanHtml($bio));
	$usernameClean = mysqli_real_escape_string($dbConn, $loggedInUser["username"]);

	$sql = "
		UPDATE user
		SET
		user_display_name = '$displayNameClean',
		user_twitter = '$twitterHandleClean',
		user_email = '$emailAddressClean',
		user_bio = '$bioClean'
		WHERE user_username = '$usernameClean';
	";
	$data = mysqli_query($dbConn, $sql);
	$sql = "";

	LoadUsers();
	$loggedInUser = IsLoggedIn(TRUE);
}

if(IsLoggedIn()){
    $displayName = $_POST["displayname"];
    $twitterHandle = $_POST["twitterhandle"];
    $emailAddress = $_POST["emailaddress"];
    $bio = $_POST["bio"];

    ChangeUserData($displayName, $twitterHandle, $emailAddress, $bio);
}
$page = "usersettings";

?>