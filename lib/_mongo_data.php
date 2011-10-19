<?php
/**
 * Mongo Data Driver
 */
class MongoDataPoint extends DataPoint implements _data_point {

	private $__name__ = "MongoDataPoint";
	private $m; // mongo
	private $collection; //

	function __construct($name = "MongoDataPoint") {
		$this->__name__ = $name;
		//   mongodb://<user>:<password>@dbh08.mongolab.com:27087/i
		$user = "operator";
		$pass = "canyouhelpme";
		$db = "fbdata";
		try { 
			$this->m = new Mongo("mongodb://$user:$pass@dbh63.mongolab.com:27637/$db");
		} catch (Exception $e) {
			print $e->getMessage();
			die("cant connect to mongodb");
		}
		$this->collection = $this->m->$db;
	}

	function getFromCache($token, $id) { 
		return;	

	}
	function storeToCache($token, $uri, $data) {
		dumper($uri);
		splat($data);	

	}

}
