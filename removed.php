<?php
/**
 * parse_signed_request
 * deal with facebook login, oauth2
 */
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

if ($_POST) {
	/* Turning off all php authentication steps 
	 *   but still responding only to POST
	 */
	if (0) { 
		// all the auth now happens in javascript
		// this is left over
		$decode = parse_signed_request($_POST['signed_request'], $FBSECRET);

		// if we don't have a token then we need to ask this dude for permission
		if (!isset($decode['oauth_token'])) {
			error_log("authenticate with the fb");
			error_log("Location: https://www.facebook.com/dialog/oauth?client_id=$YOUR_APP_ID".
				"&redirect_uri=$YOUR_CANVAS_PAGE&scope=read_stream,user_likes,friends_likes");
			echo "<script>top.location.href = \"https://www.facebook.com/dialog/oauth?scope=email,read_stream,user_likes,friends_likes&perms=publish_stream,read_stream,user_likes,friends_likes&client_id=". $YOUR_APP_ID."&redirect_uri=".$YOUR_CANVAS_PAGE. "\";</script> ";
			die();
		}	

		# You seem ok...on with the app!
		$token = $decode['oauth_token'];
	}
} else { 
	die("nothing to get here");
}




