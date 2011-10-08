<?php
/**
 * _data
 * persistance for ojects fetched from the facebook graph
 * these are keyed off the URI and optionally the access_token
 */


class DataPoint {
	private $__name__ = "DataPoint";
	private $data_dir = "/Users/jkolber/Projects/fb_data/";
	private $data_file_prefix = "jkdp_";
	public $data = null;

	function __create($name = "DataPointObject") { 
		// make an empty DataPoint ojecct
		$this->__name__ = $name;
	}
	function makeKey($token, $uri) {
		return (md5($token. $uri));
	}
	function makeFilename($key) {
		return $this->data_dir . 
			$this->data_file_prefix .
			$key;
	}
	function getFromCache($token, $uri) {
		$key = $this->makeKey($token, $uri);
		$keyfile = $this->makeFilename($key);
		if (file_exists($keyfile)){ 
			$data = json_decode(file_get_contents($keyfile));
			$this->data = $data;
			error_log("got $key from cache ");
			return $data;
		}
		} 
	function storeToCache($token, $uri, $data) {
		$key = $this->makeKey($token, $uri);
		$keyfile = $this->makeFilename($key);
		file_put_contents($keyfile, json_encode($data));		

		error_log("put $key into cache ");
	}

}


