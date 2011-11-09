<?php
/**
 * your file
 */

function renderSome($_data, $limit = 25, $css_class = '', $token = '') {
	$i = 1;
	?><ul <?php e($css_class ? 'class="'.$css_class.'" ': '')  ?>><?php
	foreach ($_data as $d) { 
		if ($token) {
			?><li><img src="https://graph.facebook.com/<?php e($d['id']) ?>/picture?type=square&access_token=<?php e($token)?>" /><?php 
		}
		if (array_key_exists('name',(array)$d)) { 
			?><span class="name"><a href="https://facebook.com/<?php e($d['id']) ?>"><?php echo $d['name'] ?></a></span><?php
		}
		if (array_key_exists('category',(array)$d)) { 
			?> <span class="category">(<?php echo $d['category'] ?>)</span><?php
		}
		if (array_key_exists('story',(array)$d)) { 
			?><span class="story"><?php echo $d['story'] ?></span><?php
		}
		?></li><?php 
		if (++$i > $limit) {
			?></ul><?php
			return;
		}
	}
	?></ul><?php
}


?><div class="holder">
<h1>What you like on Facebook</h1>

<img src="https://graph.facebook.com/me/picture?type=normal&access_token=<?php e($data->token) ?>" />

<br clear="both" />
<?php 
$ats = array('likes','music','movies','books');
foreach ($ats as $at) {

	echo "<div class='at'><h2>" . ucfirst($at) . "</h2>";
	$dataat = $data->$at;
	renderSome($dataat[$data->meid],25,$at,$data->token);
	echo "</div>";
}


?></div>
