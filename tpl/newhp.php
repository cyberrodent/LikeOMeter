<?php
/**
 * new home page
 */
function e($str) {
	echo $str;	
}
?>
This is the new homepage. 
<div class="friend"><img src="https://graph.facebook.com/me/picture?type=normal&access_token=<?php e($data->token) ?>" />
<h2>
<?php echo $data->name ?></h2>
<?php

if (array_key_exists('me', $data->likes)) {
		renderFriend('me', $data->likes, "likes");
	}
	if (array_key_exists('me', $data->movies)) {
		renderFriend('me', $data->movies, "movies");
	}
	if (array_key_exists('me', $data->musics)) {
		renderFriend('me', $data->musics, "music");
	}
	if (array_key_exists('me', $data->books)) {
		renderFriend('me', $data->books, "books");
	}
	if (array_key_exists('me', $data->television)) {
		renderFriend('me', $data->television, "television");
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
/**
 * renderPropertyForFriends 
 *
 * @DEPRECATED 
 *
 * @param mixed $data 
 * @param mixed $property 
 * @access public
 * @return void
 */
function renderPropertyForFriends($data, $property) {
	$out = "";
	// how many of each to show?
	$display_limit = 3; 
	$properties = array_keys((array)$data);
	print implode(", ",$properties) . "<br />";
	print $property;
	$data_property = $data->$property;
	if (!$data_property) {
			return;
	}
	if (!isset($data->friends)) {
		return "no friends";
	}

	foreach ($data->friends as $friend) {
		if (!isset($data_property[$friend->id][$friend->id])){
			print_r($data_property);
			throw new Exception("Something unexpected. oh no");
		}
		$count_properties = count($data_property[$friend->id][$friend->id]) ; 
		$out .= "<div>";
		$out .= $friend->name; 
		$out .= $friend->id . " --"; 
		$out .= "<strong>$property " .
				$count_properties .
				" things.</strong>";
		$out .= "</div>";
		if ($count_properties == 0) { continue; }
			// just show 3 things the friend likes
			//
			// dumper($data_property[$friend->id]);
		$out .= "<ul>";
		$i = 1;
		foreach($data_property[$friend->id][$friend->id]  as $_property) {
			$out .="<li>";
//			 $out .= "<pre>";
//			 $out .= print_r($_property,1);
//			 $out .= "</pre>";
			if (array_key_exists('category',(array)$_property)) { 
				$out .= $_property->category . ": ";
			}
			if (array_key_exists('name',(array)$_property)) { 
				$out .= $_property->name . ": ";
			}
			// this is a special case to handle the posts 
			// which have a slightly different structure
			if (array_key_exists('story',(array)$_property)) { 
				$out .= $_property->story. ": ";
			}

			$out .= " </li>";
			if (++$i > $display_limit) {
				break 1;
			}
		}
		$out .="</ul>";
	}
	return $out;
}



# print renderPropertyForFriends($data, 'musics');
# print renderPropertyForFriends($data, 'likes');
# print renderPropertyForFriends($data, 'books');
# print renderPropertyForFriends($data, 'posts');



function renderFriend($id, $attr, $tag) {
	?><div class="<?php e($tag) ?>">
		<h3><?php e(ucfirst($tag)) ?> (<?php print count($attr[$id][$id]) ?>)</h3>
			<?php renderSome($attr[$id][$id]); ?>
		</div><?php	

}



function renderSome($data, $limit = 25) {
	$i = 1;

	foreach ($data as $d) { 
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

print $out;
?>

<style>
body { 
	padding-left:20px;
	font-size:80%; 
	font-family:arial, helvetica, sansserif; 
	background-color:#ccc;
	background-color:#627AAD;
	color:black;
} 
h3 { padding:0px; margin:0; } 
h2 { 
	line-height:48px;
	font-size:48px;
	margin:0; 
	padding-top:2px; 
	color: #000024;
	font-weight:normal;

}


h2 .score {
	font-size:14px;
	line-height:14px;
} 
.clearall { clear: both; float:none; } 

.television, .movies, .likes, .posts, .music, .books {
	width:180px;
	height:90px;
	float:left;
	padding:8px;
	overflow:scroll;
	background-color: white;
	margin-right:18px;
	margin-left:0px;
}
.category { color:#090; font-size:0.8em; } 

div.friend { 


}
div.friend img { 
	float:left; 
	/* width:150px; */
	height:154px;
	border:solid black 2px;
	margin-bottom:20px;
}

</style>
