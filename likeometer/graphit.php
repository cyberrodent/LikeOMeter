<?php

if (array_key_exists('page', $_GET)) { 
	$page = $_GET['page'];
	$errno = $errstr = null;
	$fp = fsockopen("home.cyberrodent.com", 20003, $errno, $errstr, 3);
	if ($fp) {
		fwrite($fp, "likeometer.page $page ". time() . "\n");
		fclose($fp);
	}
}


