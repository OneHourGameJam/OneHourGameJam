<?php

//Fills the list of suggested themes
function LoadThemes(){
	global $themes, $dictionary, $loggedInUser;
	
	//Clear relevant lists
	$themes = Array();
	$dictionary["suggested_themes"] = Array();
	
	//Fill list of themes
	$themes = json_decode(file_get_contents("data/themes.json"), true);
	
	//Fill dictionary with non-banned themes
	foreach($themes as $i => $theme){
		if($theme["banned"] == 0){
			$themeData = Array("theme" => htmlspecialchars($theme["theme"], ENT_QUOTES), "author" => $theme["author"]);
			$themeData["theme_visible"] = 1;
			$dictionary["suggested_themes"][] = $themeData;
		}
	}
	
	//Fill dictionary with themes
	foreach($themes as $i => $theme){
		if($theme["banned"] != 0){
			$themeData = Array("theme" => htmlspecialchars($theme["theme"], ENT_QUOTES), "author" => $theme["author"]);
			$themeData["banned"] = 1;
			if(IsAdmin()){
				$themeData["theme_visible"] = 1;
			}
			$dictionary["suggested_themes"][] = $themeData;
		}
	}
}

//Add a suggested theme
function AddTheme($newTheme, $isBot){
	global $themes;
	
	if($isBot){
		$user = "bot";
	}else{
	
		//Authorize user (logged in)
		$user = IsLoggedIn();
		if($user === false){
			die("Not logged in.");
		}
	}
	
	$newTheme = trim($newTheme);
	if($newTheme == ""){
		die("Theme is blank");
	}
	
	foreach($themes as $i => $theme){
		if(strtolower($theme["theme"]) == strtolower($newTheme)){
			//Theme is already suggested
			die("This theme has already been suggested");
			return;
		}
	}
	
	$themes[] = Array("theme" => $newTheme, "author" => $user["username"]);
	file_put_contents("data/themes.json", json_encode($themes));
	LoadThemes();
}

//Removes a suggested theme
function RemoveTheme($removedTheme){
	global $themes;
	
	//Authorize user (logged in)
	$user = IsAdmin();
	if($user === false){
		die("Not logged in.");
	}
	
	//Authorize user (is admin)
	if(IsAdmin() === false){
		die("Only admins can delete themes.");
	}
	
	$removedTheme = trim($removedTheme);
	if($removedTheme == ""){
		die("Theme is blank");
	}
	
	foreach($themes as $i => $theme){
		if(strtolower($theme["theme"]) == strtolower($removedTheme)){
			//Theme is already suggested
			unset($themes[$i]);
			file_put_contents("data/themes.json", json_encode($themes));
			LoadThemes();
			return;
		}
	}
	
	die("This theme does not exist");
}

//Marks a suggested theme as banned
function BanTheme($bannedTheme){
	global $themes;
	
	//Authorize user (logged in)
	$user = IsAdmin();
	if($user === false){
		die("Not logged in.");
	}
	
	//Authorize user (is admin)
	if(IsAdmin() === false){
		die("Only admins can delete themes.");
	}
	
	$bannedTheme = trim($bannedTheme);
	if($bannedTheme == ""){
		die("Theme is blank");
	}
	
	foreach($themes as $i => $theme){
		if(strtolower($theme["theme"]) == strtolower($bannedTheme)){
			//Theme is already suggested
			$themes[$i]["banned"] = 1;
			file_put_contents("data/themes.json", json_encode($themes));
			LoadThemes();
			return;
		}
	}
	
	die("This theme does not exist");
}

//Unmarks a suggested theme as banned (unbans it)
function UnbanTheme($unbannedTheme){
	global $themes;
	
	//Authorize user (logged in)
	$user = IsAdmin();
	if($user === false){
		die("Not logged in.");
	}
	
	//Authorize user (is admin)
	if(IsAdmin() === false){
		die("Only admins can delete themes.");
	}
	
	$unbannedTheme = trim($unbannedTheme);
	if($unbannedTheme == ""){
		die("Theme is blank");
	}
	
	foreach($themes as $i => $theme){
		if(strtolower($theme["theme"]) == strtolower($unbannedTheme)){
			//Theme is already suggested
			unset($themes[$i]["banned"]);
			file_put_contents("data/themes.json", json_encode($themes));
			LoadThemes();
			return;
		}
	}
	
	die("This theme does not exist");
}



?>