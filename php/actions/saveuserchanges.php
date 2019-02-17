<?php

//Changes data about the logged in user
function ChangeUserData($displayName, $twitterHandle, $emailAddress, $bio, $preferences){
	global $users, $loggedInUser, $dbConn, $config;

	//Authorize user
	if($loggedInUser === false){
		return "NOT_LOGGED_IN";
	}

	//Validate values
	if(!$displayName || strlen($displayName) < $config["MINIMUM_DISPLAY_NAME_LENGTH"]["VALUE"] || strlen($displayName) > $config["MAXIMUM_DISPLAY_NAME_LENGTH"]["VALUE"]){
		return "INVALID_DISPLAY_NAME";
	}

	//Validate email address
	if($emailAddress != "" && !filter_var($emailAddress, FILTER_VALIDATE_EMAIL)) {
		return "INVALID_EMAIL";
	}
	
	$displayNameClean = mysqli_real_escape_string($dbConn, $displayName);
	$twitterHandleClean = mysqli_real_escape_string($dbConn, $twitterHandle);
	$emailAddressClean = mysqli_real_escape_string($dbConn, $emailAddress);
	$bioClean = mysqli_real_escape_string($dbConn, CleanHtml($bio));
	$preferencesClean = mysqli_real_escape_string($dbConn, $preferences);
	$usernameClean = mysqli_real_escape_string($dbConn, $loggedInUser["username"]);

	$sql = "
		UPDATE user
		SET
		user_display_name = '$displayNameClean',
		user_twitter = '$twitterHandleClean',
		user_email = '$emailAddressClean',
		user_bio = '$bioClean',
		user_preferences = $preferencesClean
		WHERE user_username = '$usernameClean';
	";
	$data = mysqli_query($dbConn, $sql);
	$sql = "";

	return "SUCCESS";
}

function PerformAction(&$loggedInUser){
	global $_POST;
	
	if($loggedInUser !== false){
		$displayName = $_POST["displayname"];
		$twitterHandle = $_POST["twitterhandle"];
		$emailAddress = $_POST["emailaddress"];
		$bio = $_POST["bio"];

		$preferenceValue = 0;
		foreach($userPreferenceSettings as $i => $preferenceSetting){
			$preferenceFlag = pow(2, $preferenceSetting["BIT_FLAG_EXPONENT"]);
			$preferenceKey = $preferenceSetting["PREFERENCE_KEY"];

			if(isset($_POST[$preferenceKey])){
				if($_POST[$preferenceKey] == "on"){
					$preferenceValue = $preferenceValue | $preferenceFlag;
				}
			}
		}

		return ChangeUserData($displayName, $twitterHandle, $emailAddress, $bio, $preferenceValue);
	}
}

?>