<?php
/**
 * new home page
 */
?>

This is the new homepage. Welcome <?php echo $data->name ?>

<div>

<?php
// TODO: Move me where template function go
//
function renderFriendData($data, $property) {
	$out = "";
	// how many of each to show?
	$display_limit = 3; 
	$properties = array_keys((array)$data);
	print_r($properties);
	print $property;
	$data_property = $data->$property;

	foreach ($data->friends as $friend) {
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
			//
//dumper($data_property[$friend->id]);
		$out .= "<ul>";
		$i = 1;
		foreach($data_property[$friend->id][$friend->id]  as $_property) {
			$out .="<li>";
			 $out .= "<pre>";
//			 $out .= print_r($_property,1);
			 $out .= "</pre>";
			//  if ($_property) { die(); } 
			if (array_key_exists('category',(array)$_property)) { 
				$out .= $_property->category . ": ";
			}
			if (array_key_exists('name',(array)$_property)) { 
				$out .= $_property->name . ": ";
			}

			// $out .= $_property->name;
			$out .= " </li>";
			if (++$i > $display_limit) {
				break 1;
			}
		}
		$out .="</ul>";
	}
	return $out;
}



 print renderFriendData($data, 'musics');
 print renderFriendData($data, 'likes');
 print renderFriendData($data, 'books');






?>
</div><hr />
