<?php
/**
 * new home page
 */

?>
<h1>This is a person page. id=<?php e($data->meid) ?></h1>
<div class="friend"><img src="https://graph.facebook.com/me/picture?type=normal&access_token=<?php e($data->token) ?>" />
<h2>
<?php echo $data->name ?></h2>
<?php
$meid = $data->meid;

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
	Here are all the things your friends like on facebook.
	<br />
</div>

<div>

<?php
// TODO: Move me where template function go
//

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



$out = '';

if (false) { 

	foreach (  array_slice($data->friends,4,25) as $friend) {

?>
<br clear="all" />
<div class="friend">
<img src="https://graph.facebook.com/<?php e($friend->id) ?>/picture?type=large" />
	<div>	
			<h2>
			<?php echo $friend->name ?> <span class="score">(
			<?php echo $data->scores[$friend->id] ?>
			)</span></h2>
<?php 


		if (array_key_exists($friend->id, $data->core)){ 

/*
		#		print_r(  $data->core[$friend->id] ); 
		if (array_key_exists('gender', (array)$data->core[$friend->id][$friend->id])) {
			echo $data->core[$friend->id][$friend->id]->gender;
		} else { 
			echo "poot";
		}
 */
/*
		if (array_key_exists('about', (array)$data->core[$friend->id][$friend->id])) {
			echo "<div>{$data->core[$friend->id][$friend->id]->about}</div>";
		} 
		if (array_key_exists('quote', (array)$data->core[$friend->id][$friend->id])) {
			echo "<div>{$data->core[$friend->id][$friend->id]->quote}</div>";
		} 
			if (array_key_exists('hometown', (array)$data->core[$friend->id][$friend->id])) {
			echo "<div>Hometown: {$data->core[$friend->id][$friend->id]->hometown}</div>";
		} 
		if (array_key_exists('location', (array)$data->core[$friend->id][$friend->id])) {
			echo "<div>Location: {$data->core[$friend->id][$friend->id]->location}</div>";
		} 
 */

		} else { 
			echo "this apple has no core";
		}



		if (array_key_exists($friend->id, $data->likes)) {
			renderFriend($friend->id, $data->likes, "likes");
		}
		if (array_key_exists($friend->id, $data->movies)) {
			renderFriend($friend->id, $data->movies, "movies");
		}
		if (array_key_exists($friend->id, $data->musics)) {
			renderFriend($friend->id, $data->musics, "music");
		}
		if (array_key_exists($friend->id, $data->books)) {
			renderFriend($friend->id, $data->books, "books");
		}
		if (array_key_exists($friend->id, $data->television)) {
			renderFriend($friend->id, $data->television, "television");
		}
		?></div></div><?php 
	}

}

print $out;
?>
