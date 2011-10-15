<?php
/**
 * Model
 *
 */

require "lib/_data.php";


	function Modelize($token, $id, $meta_data = false) {

		$fetched = 0;

		if ($meta_data) {
			$qs = "?metadata=1&access_token=$token";
		} else { 
			$qs = "?access_token=$token";
		}

		// cache doesn't know about metadata option
		$dp = new DataPoint();
		if ($data = $dp->getFromCache($token, $id)) {
			return $data;
		}

		$data = FBUtils::fetchFromFBGraph($id.$qs, $token);
		if ($data) { 
			$dp->storeToCache($token, $id, $data);
		}   // else log an error? 

		return (object)$data;
	}
