<?php


function compare_common_likes($a,$b){
	$ca = count($a['likers']);
	$cb = count($b['likers']);
	if ($ca < $cb) {
		return 1;
	} else if  ($ca == $cb) {
		return 0;
	} else { 
		return -1;
	}
}

/**
 * Common Interest Controller
 */

class Common_Interest_Controller extends Controller {

	public function Show($req, $res) {
		/* this shows a set of users who like the same things that you do */
		$res->template = "common_interest";
		$data = new StdClass();
		// get_what_you_like
		Model::setReturnObject(true);
		$likes = Model::ize($req->token, "me/likes",0);
		$friends = Model::ize($req->token,
			"me/friends",0);
		Model::setReturnObject(false);
		$i_like = array();
		$my_friends = $friends->data;
		$data->friends = keyById($my_friends);
		foreach($likes->data as $like) {
			$i_like[] = $like['id'];
		}

		$common_likes = array();
		/* 
		 * looks like: 
		 * id_of_the_liked_thing => array(of the user_ids who also like this)
		 */
		$liked_things = array();
		/* id_of_the_liked_thing => array(of details about this liked thing) */

		foreach ($my_friends as $friend) {
			// get the things this friend likes
			Model::setReturnObject(true);
			$friend_likes = Model::ize($req->token,
				$friend['id'] ."/likes",
				0
			);

			foreach ($friend_likes->data as $tafl /* thing a friend likes */) {
				if (in_array($tafl['id'], $i_like)) {
					if (array_key_exists($tafl['id'], $common_likes)) { 
						$common_likes[$tafl['id']]['likers'][] = $friend['id'];
					} else {
						$common_likes[$tafl['id']] = $tafl;
						$common_likes[$tafl['id']]['likers'] = array($friend['id']);
					}
				}
			}
		}

		usort($common_likes, 'compare_common_likes');
		$data->common = $common_likes;
		return $data;
	}
}

