<?php
/**
 * FrontController
 */
class FrontController {
	public static function dispatch($options = null) {
		try {
			$request = $_REQUEST;
			$response = new StdClass();
			$response->template = 'default';

			$uri = preg_replace('/\.(php|html|asp)$/',"",$_SERVER['REQUEST_URI']);

			list( $handler_name, $method) = ( self::deriveAction($uri) );

			require "lib/$handler_name.php";
			$handler = new $handler_name();
			$data = $handler->$method($request, $response);
			require "tpl/". $response->template . ".php";	

		} catch (Exception $e) {
			require 'uhoh.php';
			print $e->getMessage();
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
