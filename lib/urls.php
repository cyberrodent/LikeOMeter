<?php
/**
 * urls.php
 *
 * do dispatch django style
 */

class Urls {

	static  $urls = array(
		'/' => array('Homepage_Controller','Hello'),
		'/index' => array('Homepage_Controller','Hello'),
		'/flikes' => array('Homepage_Controller','youAndYourFriendsLike'),
		'/resource' => array('Homepage_Controller','Show'),
	);
}
