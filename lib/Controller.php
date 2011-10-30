<?php
/**
 * Controller
 */
class Controller {

	private $options;

	public function defaultAction() {
		$response->template = "default";
	}
	public function __construct($options = array()) {
		$this->options = $options;
	}
}
