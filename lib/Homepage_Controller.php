<?php
/**
 * Homepage Controller
 */
class Homepage_Controller extends Controller {

	private function getAtr($atr, $id, $token)
	{
		$set = array();
		$$atr = Modelize($token, $id."/$atr", null);
		if (isset($$atr->data)) { 
			$set[$id] = $$atr->data;
		} else { 
			echo "unknown ";
			splat($$atr);
		}
		return $set;
	}

	public function Hello($req, $res) {
		$res->template = "newhp";

		$friends = Modelize($req->token, "me/friends",null);

		$likes = array();

		foreach ($friends->data as $friend) {
			if (array_key_exists('id', $friend)) { 

				$like = Modelize($req->token, $friend->id."/likes", null);
				if (isset($like->data)) { 
					 $likes[$friend->id] = $like->data;
				} else { 
					echo "unknown ";
					splat($like);
				}


				$musics = $this->getAtr("music", $friend->id, $req->token);
				$books = $this->getAtr("books", $friend->id, $req->token);
			}
		}



		return (Object)array(
			'name' => 'Jeff',
			'friends' => $friends->data,
			'flikes' => $likes,
		);
	}

	
}
