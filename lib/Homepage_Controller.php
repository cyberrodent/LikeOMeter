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
 * 
 * @author Jeff Kolber 
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
		$_atr = Modelize($token, $id . ($atr ? "/$atr" : '') , null);

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
		$friends = Modelize($req->token, "me/friends",null);

		if (isset($friends->data) and count($friends->data)) { 
			foreach ($friends->data as $friend) {
				if (array_key_exists('id', (array)$friend)) { 
					$likes[$friend->id] = $this->getAtr("likes", $friend->id, $req->token);
				}
			}
		}
		$likes['me'] = $this->getAtr("likes", 'me', $req->token);

		$liked = $this->collateLikes( $likes );

		usort($liked, "compareLikeCounts");
		$liked = array_reverse($liked);
		return (Object)array(
			'name' => 'Jeff',
			'token' => $req->token,
			'friends' => isset($friends->data) ? $friends->data : null,
			'liked' => $liked,
		);

	}
	public function Hello($req, $res) {

		$likes = array();
		$musics = array();
		$books = array();
		$television = array();
		$movies = array();
		$posts = array();

		// for now just a count of all their graph da ta
		$scores = array();

		$res->template = "newhp";

		// this is the list of friends
		$friends = Modelize($req->token, "me/friends",null);
	
//		dumper($req); dumper($friends); 
//			die('Modelized friends');

		if (isset($friends->data) and count($friends->data)) { 
			foreach ($friends->data as $friend) {
				if (array_key_exists('id', (array)$friend)) { 

				$friend = (object)$friend;	

					$core[$friend->id]       = $this->getAtr('', $friend->id, $req->token);  


//				dumper($core); 
//				die('Modelized core');

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


		// get all this data for the current user too
		// FIXME : using 'me' could ufck pu the cache - 2 different me
		$core['me'] = $this->getAtr('', 'me', $req->token);  
		$likes['me'] = $this->getAtr("likes", 'me', $req->token);


		$books['me'] = $this->getAtr("books", 'me', $req->token);
		$movies['me'] = $this->getAtr("movies", 'me', $req->token);
		$musics['me'] = $this->getAtr("music", 'me', $req->token);
		$television['me'] = $this->getAtr("television", 'me', $req->token);


		return (Object)array(
			'name' => 'Jeff',
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
		$head = new StdClass();
		$head->id = 0; // the id of the thing liked
		$head->likers = array(); // users ids who like this
		$head->count = 0; // count of this->likers

		// the stuff we want is an enigma wrapped in an array wrapped in a twinkie
		//  and this is how we have to unwrap it.
		foreach ($all_likes as $uid => $likes) { 
			foreach ($likes as $aLike) {	
				foreach ($aLike as $like) {
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
