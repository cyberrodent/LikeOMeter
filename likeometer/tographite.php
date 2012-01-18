<?php

if (!empty(getenv("GRAPHITE_HOST"))) {

	$gh = getenv('GRAPHITE_HOST');
	$gp = getenv('GRAPHITE_PORT');
	$gk = getenv('GRAPHITE_KEY');

	if (array_key_exists('key', $_GET) &&  array_key_exists('val', $_GET)) { 
		$key= $_GET['key'];
		$val= $_GET['val'];
		$errno = $errstr = null;
		$fp = fsockopen($gh, $gp , $errno, $errstr, 3);
		if ($fp) {
			fwrite($fp, "$gk.$key $val ". time() . "\n");
			fclose($fp);
		}
	}
}
