<?php
/**
 * Likeometer index.php
 * This is for the canvas app version of fblikes
 *
 */


$add_this_path =  dirname(dirname(__FILE__));
set_include_path( $add_this_path . ":" . get_include_path() );
require "lib/utils.php";
require "lib/FBUtils.php";
require "lib/Model.php";

$FBSECRET = getenv("FACEBOOK_SECRET");
$YOUR_APP_ID = getenv("FACEBOOK_APP_ID");
if ($_SERVER['HTTP_HOST'] == "enilemit.home")  {
	$YOUR_CANVAS_PAGE = "https://apps.facebook.com/ns_enilemit_local/";
} else { 
	$YOUR_CANVAS_PAGE = "https://apps.facebook.com/ns_enilemit/";
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
	<link rel="stylesheet" href="https://enilemit.home/likeometer/lom.css" type="text/css" media="screen,projection" />
</head>
<body>

<h1>Facebook Like-O-Meter: Home Page</h1>
<div>
	About this app: This app will look at the things that all your friends on facebook like.  It will show you what things most of your friends like.
</div>
<div>
	There are 3 pages to visit as part of using Like-O-Meter.
	<ol>
		<li>
			What you like.
			This shows all the things that you've liked and also shows you what sorts of things you tend to like by grouping the things you've liked by a categorization proivided by Facebook via its graph API. For instance, you may like more Movies than Music or Television.
		</li>
		<li>
		What your friends all like.
		This page shows you the 20 things that most of your friends like that you don't already like. This is a great way to discover new things based upon the wisdom of your crowd.  These are also grouped by category.</li>
		</li>

		<li>What stuff do you and your friend's like in common?
		This page compares your likes to each of your friends' likes. The results show you which things that you like, how many and which friends like it too.
		</li>
	</ol>
</div>
<div>
<?php
	$me = Model::ize($token, 'me');
	dumper($me);

?>
</div>

<footer>
&copy;2011 Jeff Kolber
</footer>
</body>
</html>
