<?php
/**
 * Common Interest Controller
 */

class Common_Interest_Controller extends Controller {

	public function Show($req, $res) {
		/* this shows a set of users who like the same things that you do */
		$res->template = "common_interest";


		// get_what_you_like
		
		// foreach friend I have, compare their likes to mine
		//		if there are common likes then add this friend to our set of friends
		//		who like the same stuff as me. Add the thing to a list of things
		//		our friends like too
		//
		//




	}

}
