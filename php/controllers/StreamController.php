<?php

class StreamController{
	public static function InitStream(&$configData){
		AddActionLog("InitStream");
		StartTimer("InitStream");

		$streamData = Array();

		$timeDiff = 0;
		if(file_exists("cache/twitch_stream.json")){
			//Cache twitch API response
			$streamData = json_decode(file_get_contents("cache/twitch_stream.json"), true);
			$timeDiff = time() - intval($streamData["time_last_updated"]);
		}

		if($timeDiff > intval($configData->ConfigModels["TWITCH_API_STREAM_UPDATE_FREQUENCY"]->Value)){
			//Enough time has passed for an update from the API, fetch it.

			//First overwrite the currently saved time_last_updated, so that if there is a lot of load on the twitch API and reponses are slow, only one site user has to wait.
			$streamData["time_last_updated"] = time();
			file_put_contents("cache/twitch_stream.json", json_encode($streamData));

			//Fetch API response using CURL, because that was the easiest to copy-paste in :)
			$channelsApi = 'https://api.twitch.tv/helix/streams?user_login=';
			$channelName = $configData->ConfigModels["STREAMER_TWITCH_NAME"]->Value;
			$clientId = $configData->ConfigModels["TWITCH_CLIENT_ID"]->Value;
			$ch = curl_init();

			curl_setopt_array($ch, array(
				CURLOPT_HTTPHEADER => array(
					'Client-ID: ' . $clientId
				),
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_URL => $channelsApi . $channelName
			));

			$response = curl_exec($ch);
			curl_close($ch);

			//Log current time so next update does not happen until enough time has passed.
			$streamData = json_decode($response, true);
			$streamData["time_last_updated"] = time();
			file_put_contents("cache/twitch_stream.json", json_encode($streamData));
		}

		StopTimer("InitStream");
		return $streamData;
	}
}

?>
