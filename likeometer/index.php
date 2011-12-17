<?php
/**
 * Likeometer index.php
 * This is for the canvas app version of fblikes
 *
 */
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

$FBSECRET = getenv("FACEBOOK_SECRET");
$YOUR_APP_ID = getenv("FACEBOOK_APP_ID");
if ($_SERVER['HTTP_HOST'] == "enilemit.home")  {
	$YOUR_CANVAS_PAGE = "https://apps.facebook.com/ns_enilemit_local/";
} else { 
	$YOUR_CANVAS_PAGE = "https://apps.facebook.com/like_o_meter/";
}

if ($_POST) {
	$decode = parse_signed_request($_POST['signed_request'], $FBSECRET);

	// if we don't have a token then we need to ask this dude for permission
	if (!isset($decode['oauth_token'])) {
		error_log("authenticate with the fb");
		error_log("Location: https://www.facebook.com/dialog/oauth?client_id=$YOUR_APP_ID&redirect_uri=$YOUR_CANVAS_PAGE&scope=user_likes,friends_likes");
		echo "<script>top.location.href = \"https://www.facebook.com/dialog/oauth?client_id=".  
			$YOUR_APP_ID."&redirect_uri=".$YOUR_CANVAS_PAGE. "&scope=user_likes,friends_likes\";</script> ";
		die();
	}	


	# echo "You seem ok...on with the app!";
	$token = $decode['oauth_token'];

} else { 

	die("nothing to get here");

}


?><!DOCTYPE html>
<html lang="en-US">
<head>
	<meta charset=utf-8>
	<title>Like-O-Meter</title>
	<link rel="stylesheet" href="https://<?php echo $_SERVER['HTTP_HOST'] ?>/likeometer/lom.css" type="text/css" media="screen,projection" />
<script type="text/javascript" src="https://<?php echo $_SERVER['HTTP_HOST'] ?>/jquery-1.7.min.js"></script>
</head>
<body>
<script>
<?php include "./Likeometer.js" ?>
<?php include "./setup.js" ?>
</script>
<h1><img src="/images/lom.png" height="75" width="75">Facebook Like-O-Meter</h1>
<nav>
<a id="flikes">Friends' Likes</a>
<!-- 
<a id="you">Your Likes</a>
<a id="common">Common Likes</a>
-->
<a id="home">About the Like-O-Meter</a>

</nav>

<div id="statusline">
	Initializing the Like-O-Meter.
</div>
<?php /* <div id="scroll"></div> */ ?>

<div id="friendslikes"></div>
<div id="commnlikes"></div>
<div id="yourlikes"></div>


<div class="about">
	<div>
	<ul>
	<li>	The Like-O-Meter will let you see what your Facebook friends like. </li>
	<li>	It will rank them based on how many friends like the thing.  </li>
	<li>	It will show you up to 1000 things that at least 2 of your friends like. In other words, you can keep scrolling down for a long long time.</li>
	</ul>
	Here's a picture:
	 </div>
	
	<div>
		<img src="/images/lom-explained.jpg" height="343" width="722" />
	</div>

	<div>
	Here is the <a target="_top" href="https://www.facebook.com/apps/application.php?id=<?php echo $YOUR_APP_ID ?>">Like-O-Meter's page on Facebook</a>
	</div>
	<div class="fb-like-box" data-href="https://www.facebook.com/apps/application.php?id=<?php echo $YOUR_APP_ID  ?>" data-width="292" data-show-faces="false" data-stream="false" data-header="true"></div>
<?php /*
	<div>
		So far there are 3 pages to visit as part of using Like-O-Meter.  
		<ol>
			<li>
				<strong>Your Likes</strong> is the simplest of these pages. It shows the things you like, grouped into the categories that Facebook assigns each thing.  Things can be assigned more than one category.
			</li>
			<li><strong>Common Likes</strong>Of all the things you like on Facebook, which of your friends also liked it? Find out on this page.

	
	
			<li>
			<strong>Friend Likes</strong>
			This page shows you up to 1000 things that your friends on Facebook "liked" on Facebook. These things are ranked based upon how many friends liked it. This is the default page and it's a great way to discover new things. 
			</li>
	
			</li>
		</ol>
	</div>
 */ ?>
	<input type="button" value="Log in Now" id="log_in_now" class="login_button" />
</div>
<div>
<?php



?>
</div>
<footer>
&copy;2011 Jeff Kolber
</footer>
</body>
</html>
