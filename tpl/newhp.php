<?php
/**
 * new home page
 */

$meid = $data->meid;

function renderFriend($id, $attr, $tag) {
	?><div class="<?php e($tag) ?>">
		<h3><?php e(ucfirst($tag)) ?> (<?php print count($attr[$id][$id]) ?>)</h3>
			<?php renderSome($attr[$id][$id]); ?>
		</div><?php	

}

function renderSome($data, $limit = 25) {
	$i = 1;
	foreach ($data as $d) { 
			$d = (object)$d;
		?><li><?php 
		if (array_key_exists('name',(array)$d)) { 
			?><span class="name"><?php echo $d->name ?></span><?php
		}
		if (array_key_exists('category',(array)$d)) { 
			?> <span class="category">(<?php echo $d->category ?>)</span><?php
		}
		if (array_key_exists('story',(array)$d)) { 
			?><span class="story"><?php echo $d->story ?></span><?php
		}
		?></li><?php 
		if (++$i > $limit) {
			return;
		}
	}
}



?><h1> This is a person page. id=<?php e($data->meid) ?></h1>
<div class="friend">
	<img src="https://graph.facebook.com/me/picture?type=normal&access_token=<?php e($data->token) ?>" />
	<h2>
		<?php echo $data->name ?>
	</h2>
	<?php
	if (array_key_exists($meid, $data->likes)) {
			renderFriend($meid, $data->likes, "likes");
		}
		if (array_key_exists($meid, $data->movies)) {
			renderFriend($meid, $data->movies, "movies");
		}
		if (array_key_exists($meid, $data->musics)) {
			renderFriend($meid, $data->musics, "music");
		}
		if (array_key_exists($meid, $data->books)) {
			renderFriend($meid, $data->books, "books");
		}
		if (array_key_exists($meid, $data->television)) {
			renderFriend($meid, $data->television, "television");
		}
	?>
</div>

<div class="clearall">
	<br /> <br /> <br /> <br />
	This could be some stuff at the bottom of the page	
	<br />
</div>

<div>

