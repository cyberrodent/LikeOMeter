<?php
/**
 * Homepage Controller
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
			//echo "unknown ";
			//splat($_atr);
		}
		return $set;
	}

	public function Hello($req, $res) {

		$likes = array();
		$musics = array();
		$books = array();
		$television = array();
		$posts = array();

		$scores = array();

		$res->template = "newhp";

		// this is the list of friends
		$friends = Modelize($req->token, "me/friends",null);


		if (isset($friends->data) and count($friends->data)) { 
			foreach ($friends->data as $friend) {
				if (array_key_exists('id', (array)$friend)) { 

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
//							$core[$friend->id],
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
}
