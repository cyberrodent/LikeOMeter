<?php

if (array_key_exists('key', $_GET) &&  array_key_exists('val', $_GET)) { 
	$key= $_GET['key'];
	$val= $_GET['val'];
	$errno = $errstr = null;
	$fp = fsockopen("home.cyberrodent.com", 20003, $errno, $errstr, 3);
	if ($fp) {
		fwrite($fp, "likeometer.$key $val ". time() . "\n");
		fclose($fp);
	}
}
