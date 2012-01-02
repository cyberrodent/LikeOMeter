<?php
/**
 * Likeometer index.php
 * This is for the canvas app version of Like-o-Meter
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


/* <html xmlns:fb="http://ogp.me/ns/fb#">
 * Add an XML namespace to the <html> tag of your document. This is necessary for XFBML 
 * to work in earlier versions of Internet Explorer.
 */

?><!DOCTYPE html>
<html lang="en-US">
<head>
	<meta charset=utf-8>
	<title>Like-o-Meter</title>
	<link rel="stylesheet" href="https://<?php echo $_SERVER['HTTP_HOST'] ?>/likeometer/lom.css" type="text/css" media="screen,projection" />
	<script type="text/javascript" src="https://<?php echo $_SERVER['HTTP_HOST'] ?>/jquery-1.7.min.js"></script>
	<script type="text/javascript" src="https://<?php echo $_SERVER['HTTP_HOST'] ?>/likeometer/protovis.min.js"></script>
</head>
<body>
<div id="debug"></div>
<header>
	<h1><img src="/images/lom.png" alt="logo" height="50" width="50" />
	Like-o-Meter</h1>
	<nav>
		<a id="friendslikes">Friends' Likes</a>
		<a id="about">About the Like-o-Meter</a>
		<a id="yourlikes">Your Likes</a>
		<a id="common">Common Likes</a>
		<!-- 
		// these are for future features
		//	
	
		-->
	</nav>

	<div id="statusline">
		Initializing Like-o-Meter.
	</div>
</header>

<?php /* scroll output as data loads	
<div id="scroll"></div> 
 */ ?>

<div id="friendslikes"> </div>



<div id="common">
<p>Like-o-meter can show you which of your friends like the same stuff that you do. Coming Soon. </p>
</div>




<div id="about">
	<div>
		<ul>
		<li>Lists your friends most common likes.</li>
		<li>Discover stuff that you'll like.</li>
		</ul>
	</div>
	<p>The Like-o-Meter finds all the things that all of your friends like on Facebook and collates them and then lists them in order from most common to least. You can scroll through and see who likes what.</p>
	<p>Like-o-Meter will ask you to write on your wall with your top 3 most common likes. If you say yes, Like-o-Meter won't bother you for a while, but if you say no, Like-o-Meter will keep asking you to write on your wall each time you use it.
	</p>
	<p>I made the Like-o-Meter my spare time to get familiar with Facebook's Graph API.  I don't store any of your data. The Like-o-meter is written almost entirely in Javascript. If you like the Like-O-Meter please tell your friends to check it our too.  Its just for fun.  Thanks for visiting.</p>

	<div>
		Here are my top 3:
		<img src="/images/lom-explained.jpg" height="611" width="739" />
	</div>

	<div>
		Visit the <a target="_top" href="https://www.facebook.com/apps/application.php?id=<?php echo $YOUR_APP_ID ?>">Like-o-Meter's page on Facebook</a>
	</div>

	<div class="fb-like-box" data-href="https://www.facebook.com/apps/application.php?id=<?php echo $YOUR_APP_ID  ?>" data-width="292" data-show-faces="false" data-stream="false" data-header="true"></div>

	<?php /*	<input type="button" value="Click To Fix Permission Errors" id="log_in_now" class="login_button hidden" /> */ ?>
</div>

<div id="yourlikes">
	<p>You might have liked alot of things over the years on facebook. Maybe your don't like some of those things anymore. Like-o-meter can help you manage your likes on Facebook. Coming Soon.
</div>

<div class="loading">
	Loading. Hang on.<p><img src="/images/loading.gif" /></p>
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
		<a href="<%=link %>" target=_blank><img src="http://graph.facebook.com/<%=thing_id %>/picture?type=large&auth_token=<%=token %>" align="top" border="0" width="180" alt="<%=things_name %>" class="thing" border="0" /></a>
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


