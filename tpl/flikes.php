<?php
/**
 * template for showing the commonest likes amongst your friends
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




$pos = 0;
$cats = array();
$cat_list_out = '';
foreach ($data->top as $liked_thing) {
	ob_start();
	?><li><a href="#pos<?php echo $pos++ ?>"><?php echo $liked_thing->name ?></a>
		(<?php echo $liked_thing->category ?>)
		<?php echo count($liked_thing->likers) ?> friends like this
		</li><?php	
			if (!array_key_exists($liked_thing->category, $cats)) {
				$cats[$liked_thing->category] = 1;
			} else { 
				$cats[ $liked_thing->category ]++;
			}
	$cat_list_out .= ob_get_clean();
}
arsort($cats);


?><div class="holder">
	<a name="top"></a>
	<h1>Summary: Top 20</h1>	
	<ul class="lefty w300">
<?php 
foreach ($cats as $cat => $ct) {
	print "<li><strong>$cat</strong>: $ct out of top 20.</li>";
}
?></ul>
	<ol class="lefty w500">
	<?php echo $cat_list_out ?>	
	</ol>
<br clear="both" />


<?php
$pos = 0;
foreach ($data->top as $liked_thing) { ?>

	<a name="pos<?php echo $pos++ ?>"></a>
	<h1>#<?php echo $pos  ?>) <?php echo($liked_thing->name) ?> (<?php echo($liked_thing->category); ?>)</h1>
	<div class="righty "><a href="#top">Back to top</a></div>
	<div style="float:left; width:500px;">
		<img src="<?php echo $liked_thing->graph['picture'] ?>" />
		<ul>
			<li>on Facebook:<a href="<?php echo $liked_thing->graph['link'] ?>" target="_blank"><?php echo($liked_thing->name) ?></a></li><?php
			if (!empty($liked_thing->graph['website'])) {
				drawWebsiteLink($liked_thing->graph['website']);
			}
			foreach ((array)$liked_thing->graph as $k => $v) {
				print "<li> $k = $v</li>";
			}
		?></ul>
		<p><a href="#top">Back to top</a></p>
	</div>

	
	<?php print count($liked_thing->likers) ?> friends liked this: <br />

	<div class="liker"><ul>
		<?php
			$i = 1;
			foreach ($liked_thing->likers as $liker) {
				print "<li>" . 
					'<img src="https://graph.facebook.com/'.$liker.'/picture?type=square" height="28" width="28" alt="friend" border="1" /><div class="liker_name">'.
$data->friend_idx[$liker]['name'] . 
					"</div></li>";
				if ($i++ % 10 == 0) {
					print '</ul></div> <div class="liker"><ul>';
				}
			}
		?></ul>
	</div> 
	<br style="clear:both;" /><?php
}

?>that's all folks...

