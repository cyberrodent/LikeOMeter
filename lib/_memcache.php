<?php
/**
 * memcache : get from heroku config 
 * 
 * MEMCACHE_PASSWORD => YYq2j4HlCuyNMjTy
 * MEMCACHE_SERVERS  => mc7.ec2.northscale.net
 * MEMCACHE_USERNAME => app1301727%40heroku.com
 */


define('MEMCACHE_SERVERS' , getenv('MEMCACHE_SERVERS')); 
// define('MEMCACHE_USERNAME', 'app1301727%40heroku.com');
// define('MEMCACHE_PASSWORD', 'YYq2j4HlCuyNMjTy');

class MC {
	public static $mc;
	public static function getInstance() {
		self::$mc = new Memcached();
		self::$mc->addServer(MEMCACHE_SERVERS, 11211);
		return self::$mc;
	}
}
