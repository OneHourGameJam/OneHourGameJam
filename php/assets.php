<?php

function LoadAssets(){
	global $dictionary, $assets, $dbConn;
	
	//Clear public lists which get updated by this function
	$dictionary["assets"] = Array();
	$assets = Array();
	
	//Fill list of themes - will return same row multiple times (once for each valid themevote_type)
	$sql = "
		SELECT asset_id, asset_author, asset_title, asset_description, asset_type, asset_content
		FROM asset
		WHERE asset_deleted != 1;
	";
	$data = mysqli_query($dbConn, $sql);
	$sql = "";
	
	//Fill dictionary with non-banned themes
	while($asset = mysqli_fetch_array($data)){
		$id = $asset["asset_id"];
		$author = $asset["asset_author"];
		$title = $asset["asset_title"];
		$description = $asset["asset_description"];
		$type = $asset["asset_type"];
		$content = $asset["asset_content"];
		
		$a = Array("id" => $id, "author" => $author, "title" => $title, "description" => $description, "type" => $type, "content" => $content );
		
		switch($type){
			case "AUDIO":
				$a["is_audio"] = 1;
			break;
			case "IMAGE":
				$a["is_image"] = 1;
			break;
			case "TEXT":
				$a["is_text"] = 1;
			break;
			case "LINK":
				$a["is_link"] = 1;
			break;
			default:
				$a["is_other"] = 1;
			break;
		}
		
		$assets[] = $a;
	}
	
	$dictionary["assets"] = $assets;
}

?>