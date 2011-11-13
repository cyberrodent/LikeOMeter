<?php

/*****************************************************************************
 *
 * This script provides non Facebook specific utility functions that you may
 * wish to use in your app.  These functions involve 'sanitizing' output to
 * prevent XSS (Cross-site scripting) vulnerabilities.  By using these
 * functions, you remove a certain type of malicious content that could
 * be displayed to your users.  For a more robust and comprehensive solution,
 * Facebook has open sourced XHP.  XHP is a PHP extension which makes your
 * front-end code easier to understand and help you avoid cross-site
 * scripting attacks.  Learn more at 'https://github.com/facebook/xhp/wiki/'.
 *
 ****************************************************************************/


/**
 * @return the value at $index in $array or $default if $index is not set.
 *         By default, the value returned will be sanitized to prevent
 *         XSS attacks, however if $sanitize is set to false, the raw
 *         values will be returned
 */
function idx($array, $index, $default = null, $saniztize = true) {
  if (array_key_exists($index, $array)) {
    $value = $array[$index];
  } else {
    $value = $default;
  }
  if ($sanitize) {
    return htmlentities($value);
  } else {
    return $value;
  }
}

/**
 * This will echo $value after sanitizing any html tags in it.
 * This is for preventing XSS attacks.  You should use echoSafe whenever
 * you are echoing content that could possibly be malicious (i.e.
 * content from an external request). This does not sanitize javascript
 * or attributes
 */
function echoEntity($value) {
  echo(htmlentities($value));
}

/**
 * @return $value if $value is numeric, else null.  Use this to assert that
 *         a value (like a user id) is a number.
 */
function assertNumeric($value) {
  if (is_numeric($value)) {
    return $value;
  } else {
    return null;
  }
}
function dumper($var) {
	print "<pre>";
	print_r($var);
	print "</pre>";
}
function splat($var) {
	dumper($var);
	$bt = debug_backtrace();
	$caller = $bt[0];
	error_log("splat called from ". $caller['file'] ." line " .$caller['line'] );
	die();
}
function e($str) {
	echo $str;	
}

function keyById($array) {
	$ret = array();
	foreach ($array as $k => $v) {
		if (strpos($k, "_") !== 0) {
			$ret[$v['id']] = $v; 
		}		
	}	
	return $ret;
}

require dirname(__FILE__). "/Timing.php";

//;
// facebook auth 
//
function parse_signed_request($signed_request, $secret) {
  list($encoded_sig, $payload) = explode('.', $signed_request, 2); 

  // decode the data
  $sig = base64_url_decode($encoded_sig);
  $data = json_decode(base64_url_decode($payload), true);

  if (strtoupper($data['algorithm']) !== 'HMAC-SHA256') {
    error_log('Unknown algorithm. Expected HMAC-SHA256');
    return null;
  }

  // check sig
  $expected_sig = hash_hmac('sha256', $payload, $secret, $raw = true);
  if ($sig !== $expected_sig) {
    error_log('Bad Signed JSON signature!');
    return null;
  }

  return $data;
}

function base64_url_decode($input) {
  return base64_decode(strtr($input, '-_', '+/'));
}
