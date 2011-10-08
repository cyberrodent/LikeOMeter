<?php
/**
 * Homepage Controller
 */
class Homepage_Controller extends Controller {

	private function getAtr($atr, $id, $token)
	{
		$set = array();
		$_atr = Modelize($token, $id."/$atr", null);
		if (isset($_atr->data)) { 
			$set[$id] = $_atr->data;
		} else { 
			echo "unknown ";
			splat($_atr);
		}
		return $set;
	}

	public function Hello($req, $res) {
		$res->template = "newhp";

		$friends = Modelize($req->token, "me/friends",null);

		$likes = array();
		$musics = array();
		$books = array();

		foreach ($friends->data as $friend) {
			if (array_key_exists('id', (array)$friend)) { 

		//		$like = Modelize($req->token, $friend->id."/likes", null);
		//		if (isset($like->data)) { 
		//			 $likes[$friend->id] = $like->data;
		//		} else { 
		//			echo "unknown ";
		//			splat($like);
		//		}

				$likes[$friend->id] = $this->getAtr("likes", $friend->id, $req->token);
				$books[$friend->id] = $this->getAtr("books", $friend->id, $req->token);
				$musics[$friend->id] = $this->getAtr("music", $friend->id, $req->token);

			}
		}



		return (Object)array(
			'name' => 'Jeff',
			'friends' => $friends->data,
			'likes' => $likes,
			'books' => $books,
			'musics' => $musics
		);
	}

	
}
