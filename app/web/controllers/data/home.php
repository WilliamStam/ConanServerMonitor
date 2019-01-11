<?php

namespace app\web\controllers\data;

use \timer as timer;
use \models;
use \strings;
use \arrays;
use \output;

class home extends _ {
	function __construct() {
		parent::__construct();
	}
	
	function page() {
		$timer = new \timer();
		$key = isset($_GET['day']) ? $_GET['day'] : date("Ymd");
		$return = array(
			"key" => $key,
			"label" => date("d F Y",strtotime(substr_replace(substr_replace($key, '-', 4,0), '-', 7,0))),
		);
		
		$days = array_map(function($item) {
			return  $item['daykey'];
		}, models\system\chats::getInstance()->getAll("", "chats.timestamp ASC", "", array("select" => "DISTINCT chats.daykey")));
		
		
		$return['days'] = $days;
		
		
		$return['chats'] = models\system\chats::getInstance()->getAll("chats.daykey = :key", "chats.timestamp ASC", "", array("args" => array(":key" => $key)));
		
		
		
		$timer->_stop(__NAMESPACE__);
		
		return output::json($return);
	}
	
	
}
