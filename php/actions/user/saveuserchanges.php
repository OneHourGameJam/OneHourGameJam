<?php

//Changes data about the logged in user
function ChangeUserData($displayName, $twitterHandle, $emailAddress, $bio, $preferences){
	global $loggedInUser, $configData, $userDbInterface;

	//Authorize user
	if($loggedInUser === false){
		return "NOT_LOGGED_IN";
	}

	//Validate values
	if(!$displayName || strlen($displayName) < $configData->ConfigModels[CONFIG_MINIMUM_DISPLAY_NAME_LENGTH]->Value || strlen($displayName) > $configData->ConfigModels[CONFIG_MAXIMUM_DISPLAY_NAME_LENGTH]->Value){
		return "INVALID_DISPLAY_NAME";
	}

	//Validate email address
	if($emailAddress != "" && !filter_var($emailAddress, FILTER_VALIDATE_EMAIL)) {
		return "INVALID_EMAIL";
	}

	$userDbInterface->Update($loggedInUser->Id, $displayName, $twitterHandle, $emailAddress, CleanHtml($bio), $preferences);

	return "SUCCESS";
}

function PerformAction(&$loggedInUser){
	global $_POST, $userPreferenceSettings;
	
	if($loggedInUser !== false){
		$displayName = $_POST[FORM_SAVEUSERCHANGES_DISPLAY_NAME];
		$twitterHandle = $_POST[FORM_SAVEUSERCHANGES_TWITTER_HANDLE];
		$emailAddress = $_POST[FORM_SAVEUSERCHANGES_EMAIL_ADDRESS];
		$bio = $_POST[FORM_SAVEUSERCHANGES_BIO];

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