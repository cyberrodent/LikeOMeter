<?php
/**
 * Model
 *
 */

require "lib/_data.php";
require "lib/_mongo_data.php";

	function Modelize($token, $id, $meta_data = false) {

		$fetched = 0;

		if ($meta_data) {
			$qs = "?metadata=1&access_token=$token";
		} else { 
			$qs = "?access_token=$token";
		}

		// cache doesn't know about metadata option
		$dp = new MongoDataPoint();

		if ($data = $dp->getFromCache($token, $id)) {
			return $data;
		}

		$data = FBUtils::fetchFromFBGraph($id.$qs, $token);

		if ($data) { 
			$dp->storeToCache($token, $id, $data);
			return (object)$data;
		} else {
			throw new Exception("no data returned from graph");
		}

	}
