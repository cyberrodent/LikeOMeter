<?php
/**
 * your file
 */
require "../lib/utils.php";

$FBSECRET = getenv("FACEBOOK_SECRET");
$YOUR_APP_ID = getenv("FACEBOOK_APP_ID");

if ($_SERVER['HTTP_HOST'] == "enilemit.home")  {
	$YOUR_CANVAS_PAGE = "https://apps.facebook.com/ns_enilemit_local/";
} else { 
	$YOUR_CANVAS_PAGE = "https://apps.facebook.com/ns_enilemit/";
}

if ($_POST) {
	# dumper($_POST);
	$decode = parse_signed_request($_POST['signed_request'], $FBSECRET);


	if (!isset($decode['oauth_token'])) {
		error_log("authenticate with the fb");
		error_log("Location: https://www.facebook.com/dialog/oauth?client_id=$YOUR_APP_ID&redirect_uri=$YOUR_CANVAS_PAGE&scope=user_likes,friends_likes");
		echo "<script>top.location.href = \"https://www.facebook.com/dialog/oauth?client_id=".  $YOUR_APP_ID."&redirect_uri=".$YOUR_CANVAS_PAGE.  "&scope=user_likes,friends_likes\"; </script> ";

		die();
	}	


	echo "You seem ok...on with the app!";


} else { 

	die("nothing to get here");

}
