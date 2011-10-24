<?php
/**
 * your file
 */
function drawWebsiteLink($website) {
	$sites = explode("\n",$website);
	foreach ($sites as $site) {
		?><li>Web link: <a href="<?php
				echo $site;
				?>" target="_blank"><?php
				echo $site;
				?></a><?php
	}
}


?><html><head>
	<title>What your friends like on Facebook</title>
<style>
body {
margin:80px 0 0 0; 
padding:0; 
border:0; 
font-family: Helvetica, Arial,sans-serif; 
font-size:100%;
} 
.lefty { float: left;  }
.w300 { width: 350px; } 
.w500 { width: 450px; } 
h1.clean { border:0; margin:0; padding:0; line-height:70px; } 
div.holder {  padding-top:0px; padding-left:10px; padding-right:10px; } 
.holder h1 { padding-top:80px; border-top:1px dashed #ccc; } 
div.fixy { 
padding-left:10px;
top:0;
position:fixed;
border:0; 
height:70px; 
line-height:50px; 
background-color:#627AAD; 
color:white;
display:block;
width:100%; 
margin-bottom:70px;
}
</style>

</head>
<body>	
<div class="fixy"> 
<h1 class="clean">
	What all your friends like on Facebook
</h1></div>

<div class="holder">
	<a name="top"></a>
	<h1>Summary: Top 20</h1>	
	<ol class="lefty w500">
<?php 
$pos = 0;
$cats = array();
foreach ($data->top as $liked_thing) {
	?><li><a href="#pos<?php echo $pos++ ?>"><?php echo $liked_thing->name ?></a>
		(<?php echo $liked_thing->category ?>)
		<?php echo count($liked_thing->likers) ?> friends like this
		</li><?php	
			if (!array_key_exists($liked_thing->category, $cats)) {
				$cats[$liked_thing->category] = 1;
			} else { 
				$cats[ $liked_thing->category ]++;
			}
}
?>
</ol>

<ul class="lefty w300">
<?php 
arsort($cats);
foreach ($cats as $cat => $ct) {
	print "<li>$ct liked things are <strong>$cat</strong></li>";
}
?></ul>

<br clear="both" />


<?php
$pos = 0;
foreach ($data->top as $liked_thing) { ?>

	<a name="pos<?php echo $pos++ ?>"></a>
	<h1>#<?php echo $pos  ?>) <?php echo($liked_thing->name) ?> (<?php echo($liked_thing->category); ?>)</h1>

	<div style="float:left; width:500px;">

	<img src="<?php echo $liked_thing->graph['picture'] ?>" />

	<li>on Facebook:<a href="<?php echo $liked_thing->graph['link'] ?>" target="_blank"><?php echo($liked_thing->name) ?></a></li><?php
			if (!empty($liked_thing->graph['website'])) {
				drawWebsiteLink($liked_thing->graph['website']);
			}
			?>	
			<?php
				foreach ((array)$liked_thing->graph as $k => $v) {
					print "<li> $k = $v</li>";
				}
			?>
		<p><a href="#top">Back to top</a></p>
	</div>

	<div style="float:left; width:300px;">
	These <?php print count($liked_thing->likers) ?> friends liked this: <ol>
		<?php
			foreach ($liked_thing->likers as $liker) {
				print "<li>" . $data->friend_idx[$liker]['name'] . "</li>";
			}
		?></ol>
	</div>

	<br clear="both" />
	<?php

}

?></div></body></html>
