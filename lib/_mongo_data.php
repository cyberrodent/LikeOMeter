<?php
/**
 * Mongo Data Driver
 */
class MongoDataPoint extends DataPoint implements _data_point {

	private $__name__ = "MongoDataPoint";
	private $m; // mongo
	private $collection; //
	private $use_cache = true;
	private $verbose; // log or not

	function __construct($name = "MongoDataPoint") {
		$this->__name__ = $name;
		//   mongodb://<user>:<password>@dbh08.mongolab.com:27087/
		$dsn = getenv("MONGOLAB_URI");
	
		try { 
			$this->m = new Mongo($dsn);
			$this->db = $this->m->selectDB('heroku_app1301727');
		} catch (Exception $e) {
			print $e->getMessage();
			die("cant connect to mongodb");
		}
	}

	function getFromCache($token, $uri) { 

		$key = $this->makeKey($token, $uri);
		$collection = "f";
		$this->verbose and error_log("fetching $uri ($key) from MongoDB");	
		$data = $this->db->$collection->findOne(  array("key" => $key) );
		$this->verbose and error_log("fetched $key from MongoDB");	
		$this->verbose and error_log("got $data");	
		return $data;
	

	}
	function storeToCache($token, $uri, $data) {
		if (empty($data)) { 
			error_log("tried to cache empty data for $uri");
			return; 
		} 
	
		$key = $this->makeKey($token, $uri);

		$data['key'] = $key;
		$data['uri'] = $uri;

		$collection = "f";

		// $this->db->$collection->ensureIndex(array("key" => 1) , array("unique"=>1));
		$ok = $this->db->$collection->insert( (object)$data)  ;
		if ($ok) {
			$this->verbose and error_log("Insert OK for $uri");
		} else if ($this->db->$collection->update( array("key" => $key),  (object)$data) ) { 
			$this->verbose and error_log("Update OK for $uri");
		} else { 
			die("faileds " . __CLASS__ ."::".__FUNCTION__ ." $uri $token");
		}
	}

}
