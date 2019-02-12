<?php

function LoadTools(){
	global $dbConn;
	AddActionLog("LoadTools");
	StartTimer("LoadTools");
	
	$tools = Array();

	$sql = "
		SELECT tool_id, tool_username, tool_title, tool_description, tool_url, tool_is_free, tool_price, tool_pricing_model, tool_intended_audience, tool_platforms, tool_category, tool_subcategory, tool_deleted
		FROM tool
	";
	$data = mysqli_query($dbConn, $sql);
	$sql = "";

	while($tool = mysqli_fetch_array($data)){
        $toolData = Array();

        $toolID = $tool["tool_id"];;
        $toolCategory = $tool["tool_category"];;
        $toolSubcategory = $tool["tool_subcategory"];;
        $intendedAudience = explode("|", $tool["tool_intended_audience"]);
        $platformKeyValuePairs = explode("|", $tool["tool_platforms"]);

        $toolData["id"] = $toolID;
        $toolData["username"] = $tool["tool_username"];
        $toolData["title"] = $tool["tool_title"];
        $toolData["description"] = $tool["tool_description"];
        $toolData["url"] = $tool["tool_url"];
        $toolData["is_free"] = $tool["tool_is_free"] == 1;
        $toolData["price"] = $tool["tool_price"];
        $toolData["pricing_model"] = $tool["tool_pricing_model"];
        $toolData["intended_audience"] = $intendedAudience;
        $toolData["platforms"] = Array();
        foreach($platformKeyValuePairs as $id => $platformKeyValuePair){
            $keyValuePair = explode("=", $platformKeyValuePair);
            if(count($keyValuePair) != 2){
                continue;
            }
            $platform = $keyValuePair[0];
            $platformSupport = $keyValuePair[1];
            $toolData["platforms"][$platform] = $platformSupport;
        }
        $toolData["category"] = $toolCategory;
        $toolData["subcategory"] = $toolSubcategory;
        $toolData["tool_deleted"] = $tool["tool_deleted"];

		$tools[$toolCategory][$toolSubcategory][$toolID] = $toolData;
    }
    
	StopTimer("LoadTools");
	return $tools;
}

function RenderTools(&$tools){
	AddActionLog("RenderTools");
	StartTimer("RenderTools");
	
	$render = Array();

	foreach($tools as $category => $categoryData){
        $categoryID = 0;
        if(isset($render["TOOL_CATEGORY"])){
            $categoryID = count($render["TOOL_CATEGORY"]);
        }
        $render["TOOL_CATEGORY"][$categoryID] = Array("CATEGORY_TITLE" => $category);
        foreach($categoryData as $subcategory => $subcategoryData){
            $subcategoryID = 0;
            if(isset($render["TOOL_CATEGORY"][$categoryID]["TOOL_SUBCATEGORY"])){
                $subcategoryID = count($render["TOOL_CATEGORY"][$categoryID]["TOOL_SUBCATEGORY"]);
            }
            $render["TOOL_CATEGORY"][$categoryID]["TOOL_SUBCATEGORY"][$subcategoryID] = Array("SUBCATEGORY_TITLE" => $subcategory);
            foreach($subcategoryData as $id => $toolData){
                $tool = Array();

                $tool["id"] = $toolData["id"];
                $tool["username"] = $toolData["username"];
                $tool["title"] = $toolData["title"];
                $tool["description"] = $toolData["description"];
                $tool["url"] = $toolData["url"];
                if($toolData["is_free"]){
                    $tool["is_free"] = 1;
                }
                $tool["price"] = $toolData["price"];
                $tool["pricing_model"] = $toolData["pricing_model"];

                if(array_search("BEGINNER", $toolData["intended_audience"]) !== false){
                    $tool["intended_audience_beginner"] = 1;
                }
                if(array_search("ENTHUSIAST", $toolData["intended_audience"]) !== false){
                    $tool["intended_audience_enthusiast"] = 1;
                }
                if(array_search("ADVANCED", $toolData["intended_audience"]) !== false){
                    $tool["intended_audience_advanced"] = 1;
                }

                foreach($toolData["platforms"] as $platform => $platformSupport){
                    $dictionaryKey = GeneratePlatformDictionaryKey($platform, $platformSupport);
                    $tool[$dictionaryKey] = 1;
                }

                if($toolData["tool_deleted"]){
                    $tool["tool_deleted"] = 1;
                }


                $render["TOOL_CATEGORY"][$categoryID]["TOOL_SUBCATEGORY"][$subcategoryID]["LIST"] = $tool;
            }
        }
    }
    
	StopTimer("RenderTools");
	return $render;
}

function GeneratePlatformDictionaryKey($platform, $platformSupport){
    $dictionaryKey = "platform_";
    switch($platform){
        case "WINDOWS":
            $dictionaryKey .= "windows_";
            break;
        case "LINUX":
            $dictionaryKey .= "linux_";
            break;
        case "MAC":
            $dictionaryKey .= "mac_";
            break;
        case "":
            break;
        default:
            die("Unknown platform for tool: ".$platform);
    }
    
    switch($platformSupport){
        case "GOOD":
            $dictionaryKey .= "good";
            break;
        case "WIP":
            $dictionaryKey .= "wip";
            break;
        case "BAD":
            $dictionaryKey .= "bad";
            break;
        case "":
            break;
        default:
            die("Unknown platform support for tool: ".$platformSupport);
    }

    return $dictionaryKey;
}
?>