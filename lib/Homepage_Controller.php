<?php
/**
 * Homepage Controller
 */


/**
 * compareLikeCounts 
 * 
 * @param stdClass $like1 
 * @param stdClass $like2 
 * @access public
 * @return -1 or 1 for use with usort
 */
function compareLikeCounts(stdClass $like1, stdClass $like2) {
	return ($like1->count > $like2->count) ? 1 : -1;
}



/**
 * Homepage_Controller 
 */
class Homepage_Controller extends Controller {
	

	/**
	 * getAtr 
	 * 
	 * @param mixed $atr 
	 * @param mixed $id 
	 * @param mixed $token 
	 * @return array of results from the graph
	 */
	private function getAtr($atr, $id, $token)
	{
		$set = array();
		$_atr = Model::ize($token, $id . ($atr ? "/$atr" : '') , null);

		if (isset($_atr->data)) { 
			$set[$id] = $_atr->data;
		} else { 
			$set[$id] = $_atr;
		}

		return $set;
	}

	public function youAndYourFriendsLike($req, $res) {

		$likes = array();

		$res->template = "flikes";
		// this is the list of friends
		$friends = Model::ize($req->token, "me/friends",null);
		$friends = (array)$friends;

		if (array_key_exists('data', $friends) and count($friends['data'])) {
			foreach ($friends['data'] as $friend) {
				if (array_key_exists('id', $friend)) { 
					$likes[$friend['id']] = $this->getAtr("likes", $friend['id'], $req->token);
				}
			}
		}

		Model::setReturnObject(true);
		$me = Model::ize($req->token, "me", null);
		Model::setReturnObject(false);

		$likes[$me->id] = $this->getAtr("likes", $me->id, $req->token);

		$friend_idx = keyById($friends['data']);

		$friend_idx[$me->id] =  array(
			'id' => $me->id,
			'name' => $me->name
			);

		$liked = $this->collateLikes( $likes );

		usort($liked, "compareLikeCounts");
		$liked = array_reverse($liked);


		$all_liked = $liked;
		$top = array_splice($liked,0,20);


		foreach ($top as $pos => $thing) {
			$graph = $this->getAtr(false , $thing->id, $req->token);
			$thing->graph = $graph[ $thing->id];
		}

		return (Object)array(
			'name' => 'Jeff',
			'token' => $req->token,
			'friend_idx' => $friend_idx,	
			'top' => $top,
		);

	}
	public function Hello($req, $res) {
		$CONF = array();
		$CONF['dofriends'] = false; // 
		/* 
		 *
		 * memcache test
		 * 
		 
		require_once("./lib/_memcache.php");
		$mc = MC::getInstance();
		$mc->set('key1','yo mamma');
		$test = $mc->get('key1');
		dumper($test);
		*/	



		$likes = array();
		$musics = array();
		$books = array();
		$television = array();
		$movies = array();
		$posts = array();

		// for now just a count of all their graph da ta
		$scores = array();

		$res->template = "newhp";

		Model::setReturnObject(true);

		$me = Model::ize($req->token, "me", null);
		$meid = $me->id;
		// error_log("$meid    User: " . $me->name . "    " . __FUNCTION__);


		if ($CONF['dofriends']) {

	
			$friends = Model::ize($req->token, "$meid/friends",null);
			if (isset($friends->data) and count($friends->data)) { 
				foreach ($friends->data as $friend) {
					if (array_key_exists('id', (array)$friend)) { 

						$friend = (object)$friend;	

						$core[$friend->id]       = $this->getAtr('', $friend->id, $req->token);  

						$likes[$friend->id]      = $this->getAtr("likes", $friend->id, $req->token);
						$books[$friend->id]      = $this->getAtr("books", $friend->id, $req->token);
						$movies[$friend->id]     = $this->getAtr("movies", $friend->id, $req->token);
						$musics[$friend->id]     = $this->getAtr("music", $friend->id, $req->token);
						$television[$friend->id] = $this->getAtr("television", $friend->id, $req->token);
						// $posts[$friend->id] = $this->getAtr("posts", $friend->id, $req->token);

						// calculate some sort of score for this friend...
						$score = $this->calculateScore(
							array(
								//	$core[$friend->id],
								$likes[$friend->id],
								$books[$friend->id],
								$movies[$friend->id],
								$musics[$friend->id],
								$television[$friend->id],
							)
						);
						$scores[$friend->id]  = $score;
					}
				}
			}
		}
		// get all this data for the current user too
		$core[$meid] = $this->getAtr('', $meid, $req->token);  
		$likes[$meid] = $this->getAtr("likes", $meid, $req->token);


		$books[$meid] = $this->getAtr("books", $meid, $req->token);
		$movies[$meid] = $this->getAtr("movies", $meid, $req->token);
		$musics[$meid] = $this->getAtr("music", $meid, $req->token);
		$television[$meid] = $this->getAtr("television", $meid, $req->token);


		return (Object)array(
			'name' => $me->name,
			'meid' => $me->id,
			'token' => $req->token,
			'friends' => isset($friends->data) ? $friends->data : null,
			'likes' => $likes,
			'books' => $books,
			'musics' => $musics,
			'posts' => $posts,
			'movies' => $movies,
			'core' => $core,
			'television' => $television,
			'scores' => $scores,
		);
	}
	public function calculateScore($stuff) {
		$score = 0;

		foreach ($stuff as $att) { 
			$key = array_keys($att);
			$score += count($att[$key[0]]);
		}
		return $score;
	}



