<?php
/**
 * Likeometer index.php
 * This is for the canvas app version of Like-O-Meter
 *
 * Copyright 2011 Jeffrey Kolber
 * All Rights Reserved
 */

$FBSECRET = getenv("FACEBOOK_SECRET");
$YOUR_APP_ID = getenv("FACEBOOK_APP_ID");

if ($_SERVER['HTTP_HOST'] == "enilemit.home")  {
	// handles dev and production setup
	$YOUR_CANVAS_PAGE = "https://apps.facebook.com/ns_enilemit_local/";
} else { 
	$YOUR_CANVAS_PAGE = "https://apps.facebook.com/like_o_meter/";
}

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




/* <html xmlns:fb="http://ogp.me/ns/fb#">
 * Add an XML namespace to the <html> tag of your document. This is necessary for XFBML 
 * to work in earlier versions of Internet Explorer.
 */

?><!DOCTYPE html>
<html lang="en-US">
<head>
	<meta charset=utf-8>
	<title>Like-O-Meter</title>
	<link rel="stylesheet" href="https://<?php echo $_SERVER['HTTP_HOST'] ?>/likeometer/lom.css" type="text/css" media="screen,projection" />
	<script type="text/javascript" src="https://<?php echo $_SERVER['HTTP_HOST'] ?>/jquery-1.7.min.js"></script>
	<script type="text/javascript" src="https://<?php echo $_SERVER['HTTP_HOST'] ?>/likeometer/protovis.min.js"></script>
</head>
<body>
<div id="debug"></div>
<header>
	<h1><img src="/images/lom.png" height="75" width="75" />Like-o-Meter</h1>
	<nav>
		<a id="flikes">Friends' Likes</a>
		<!-- 
		<a id="you">Your Likes</a>
		<a id="common">Common Likes</a>
		-->
		<a id="home">About the Like-O-Meter</a>
	</nav>

<div id="statusline">
	Initializing Like-o-Meter.
</div>
</header>

<?php /* scroll output as data loads	
<div id="scroll"></div> 
 */ ?>

<div id="friendslikes"> </div>

<!--
<div id="commnlikes"></div>

<div id="yourlikes"></div>
-->
<div class="about">

	<div>
		<ul>
		<li>	See what your Facebook friends like. </li>
		<li>	See 100's of things that 2 or more of your friends like.</li>
		<li>	Ranks liked-things based on how many friends like it.  </li>
		<li>	A great way to discover new things.</li>
		</ul>
		Here's a picture:
	</div>
		
	<div>
		<img src="/images/lom-explained.jpg" height="343" width="722" />
	</div>

	<div>
		Visit the <a target="_top" href="https://www.facebook.com/apps/application.php?id=<?php echo $YOUR_APP_ID ?>">Like-O-Meter's page on Facebook</a>
	</div>
	<div class="fb-like-box" data-href="https://www.facebook.com/apps/application.php?id=<?php echo $YOUR_APP_ID  ?>" data-width="292" data-show-faces="false" data-stream="false" data-header="true"></div>
<?php /*	<input type="button" value="Click To Fix Permission Errors" id="log_in_now" class="login_button hidden" /> */ ?>


</div>


<div class="loading">
Loading. Hang on.
<p><img src="/images/loading.gif" /></p>
</div>
<footer>
&copy;2011 Jeff Kolber
</footer>


<?php /* Templates hidden as script tags for microtemplate */ ?>

<script type="text/html" id="ltrph_tpl">
<div class="ltr" id="ltr<%=thing_id %>"></div>
</script>

<script type="text/html" id="histogram">
<h3>Distribution of likes</h3>
	<ol class="histogram">
	<% for (var i=0; i<like_count_lengths.length; i++) { %>
		<li> <%=like_count_lengths[i] %> 
		</li>
	<% } %>
	</ol>
</script>

<script type="text/html" id="ltr_tpl">
<div class="ltr" id="ltr<%=thing_id %>">
	<div class="h2" id="h2<%=thing_id %>">
		<a href="<%=link %>" target=_blank><img src="http://graph.facebook.com/<%=thing_id %>/picture?type=large&auth_token=<%=token %>" align="top" border="0" width="200" alt="<%=things_name %>" class="thing" border="0" /></a>
		<span class="bigger"><%=how_many_friends %></span> friends like<br />
		<a target=_blank href="<%=link %>"><%=things_name %></a>
		<span class="category">(<%=things_category %>)</span>
		<br />
		<fb:like send="false" show_faces="false" href="<%=link %>"></fb:like>
	</div>
	<div class="h3">
		<% for (var j=0; j < how_many_friends; j++) { %>
		<div class="fimg">
			<a target="_blank" href="https://facebook.com/<%=aLikers[j] %>" title="<%=friend_name[aLikers[j]] %>" ><img src="https://graph.facebook.com/<%=aLikers[j] %>/picture?type=square" height="32" width="32" border="0" /></a>
		</div>
		<% } %>
	</div>
</div>
</script>

<script type="text/html" id="debug_tpl">
<div id="debug">
  <div><%=doc_height %></div>
	<div><%=scrolltop %></div>
	<div><%=scroll_bar_height %></div>
	</div>
</script>

<?php /* Now load our javascript, setup and app */ ?>

<script type="text/javascript">
<?php include "./setup.js" ?>
</script>

<script type="text/javascript">
<?php include "./Likeometer.js" ?>
</script>


</body>
</html><?php


// hit graphite
$errno = $errstr = null;
$fp = fsockopen("home.cyberrodent.com", 20003, $errno, $errstr, 3);
if ($fp) { fwrite($fp, "likeometer.view 1 ". time() . "\n"); fclose($fp);
}


