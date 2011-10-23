<?php
/**
 * Model
 *
 */

require "lib/_data.php";
require "lib/_mongo_data.php";


class Model {
	/**
	 * is true then return an object-ified data
	 * otherwise return whatever you got, presumably
	 * an array
	 */
	private static $return_object = false;

	static function ize($token, $id, $meta_data = false) {

		$fetched = 0;

		if ($meta_data) {
			$qs = "?metadata=1&access_token=$token";
		} else { 
			$qs = "?access_token=$token";
		}

		$dp = new MongoDataPoint();
		$data = $dp->getFromCache($token, $id);
		if (!empty($data)) { 
			if (is_array($data) and self::$return_object) {
				return self::_to_object($data);
			}
			return $data;
		}

		error_log("Fetching $id from Facebook");
		$data = FBUtils::fetchFromFBGraph($id.$qs, $token);
		error_log("fetched from fb $data");

		if ($data) { 
			$dp->storeToCache($token, $id, $data);
			
			if (is_array($data) and self::$return_object) {
				return self::_to_object($data);
			}

			return $data;
		} else {
			throw new Exception("no data returned from graph for $id$qs");
		}
	}

	private static function _to_object($array) {
		$ret = new stdClass();
		foreach($array as $k => $v) {
			// only get string keys
			if (!is_numeric($k)) {
				$ret->$k = $v;
			}
		}
		return $ret;
	}
	public static function setReturnObject($bool) {
		self::$return_object = $bool ? true : false; 
	}
}
