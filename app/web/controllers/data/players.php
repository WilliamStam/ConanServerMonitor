<?php

namespace app\web\controllers\data;

use \timer as timer;
use \models;
use \strings;
use \arrays;
use \output;

class players extends _ {
	function __construct() {
		parent::__construct();
	}
	
	function page() {
		$timer = new \timer();
		$search = isset($_GET['search']) ? $_GET['search'] : "";
		
		$args = array();
		$where = "1";
		
		if ( $search ) {
			$where .= " AND players.`player` LIKE :search";
			$args[':search'] = '%'.$search.'%';
		}
		
		$select = "players.*, (SELECT count(chats.ID) FROM chats WHERE chats.playerID = players.ID) AS chats, (SELECT count(ips.ID) FROM ips WHERE ips.playerID = players.ID) AS ips";
		
		
		$return = array(
			"search" => $search,
			"list"=>models\system\players::getInstance()->getAll($where,"players.player ASC","",array("args"=>$args,"select"=>$select))
		);
		
		
		$sharedips = $this->f3->get("DB")->exec("SELECT ip, CONCAT(
  '[',GROUP_CONCAT(
  JSON_OBJECT(
    'ID', playerID,
		'player', player
	)
),']') AS players, count(players.ID) as c FROM `ips` LEFT JOIN players ON players.ID = ips.playerID
GROUP BY ip
HAVING c > 1");
		
//		debug($sharedips);
		
		
		
		
		$sharedips = array_map(function($item) {
			
			$item['players'] = json_decode($item['players'],true);
			usort($item['players'], function($a, $b) {
				return $a['player']<=>$b['player'];
			});
			
			
			return $item;
		},$sharedips);
		
		$t = array();
		
		
		
		
		
		
		
//		debug($sharedips);
		
		
		
		$return['sharedips'] =$sharedips;
		
		
			$timer->_stop(__NAMESPACE__);
		
		return output::json($return);
	}
	
	
}
