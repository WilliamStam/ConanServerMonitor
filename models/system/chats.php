<?php

namespace models\system;

use \timer as timer;
use \models\core\db as DB;


class chats extends \models\models {
	
	private static $instance;
	
	function __construct() {
		parent::__construct();
		
		$this->table = "chats";
		$this->result = array();
	}
	
	public static function getInstance() {
		if ( is_null(self::$instance) ) {
			self::$instance = new self();
		}
		
		return self::$instance;
	}
	
	function get($ID, $options = array()) {
		$timer = new timer();
		$where = $this->table . ".ID = '$ID'";
		$result = array();
		
		if ( $options['orgID'] ) {
			$where = $where . " AND {$this->table}.orgID = '{$options['orgID']}'";
		}
		
		
		$result = $this->getData($where, $this->table . ".ID DESC", "0,1", $options);
		
		
		if ( count($result) ) {
			$return = $result[0];
			
		} else {
			$return = parent::dbStructure($this->table);
		}
		
		if ( $options['format'] !== FALSE ) {
			$return = $this->format($return, $options);
		}
		//test_array($return);
		$timer->_stop("models");
		
		return $return;
	}
	
	
	public function getAll($where = "", $orderby = "", $limit = "", $options = array()) {
		$timer = new timer();
		$result = $this->getData($where, $orderby, $limit, $options);
		$result = $this->format($result, $options);
		$timer->_stop("models");
		
		return $result;
		
	}
	
	public function getCount($where = "", $options = array()) {
		$timer = new timer();
		$options['count'] = TRUE;
		$options['select'] = $options['select'] . ", COUNT({$this->table}.ID) AS c";
		
		$options['select'] = trim($options['select'], ",");
		$result = $this->getData($where, "", "", $options);
		
		
		if ( isset($options['groupby']) ) {
		
		} else {
			$result = $result[0]['c'];
		}
		
		$timer->_stop("models");
		
		return $result;
		
	}
	
	public function getData($where = "", $orderby = "", $limit = "", $options = array()) {
		$f3 = \Base::instance();
		
		$where_ = "WHERE {$this->table}.deleted !='1'   ";
		if ( isset($options['includeDeleted']) && $options['includeDeleted'] ) {
			$where_ = "WHERE 1   ";
		}
		if ( $where ) {
			$where_ = $where_ . " AND (" . $where . ") ";
		}
		
		
		$groupby = "";
		if ( isset($options['groupby']) ) {
			$groupby = "GROUP BY " . $options['groupby'];
		}
		if ( $orderby ) {
			$orderby = " ORDER BY " . $orderby;
		}
		if ( $limit ) {
			$limit = " LIMIT " . $limit;
		}
		
		$args = "";
		if ( isset($options['args']) ) {
			$args = $options['args'];
		}
		
		$ttl = "";
		if ( isset($options['ttl']) ) {
			$ttl = $options['ttl'];
		}
		
		
		
		if ( isset($options['select']) ) {
			$select = $options['select'];
		} else {
			$select = "*";
			
			
		}
		
		
		$sql = "
			 SELECT $select
			 FROM {$this->table}
			 
			$where_ 
			$groupby
			$orderby
			$limit;
		";
		
		//	test_string($sql);
		
		$result = $f3->get("DB")->exec($sql, $args, $ttl);
		
		$return = $result;
		
		return $return;
	}
	
	
	public function save($ID, $values = array()) {
		$result = DB::getInstance($this->table)->save($ID, $values, TRUE);
		
		return $result;
	}
	
	
	public function delete($ID) {
		return DB::getInstance($this->table)->delete($ID);
	}
	
	
	static function format($data, $options = array()) {
		$timer = new timer();
		$single = FALSE;
		//	test_array($items);
		if ( isset($data['ID']) ) {
			$single = TRUE;
			$data = array($data);
		}
		
		
		//	test_array($data);
		//test_array($data);
		
		$i = 1;
		$n = array();
		foreach ( (array) $data as $item ) {
			unset($item['log']);
			unset($item['deleted']);
			
			$item['timestamp'] = \strings::timestamp($item['timestamp']);
			
			
			$n[] = $item;
		}
		
		if ( $single ) {
			$n = $n[0];
		}
		
		
		$return = $n;
		
		
		//test_array($n); 
		
		
		$timer->_stop("models");
		
		return $return;
	}
	

	
	
}
