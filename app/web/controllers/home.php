<?php
namespace app\web\controllers;
use \timer;
use \output;
use \models;
class home extends _ {
	function __construct() {
		parent::__construct();
	}

	function page(){
		$timer = new \timer();

//		t = new woof();
		
		$key = isset($_GET['day']) ? $_GET['day'] : date("Ymd");
		
		$days = array_map(function($item){ return array("key"=>$item['daykey'],"label"=>date("d M y",strtotime(substr_replace(substr_replace($item['daykey'], '-', 4,0), '-', 7,0)))); }, models\system\chats::getInstance()->getAll("", "timestamp ASC", "", array("select" => "DISTINCT daykey")));
		
		//debug($days);
		
		$chats = models\system\chats::getInstance()->getAll("daykey = :key","timestamp ASC","",array("args"=>array(":key"=>$key)));
		
		$tmpl = new \template("template.twig");
		$tmpl->page = array(
			"title"=>"Home Page!",
		);
		
		$tmpl->days = $days;
		$tmpl->chats = $chats;
		$tmpl->key = $key;
		
		
//		date("d M y",strtotime($item['daykey']))
		
		
		

		$timer->_stop(__NAMESPACE__);
		return $tmpl->renderPage($this,__FUNCTION__);

	}


}
