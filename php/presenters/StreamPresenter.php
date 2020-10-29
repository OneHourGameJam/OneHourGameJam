<?php

class StreamPresenter{
	public static function RenderStream(&$streamData){
		AddActionLog("RenderStream");
		StartTimer("RenderStream");
	
		$streamViewModel = new StreamViewModel();

		if(isset($streamData) && isset($streamData["data"]) && count($streamData["data"]) > 0){
			if(isset($streamData["data"][0]["type"]) && $streamData["data"][0]["type"] == "live"){
				$streamViewModel->IS_STREAM_ACTIVE = 1;
				$streamViewModel->STREAMER_CHANNEL = $streamData["data"][0]["user_name"];
			}
		}
	
		StopTimer("RenderStream");
		return $streamViewModel;
	}
}

?>