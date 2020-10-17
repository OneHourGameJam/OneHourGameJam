<?php

class StreamController{
	public static function InitStream(&$configData){
		AddActionLog("InitStream");
		StartTimer("InitStream");

		if(file_exists("cache/twitch_stream.json")){
			//Cache twitch API response
			$streamData = json_decode(file_get_contents("cache/twitch_stream.json"), true);
			$timeLastUpdated = intval($streamData["time_last_updated"]);
		} else {
			$streamData = Array();
			$timeLastUpdated = 0;
		}

		$clientId = $configData->ConfigModels[CONFIG_TWITCH_CLIENT_ID]->Value;
		$clientSecret = $configData->ConfigModels[CONFIG_TWITCH_CLIENT_SECRET]->Value;
		$channelName = $configData->ConfigModels[CONFIG_STREAMER_TWITCH_NAME]->Value;

		if(!empty($clientId) && !empty($clientSecret) && !empty($channelName)){
			if(time() - $timeLastUpdated > intval($configData->ConfigModels[CONFIG_TWITCH_API_STREAM_UPDATE_FREQUENCY]->Value)){
				//Enough time has passed for an update from the API, fetch it.

				//First overwrite the currently saved time_last_updated, so that if there is a lot of load on the twitch API and reponses are slow, only one site user has to wait.
				$streamData["time_last_updated"] = time();
				file_put_contents("cache/twitch_stream.json", json_encode($streamData));

				// Fetch channel state using the Twitch API
				$token = self::GetTwitchAccessToken($clientId, $clientSecret);
				$streamDataResponse = self::CallTwitchApi("/helix/streams?user_login=" . $channelName, $clientId, $token);
				$streamData = json_decode($streamDataResponse, true);

				//Log current time so next update does not happen until enough time has passed.
				$streamData["time_last_updated"] = time();
				file_put_contents("cache/twitch_stream.json", json_encode($streamData));
			}
		}else{
			$streamData = Array();
			$streamData["time_last_updated"] = time();
		}

		StopTimer("InitStream");
		return $streamData;
	}

	private static function GetTwitchAccessToken($clientId, $clientSecret) {
		$url = "https://id.twitch.tv/oauth2/token"
			. "?client_id=" . $clientId
			. "&client_secret=" . $clientSecret
			. "&grant_type=client_credentials";
		$headers = array();

		$response = self::HttpRequest($url, $headers, array(CURLOPT_POST => true));
		$responseObject = json_decode($response);
		return $responseObject->access_token;
	}

	private static function CallTwitchApi($path, $clientId, $accessToken) {
		$url = "https://api.twitch.tv" . $path;
		$headers = array(
			"Client-ID: " . $clientId,
			"Authorization: Bearer " . $accessToken
		);

		return self::HttpRequest($url, $headers);
	}

	private static function HttpRequest($url, $headers, $curlOptions = array()) {
		$curlOptions = array(
				CURLOPT_HTTPHEADER => $headers,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_URL => $url
			) + $curlOptions;

		$ch = curl_init();
		curl_setopt_array($ch, $curlOptions);
		$response = curl_exec($ch);
		curl_close($ch);

		return $response;
	}
}

?>
