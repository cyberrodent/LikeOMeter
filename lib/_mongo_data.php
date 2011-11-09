<?php
/**
 * Mongo Data Driver
 */
class MongoDataPoint extends DataPoint implements _data_point {

	private $__name__ = "";


	private $db = 'heroku_app1301727';

	private $m; // mongo
	private $collection; //
	private $use_cache = true;
	private $verbose = 1; // log or not
	private $TTLMAXSEC = 3600;
	

	function __construct($name = "MongoDataPoint") {
		$this->__name__ = $name;
		//   mongodb://<user>:<password>@dbh08.mongolab.com:27087/
		$dsn = getenv("MONGOLAB_URI");
	
		try { 
			$this->m = new Mongo($dsn);
			$this->db = $this->m->selectDB($this->db);
		} catch (Exception $e) {
			die("<p>". $e->getMessage() . "</p>cant connect to mongodb");
		}
	}

	function getFromCache($token, $uri) { 

		$key = $this->makeKey($token, $uri);
		$collection = "f";
		$this->verbose and error_log("fetching $uri ($key) from MongoDB");	
		$data = $this->db->$collection->findOne(  array("key" => $key) );

		if (array_key_exists('last_fetch_time', $data)) {

			if ($data['last_fetch_time'] + $this->TTLMAXSEC < mktime()) {
				// data is too old
				// trigger a re-fetch form source
				error_log("* * * * * * * * * * * * * * * * * ");
				error_log("Data too old for $uri  key: " . $key);
				error_log("* * * * * * * * * * * * * * * * * ");
				return false;
			} 
		} else { 

			error_log("- - - - - - - - - - - - - - - - - ");
			error_log("no last fetch time for $uri  key: " . $key);
			error_log("- - - - - - - - - - - - - - - - - ");
			return false;

		}

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
		$now = mktime();
		$data['key'] = $key;
		$data['uri'] = $uri;
		$data['last_fetch_time'] = $now;

		$collection = "f";

		// $this->db->$collection->ensureIndex(array("key" => 1) , array("unique"=>1));
		if ($this->db->$collection->update( array("key" => $key), $data, true) ) { 
			$this->verbose and error_log("Upsert OK for $uri");
		} else { 
			die("faileds " . __CLASS__ ."::".__FUNCTION__ ." $uri $token");
		}
	}

}
