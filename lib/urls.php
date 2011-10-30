<?php
/**
 * urls.php
 *
 */
class Urls {
	static  $urls = array(

		'/^$/' => array(
			'Homepage_Controller',
			'Hello',
			array(),	
		),

		'/^index$/' => array(
			'Homepage_Controller',
			'Hello',
			array(),	
		),

		'/^flikes$/' => array(
			'Homepage_Controller',
			'youAndYourFriendsLike',
		array()	
		),

		'/^common$/' => array(
			'Common_Interest_Controller',
			'Show',
			array()	
		),

		'/^person\/([0-9]+)\/?$/' => array(
			'Common_Interest_Controller',
			'Show',
			array('id'=>'%1%')
		),
	);
}
