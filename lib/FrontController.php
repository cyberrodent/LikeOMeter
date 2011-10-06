<?php
require_once 'lib/Controller.php';
/**
 * FrontController
 */
class FrontController {

	public static function dispatch($options = null) {

		$request = new StdClass();
		// this is a facebook app so lets make the token part of the request
		$request->token = FBUtils::login(AppInfo::getHome());

		$response = new StdClass();
		$response->template = 'default';

		// strip some common file extensions if present and the query string
		$uri = trim(preg_replace("/(\.(html|asp|php))?(\?.*)?/","",$_SERVER['REQUEST_URI']));

		try {

			if (!$request->token) { 
				throw new Exception("No Facebook Token.");
			}
			list($handler_name, $method) = self::deriveAction($uri);
			require "lib/$handler_name.php";

			$handler = new $handler_name();

			$data = $handler->$method($request, $response);

			require "tpl/". $response->template . ".php";	

		} catch (Exception $e) {
			require 'uhoh.php';
			print "<div class='error'>" . $e->getMessage() . "</div>";
			@ print "uri: $uri<br />"; 
			@ print "handler: $handler_name <br />";
			@ print "method: $method <br />";
			die();
		}
	}


	private static function deriveAction($uri) {
		if (array_key_exists($uri, Urls::$urls)) {
			return Urls::$urls[$uri];
		}	
		throw new Exception("no action could be derived");
	}
}
