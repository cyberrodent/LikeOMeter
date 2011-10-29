<?php
/**
 * urls.php
 *
 */
class Urls {
	static  $urls = array(
		'/' => array('Homepage_Controller','Hello'),
		'/index' => array('Homepage_Controller','Hello'),
		'/flikes' => array('Homepage_Controller','youAndYourFriendsLike'),
		'/resource' => array('Homepage_Controller','Show'),
		'/common' => array('Common_Interest_Controller','Show'),
	);
}
