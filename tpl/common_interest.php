<div class="CommonI">
<h1>Common Interest</h1>
<p>Here are the things you like that your friends also like</p>
<?php foreach ($data->common as $key => $thing) { ?>
	<h3><?php e($thing['name']) ?></h3>
	<img src="https://graph.facebook.com/<?php 
			e($thing['id'])
	?>/picture?type=large" width="200" alt="<?php 
			e($thing['name']) 
	?> />" />

	<div><strong>You and these friends like this: </strong>
<?php foreach ($thing['likers'] as $fkey => $friend) { ?>
		<div> 
			<?php echo $data->friends[$friend]['name'] ?>
		</div>
<?php } ?>
	</div>
<?php } ?>
</div>
