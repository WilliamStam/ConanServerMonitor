<?php
namespace app\web\controllers;
use \timer;
use \output;
use \models;
class players extends _ {
	function __construct() {
		parent::__construct();
	}

	function page(){
		$timer = new \timer();

//		t = new woof();
		
		
		$tmpl = new \template("template.twig");
		$tmpl->page = array(
			"title"=>"Server Players",
		);
		
		
		
//		date("d M y",strtotime($item['daykey']))
		
		
		

		$timer->_stop(__NAMESPACE__);
		return $tmpl->renderPage($this,__FUNCTION__);

	}


}
