<?php
/**
 * new home page
 */
?>

This is the new homepage. Welcome <?php echo $data->name ?>

<div>
<?php




foreach ($data->friends as $friend) {
	print $friend->name;
	print $friend->id . " --"; 
	print "<strong>LIKES " .  count($data->flikes[$friend->id]) ." things. </strong>";
	// just show 3 things the friend likes
	$like_limit = 3; $i = 1;
	print "<ul>";
	foreach($data->flikes[$friend->id] as $flike) {
		print "<li>";
		isset($flike->category) and print $flike->category . ": ";
		print $flike->name;
		print " </li>";
		if (++$i > $like_limit) {
			break 1;
		}
	}

	print "</ul>";
}



?>
</div><hr />
