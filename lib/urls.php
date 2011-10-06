<?php
/**
 * urls.php
 *
 * do dispatch django style
 */

class Urls {

	static  $urls = array(
		'/' => array('Homepage_Controller','Hello'),
		'/index' => array('Homepage','Hello'),
		'/resource' => array('Homepage','Show'),
	);
}
