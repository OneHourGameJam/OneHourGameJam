<?php

class StreamPresenter{
	public static function RenderStream(&$streamData, &$configData){
		AddActionLog("RenderStream");
		StartTimer("RenderStream");
	
		$streamViewModel = new StreamViewModel();

		if(isset($streamData) && isset($streamData["data"]) && count($streamData["data"]) > 0){
			if(isset($streamData["data"][0]["type"]) && $streamData["data"][0]["type"] == "live"){
				$streamViewModel->IS_STREAM_ACTIVE = 1;
				$streamViewModel->STREAMER_CHANNEL = $configData->ConfigModels["STREAMER_TWITCH_NAME"]->Value;
			}
		}
	
		StopTimer("RenderStream");
		return $streamViewModel;
	}
}

?>