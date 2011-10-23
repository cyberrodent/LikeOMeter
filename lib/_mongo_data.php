<?php
/**
 * Mongo Data Driver
 */
class MongoDataPoint extends DataPoint implements _data_point {

	private $__name__ = "MongoDataPoint";
	private $m; // mongo
	private $collection; //
	private $use_cache = true;

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
		error_log("fetching $uri ($key) from MongoDB");	
		$data = $this->db->$collection->findOne(  array("key" => $key) );
		error_log("fetched $key from MongoDB");	
		error_log("got $data");	
		return $data;
	

	}
	function storeToCache($token, $uri, $data) {
		if (empty($data)) { 
			error_log("tried to cache empty data for $uri");
			return; 
		} 
	
		$key = $this->makeKey($token, $uri);

		$data['key'] = $key;

		$collection = "f";
//		$this->db->$collection->ensureIndex(array("key" => 1) , array("unique"=>1));
		$this->db->$collection->insert( (object)$data)  ;
		if ($this->db->$collection->update( array("key" => $key),  (object)$data) ) { 
			error_log("updated mongo");
		} else { 
			die("failes trying updated");
		}
	}

}
