<?php
/**
 * your file
 */

function renderSome($data, $limit = 25, $css_class = '') {
	$i = 1;
	?><ul><?php
	foreach ($data as $d) { 
		?><li><?php 
		if (array_key_exists('name',(array)$d)) { 
			?><span class="name"><?php echo $d['name'] ?></span><?php
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


?><h1>What you like on Facebook</h1>

<img src="https://graph.facebook.com/me/picture?type=normal&access_token=<?php e($data->token) ?>" />

<?php 
$ats = array('likes','music','movies','books');
foreach ($ats as $at) {
	echo "<h1>" . ucfirst($at) . "</h1>";
	$dataat = $data->$at;
	renderSome($dataat[$data->meid]);
}



