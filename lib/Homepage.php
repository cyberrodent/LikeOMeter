<?php
/**
 * your file
 */
class Homepage {

	public function Hello($request, $response) {
		$response->template = "newhp";	
		return array(
			'name' => 'Jeff'
		);

	}
}
