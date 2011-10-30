<?php
require_once 'lib/Controller.php';
/**
 * FrontController
 */
class FrontController {


	public static $mode = 'online'; // online or offline

	public static function dispatch($options = null) {

		// we create a request and response object 
		// that we use to handle the client request

		$request = new StdClass();
		// this is a facebook app so lets make the token part of our request object
		if (self::$mode == 'online') { 
			$request->token = FBUtils::login(AppInfo::getHome());
		} else {
			error_log("Issued mock token");
			$request->token = 'mock token';
		}


		$response = new StdClass();
		$response->template = 'default';

		// strip some common file extensions if present and the query string
		$uri = trim(preg_replace("/(^\/)?(\.(html|asp|php))?(\?.*)?/","",$_SERVER['REQUEST_URI']));

		try {

			if (!$request->token) { 
				throw new Exception("No Facebook Token.");
			}

			list($handler_name, $method, $options, $matches) = self::deriveAction($uri);


			require "lib/$handler_name.php";
			$handler = new $handler_name($options);
			$data = $handler->$method($request, $response);

			require "tpl/".  "header.php";	
			require "tpl/". $response->template . ".php";	
			require "tpl/". "footer.php";


		} catch (Exception $e) {
			require 'uhoh.php';
			print "<div class='error'>" . $e->getMessage() . "</div>";
			@ print "uri: $uri<br />"; 
			@ print "handler: $handler_name <br />";
			@ print "method: $method <br />";
			// print debug_backtrace();
			die();
		}
	}


	private static function deriveAction($uri) {
		//if (array_key_exists($uri, Urls::$urls)) {
		//	return Urls::$urls[$uri];
		//}
		foreach (Urls::$urls as $route => $setup) {
			$matches = null;
			if (preg_match($route, $uri, $matches)) {
				Urls::$urls[$route][] = $matches;
				return Urls::$urls[$route];
			}
		}
		throw new Exception("no action could be derived");
	}
}
