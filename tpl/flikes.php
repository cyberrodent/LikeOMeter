<?php
/**
 * your file
 */


?><h1>What you and your friends like most on facebook</h1>



<?php

foreach ($data->top as $liked_thing) {

	?>
	<h1><?php echo($liked_thing->name) ?> (
	<?php echo($liked_thing->category); ?>)</h1>


	<div>
			These friends liked this: <ol>
		<?php
			foreach ($liked_thing->likers as $liker) {
				print "<li>" . $data->friend_idx[$liker]['name'] . "</li>";
			}
		?></ol>
	</div>


	<div>About this :
			<?php
				foreach ((array)$liked_thing->graph as $k => $v) {
					print "<li> $k = $v</li>";

				}
			?>

	</div>
	<?php

}
