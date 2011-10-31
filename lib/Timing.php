<?php
/**
 * Timing
 *
 * Timing related functions
 */

function gentime() {
	static $a;
	if($a == 0) $a = microtime(true);
	else return (string)(microtime(true)-$a);
}
