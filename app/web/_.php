<?php
namespace app\web;
use \timer as timer;
/* INFO: execute the routes method on include */


class _ {
	private static $instance;
	function __construct() {
		$this->f3 = \Base::instance();
		$this->cfg = $this->f3->get("CFG");
	}

	function routes(){

		$this->f3->route('GET /php', function($f3, $params) {
			echo phpinfo();
			exit();
		});
		
		
		$this->f3->route("GET / [ajax]", function($app, $params) {
			$app->call("\\app\\web\\controllers\\data\\home->page");
		});
		$this->f3->route("GET / [sync]", function($app, $params) {
			if ( isset($_GET['debug']) ) {
				$app->call("\\app\\web\\controllers\\data\\home->page");
			} else {
				$app->call("\\app\\web\\controllers\\home->page");
			}
		});
		
		$this->f3->route("GET /chat [ajax]", function($app, $params) {
			$app->call("\\app\\web\\controllers\\data\\home->page");
		});
		$this->f3->route("GET /chat [sync]", function($app, $params) {
			if ( isset($_GET['debug']) ) {
				$app->call("\\app\\web\\controllers\\data\\home->page");
			} else {
				$app->call("\\app\\web\\controllers\\home->page");
			}
		});
		
		$this->f3->route("GET /players [ajax]", function($app, $params) {
			$app->call("\\app\\web\\controllers\\data\\players->page");
		});
		$this->f3->route("GET /players [sync]", function($app, $params) {
			if ( isset($_GET['debug']) ) {
				$app->call("\\app\\web\\controllers\\data\\players->page");
			} else {
				$app->call("\\app\\web\\controllers\\players->page");
			}
		});






	}


}
