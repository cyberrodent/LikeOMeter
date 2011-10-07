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
		'/resource' => array('Homepage_Controller','Show'),
	);
}
