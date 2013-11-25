<?php
/*
########################################################################                                        
Copyright 2007, Michael Schrenk                                                                                 
   This software is designed for use with the book,                                                             
   "Webbots, Spiders, and Screen Scarpers", Michael Schrenk, 2007 No Starch Press, San Francisco CA             
                                                                                                                
W3C® SOFTWARE NOTICE AND LICENSE                                                                                
                                                                                                                
This work (and included software, documentation such as READMEs, or other                                       
related items) is being provided by the copyright holders under the following license.                          
 By obtaining, using and/or copying this work, you (the licensee) agree that you have read,                     
 understood, and will comply with the following terms and conditions.                                           
                                                                                                                
Permission to copy, modify, and distribute this software and its documentation, with or                         
without modification, for any purpose and without fee or royalty is hereby granted, provided                    
that you include the following on ALL copies of the software and documentation or portions thereof,             
including modifications:                                                                                        
   1. The full text of this NOTICE in a location viewable to users of the redistributed                         
      or derivative work.                                                                                       
   2. Any pre-existing intellectual property disclaimers, notices, or terms and conditions.                     
      If none exist, the W3C Software Short Notice should be included (hypertext is preferred,                  
      text is permitted) within the body of any redistributed or derivative code.                               
   3. Notice of any changes or modifications to the files, including the date changes were made.                
      (We recommend you provide URIs to the location from which the code is derived.)                           
                                                                                                                
THIS SOFTWARE AND DOCUMENTATION IS PROVIDED "AS IS," AND COPYRIGHT HOLDERS MAKE NO REPRESENTATIONS OR           
WARRANTIES, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO, WARRANTIES OF MERCHANTABILITY OR FITNESS          
FOR ANY PARTICULAR PURPOSE OR THAT THE USE OF THE SOFTWARE OR DOCUMENTATION WILL NOT INFRINGE ANY THIRD         
PARTY PATENTS, COPYRIGHTS, TRADEMARKS OR OTHER RIGHTS.                                                          
                                                                                                                
COPYRIGHT HOLDERS WILL NOT BE LIABLE FOR ANY DIRECT, INDIRECT, SPECIAL OR CONSEQUENTIAL DAMAGES ARISING OUT     
OF ANY USE OF THE SOFTWARE OR DOCUMENTATION.                                                                    
                                                                                                                
The name and trademarks of copyright holders may NOT be used in advertising or publicity pertaining to the      
software without specific, written prior permission. Title to copyright in this software and any associated     
documentation will at all times remain with copyright holders.                                                  
########################################################################                                        
*/


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
