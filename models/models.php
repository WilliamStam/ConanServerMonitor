<?php
namespace models;
use \timer as timer;

class models {
	private static $instance;
	function __construct() {
		$this->f3 = \Base::instance();
		$this->user = $this->f3->get("USER");
		$this->cfg = $this->f3->get("CFG");



	}

	

}
