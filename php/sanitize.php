<?php

// Validate and Sanitize Things
// - Validate functions return whether they are valid or not
// - Sanitize functions return a legal string, or false on failure


// Given a URL, returns a valid (escaped) URL, or false if it's bad. //
//function validate_url($url) {
function SanitizeURL($url) {
	// Step 0. Confirm that the input is UTF-8 encoded.
	if(!mb_check_encoding($url, 'UTF-8')){
		// ERROR: Expected URL in UTF-8 encoding.
		return false;
	}
	
	// Step 1. Trim whitespace.
	$url = trim($url);
	
	// Step 2. Confirm that it's a valid URL (i.e. has a scheme).
	$protocols = [
		// Standard URLs (scheme://path/?query).
		'http', 'https'
	];
	
	// NOTE: parse_url isn't multibyte aware, so you should only rely on scheme and the existence of other members.
	$parsed = parse_url($url);
	$protocol = false;
	// If a scheme is set //
	if(isset($parsed['scheme'])){
		foreach($protocols as $item){
			if($item === strtolower($parsed['scheme'])){
				$protocol = $item;
				break;
			}
		}
	}else{
		// If no scheme is set, but there is a path, assume it's http.
		if(isset($parsed['path'])){
			$url = 'http://'. $url;
			$protocol = 'http';
		}
	}
	
	if($protocol === false){
		// ERROR: Unknown URL scheme.
		return false;
	}
	// We now know the protocol. It will always be lower case.
	
	// Step 3. Escape URL.
	$url = htmlspecialchars($url, ENT_QUOTES | ENT_HTML5, 'UTF-8', false);
	
	return $url;
}

?>