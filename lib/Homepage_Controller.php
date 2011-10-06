<?php
/**
 * Homepage Controller
 */
class Homepage_Controller extends Controller {

	public function Hello($request, $response) {
		$response->template = "newhp";
		return (Object)array(
			'name' => 'Jeff'
		);

	}
}