	/**
	 * collateLikes 
	 * 
	 * @param mixed $all_likes 
	 * @return void
	 */
	public function collateLikes($all_likes) {
		$liked = array(); // an array of stdClass objs tracking our friend's like

		// the idea here was to find the most liked as we go ... on hold for now
		// $head = new StdClass();
		// $head->id = 0; // the id of the thing liked
		// $head->likers = array(); // users ids who like this
		// $head->count = 0; // count of this->likers



		// the stuff we want is an enigma wrapped in an array wrapped in a twinkie
		//  and this is how we have to unwrap it.
/*
Array (
    [106412] => Array
        (
            [106412] => Array
                (
                    [_id] => MongoId Object
                        (
                            [$id] => 4ea42a5b584baffb37000002
                        )

                    [data] => Array
                        (
                            [0] => Array
                                (
                                    [name] => The State
                                    [category] => Tv show
                                    [id] => 10042595019
                                    [created_time] => 2011-07-27T22:25:11+0000
                                )

                            [1] => Array
                                (
                                    [name] => Dog Bless You
                                    [category] => Non-profit organization
                                    [id] => 114790481886534
                                    [created_time] => 2011-06-03T18:00:38+0000
                                )
etc...
*/
		foreach ($all_likes as $uid => $likes) { 
			foreach ($likes as $aLike) {	
				foreach ($aLike['data'] as $like) {
					$like = (object)$like;
					if (!array_key_exists($like->id, $liked)) {
						$myO = new stdClass();
						$myO->id = $like->id;
						$myO->category = $like->category;
						$myO->name = $like->name;
						if (!property_exists($myO, 'likers')) {
							$myO->likers = array();
						}
						$liked[$like->id] = $myO;
					} 
					$myO = $liked[$like->id];
					array_push($myO->likers, $uid);
					$myO->count = count($myO->likers);
					$liked[$like->id] = $myO;

					/* on hold for now: keep track of the most pop
					 *
					if (count($liked[$like->id]) >= $head->count) {
						$head->count = count($liked[$like->id]);
						// this isn't always going to work FIXME
							array_push($head->likers, $uid);
							$head->id = $like->id;
					}
					 *
					 */	
				}
			}
		}
		return $liked;
	}
}
