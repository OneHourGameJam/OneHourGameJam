<?php

//Changes data about the logged in user
function ChangeUserData($displayName, $twitterHandle, $emailAddress, $bio, $preferences){
	global $loggedInUser, $dbConn, $configData;

	//Authorize user
	if($loggedInUser === false){
		return "NOT_LOGGED_IN";
	}

	//Validate values
	if(!$displayName || strlen($displayName) < $configData->ConfigModels["MINIMUM_DISPLAY_NAME_LENGTH"]->Value || strlen($displayName) > $configData->ConfigModels["MAXIMUM_DISPLAY_NAME_LENGTH"]->Value){
		return "INVALID_DISPLAY_NAME";
	}

	//Validate email address
	if($emailAddress != "" && !filter_var($emailAddress, FILTER_VALIDATE_EMAIL)) {
		return "INVALID_EMAIL";
	}
	
	$cleanDisplayName = mysqli_real_escape_string($dbConn, $displayName);
	$cleanTwitterHandle = mysqli_real_escape_string($dbConn, $twitterHandle);
	$cleanEmailAddress = mysqli_real_escape_string($dbConn, $emailAddress);
	$cleanBio = mysqli_real_escape_string($dbConn, CleanHtml($bio));
	$cleanPreferences = mysqli_real_escape_string($dbConn, $preferences);
	$cleanUserId = mysqli_real_escape_string($dbConn, $loggedInUser->Id);

	$sql = "
		UPDATE user
		SET
		user_display_name = '$cleanDisplayName',
		user_twitter = '$cleanTwitterHandle',
		user_email = '$cleanEmailAddress',
		user_bio = '$cleanBio',
		user_preferences = $cleanPreferences
		WHERE user_id = $cleanUserId;
	";
	$data = mysqli_query($dbConn, $sql);
	$sql = "";

	return "SUCCESS";
}

function PerformAction(&$loggedInUser){
	global $_POST, $userPreferenceSettings;
	
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