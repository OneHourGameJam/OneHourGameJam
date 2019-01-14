<?php

function InitStream(){
	global $config, $dictionary;
	StartTimer("InitStream");

	$timeDiff = 0;
	if(file_exists("cache/twitch_stream.json")){
		//Cache twitch API response
		$data = json_decode(file_get_contents("cache/twitch_stream.json"), true);
		$timeDiff = time() - intval($data["time_last_updated"]);
	}

	if($timeDiff > intval($config["TWITCH_API_STREAM_UPDATE_FREQUENCY"]["VALUE"])){
		//Enough time has passed for an update from the API, fetch it.

		//First overwrite the currently saved time_last_updated, so that if there is a lot of load on the twitch API and reponses are slow, only one site user has to wait.
		$data["time_last_updated"] = time();
		file_put_contents("cache/twitch_stream.json", json_encode($data));

		//Fetch API response using CURL, because that was the easiest to copy-paste in :)
		$channelsApi = 'https://api.twitch.tv/kraken/streams/';
		$channelName = $config["STREAMER_TWITCH_NAME"]["VALUE"];
		$clientId = $config["TWITCH_CLIENT_ID"]["VALUE"];
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
		$data = json_decode($response, true);
		$data["time_last_updated"] = time();
		file_put_contents("cache/twitch_stream.json", json_encode($data));
	}

	if(isset($data) && isset($data["stream"]) && $data["stream"] != null && count($data["stream"]) > 0){
		$dictionary["IS_STREAM_ACTIVE"] = 1;
		$dictionary["STREAMER_CHANNEL"] = $config["STREAMER_TWITCH_NAME"]["VALUE"];
	}

	StopTimer("InitStream");
}

?>