<?php
/**
 * _data
 * persistance for ojects fetched from the facebook graph
 * these are keyed off the URI and optionally the access_token
 */


interface _data_point {
	public function getFromCache($token, $id);
	public function storeToCache($token, $id, $data);
}	

class DataPoint implements _data_point {

	private $__name__ = "DataPoint";
	private $data_dir = "./data_cache/";
	private $data_file_prefix = "jkdp_";
	private $ttl_file_suffix = "_ttl";

	/**
	 * max_ttl 
	 * max age in seconds. use negative number for infinite
	 * @var int
	 */
	private $max_ttl = 3600000000000000;
	public $data = null;
	/**
	 * use_token_for_hash 
	 * you'd want to use the token for the hash
	 * if you a) don't care about working offline and 
	 * b) if you want to isolate cached results per user
	 * 
	 * @var mixed
	 */
	private $use_token_for_hash = false;
	private $use_cache = false; 

	function __construct($name = "DataPointObject") { 
		// make an empty DataPoint ojecct
		$this->__name__ = $name;
		// mkdir($this->data_dir);
		$this->use_cache = file_exists($this->data_dir) and 
			is_dir($this->data_dir) and 
			is_writable($this->data_dir) and 
			is_readable($this->data_dir);
	}
	function makeKey($token, $uri) {
		// TODO : make sure that "me" isn;t in the uri
		// especially if not $use_token_for_hash
		if ($this->use_token_for_hash) { 
			$mash = $token . $uri;
		} else { 
			$mash = $uri;
		}
		return (md5($mash));
	}
	function makeFilename($key) {
		return $this->data_dir . 
			$this->data_file_prefix .
			$key;
	}
	function makeTTLfile($key) {
		return $this->makeFilename($key) . 
			$this->ttl_file_suffix;
	}
	function getFromCache($token, $uri) {
		if (!$this->use_cache) { 
			return false;
		}
		$key = $this->makeKey($token, $uri);
		$ttlfile = $this->makeTTLfile($key);

		if ($this->max_ttl > 0) {
			if (!file_exists($ttlfile)) {
				// assume it too old
				return false;
			}
			$ttl = file_get_contents($ttlfile);
			if (time() > $ttl + $this->max_ttl) {;
			error_log("cached key $key is too old, refetching");
			return false;
			}
		}

		$keyfile = $this->makeFilename($key);
		if (file_exists($keyfile)){ 
			$data = json_decode(file_get_contents($keyfile));
			$this->data = $data;
			error_log("got $key from cache ");
			return $data;
		}
	} 
	function storeToCache($token, $uri, $data) {

		if (is_writable($this->data_dir))  {
			$key = $this->makeKey($token, $uri);
			$keyfile = $this->makeFilename($key);
			$ttlfile = $this->makeTTLfile($key);
			file_put_contents($ttlfile, time());
			file_put_contents($keyfile, json_encode($data));		

			error_log("put $key into cache ");
		}
	}
}


