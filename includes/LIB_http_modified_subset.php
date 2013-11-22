<?php
$dir_includes = implode("/", explode("/", realpath(__FILE__), -1));
# fake
define("WEBBOT_NAME", "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/535.19 (KHTML, like Gecko) Chrome/18.0.1025.162 Safari/535.19");

# Length of time cURL will wait for a response (seconds)
define("CURL_TIMEOUT", 17200);

# Location of your cookie file. (Must be fully resolved local address)
#define("COOKIE_FILE", "c:\cookie.txt");
define("COOKIE_FILE", $dir_includes."/../cookies/webbots_http_cookie.txt");

# DEFINE METHOD CONSTANTS
define("HEAD", "HEAD");
define("GET",  "GET");
define("POST", "POST");

# DEFINE HEADER INCLUSION
define("EXCL_HEAD", FALSE);
define("INCL_HEAD", TRUE);

$timer = microtime(true);
function http($target, $ref, $method, $data_array, $incl_head, $cookies="") {
#	global $access_counter;
#	global $ch;
	global $timer;

	$target = str_replace("limit=25", "limit=1000", $target);

	$ch = curl_init();
#	$access_counter++;

	# Prcess data, if presented
	if(is_array($data_array)) {
		# Convert data array into a query string (ie animal=dog&sport=baseball)
		foreach ($data_array as $key => $value) {
			if(strlen(trim($value))>0) {
				$temp_string[] = $key . "=" . urlencode($value);
			}
			else {
				$temp_string[] = $key;
			}
		}
		$query_string = join('&', $temp_string);
		$query_string = str_replace("%2A", "*", $query_string);
	}

	# HEAD method configuration
	if($method == HEAD) {
		curl_setopt($ch, CURLOPT_HEADER, TRUE);				// No http head
		curl_setopt($ch, CURLOPT_NOBODY, TRUE);				// Return body
	}
	else {
		# GET method configuration
		if($method == GET) {
			if(isset($query_string)) {
				$target = $target . "?" . $query_string;
			}
#			   echo $target . "\n";
			curl_setopt ($ch, CURLOPT_HTTPGET, TRUE);
			curl_setopt ($ch, CURLOPT_POST, FALSE);
		}
		# POST method configuration
		if($method == POST) {
			if(isset($query_string)) {
				curl_setopt ($ch, CURLOPT_POSTFIELDS, $query_string);
			}
			curl_setopt ($ch, CURLOPT_POST, TRUE);
			curl_setopt ($ch, CURLOPT_HTTPGET, FALSE);
		}
		curl_setopt($ch, CURLOPT_HEADER, $incl_head);   // Include head as needed
		curl_setopt($ch, CURLOPT_NOBODY, FALSE);		// Return body
	}

	if (!empty($cookies)) {
		$cookies = str_replace("-", "", $cookies);
		curl_setopt($ch, CURLOPT_COOKIE, $cookies);   // request cookies
	}

	curl_setopt($ch, CURLOPT_COOKIEJAR, COOKIE_FILE);   // Cookie management.
	curl_setopt($ch, CURLOPT_COOKIEFILE, COOKIE_FILE);
	curl_setopt($ch, CURLOPT_TIMEOUT, CURL_TIMEOUT);	// Timeout
	curl_setopt($ch, CURLOPT_USERAGENT, WEBBOT_NAME);   // Webbot name
	curl_setopt($ch, CURLOPT_URL, $target);			 // Target site
	curl_setopt($ch, CURLOPT_REFERER, $ref);			// Referer value
	curl_setopt($ch, CURLOPT_VERBOSE, FALSE);		   // Minimize logs
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);	// No certificate
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);	 // Follow redirects
	curl_setopt($ch, CURLOPT_MAXREDIRS, 4);			 // Limit redirections to four
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);	 // Return in string

#	$header[] = "Accept: text/turtle";
	$header[] = "Connection: keep-alive";
#	$header[] = "Accept-Charset: Big5,utf-8;q=0.7,*;q=0.3";
	curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

	# Create return array
	$return_array['FILE']   = curl_exec($ch);
	$return_array['STATUS'] = curl_getinfo($ch);
	$return_array['ERROR']  = curl_error($ch);

	$timeDiff = microtime(true) - $timer;

	# Close PHP/CURL handle
	curl_close($ch);

	$wt = 0;
	while ($timeDiff < 1) {
		usleep(0.01 * 1000000);
		$timeDiff = microtime(true) - $timer;
		$wt += 0.01;
	}
	echo "Waited for $wt seconds\n";
	$timer = microtime(true);

	# Return results
	return $return_array;
}



?>
