<?php
/**
 * Common Interest Controller
 */

class Common_Interest_Controller extends Controller {

	public function Show($req, $res) {
		/* this shows a set of users who like the same things that you do */
		$res->template = "common_interest";

		// get_what_you_like
		Model::setReturnObject(true);
		$likes = Model::ize($req->token, "me/likes",0);
		$friends = Model::ize($req->token,
			"me/friends",0);
		Model::setReturnObject(false);
		$i_like = array();
		$my_friends = $friends->data;

		foreach($likes->data as $like) {
			$i_like[] = $like['id'];
		}

		$common_likes = array();

		foreach($my_friends as $friend) {
			// get the things this friend likes
			Model::setReturnObject(true);
			$friend_likes = Model::ize($req->token,
				$friend['id'] ."/likes",
				0
			);

			foreach($friend_likes->data as $tafl /* thing a friend likes */) {
				if (in_array($tafl['id'], $i_like)) {
					print $friend['name']." and you both like ".$tafl['name']."<br />";
					if (array_key_exists($tafl['id'], $common_likes) ) { 
						$common_likes[$tafl['id']][] = $friend['id'];
					} else {
						$common_likes[$tafl['id']] = array($friend['id']);
					}
				}
			}
		}
		dumper($common_likes);
	}
}
