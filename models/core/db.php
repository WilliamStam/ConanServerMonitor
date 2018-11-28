<?php

namespace models\core;

use \timer as timer;


class db extends \models\models {
	private static $instance;
	
	function __construct($table) {
		parent::__construct();
		
		$this->table = $table;
	}
	
	public static function getInstance($table) {
		self::$instance = new self($table);
		
		return self::$instance;
	}
	
	function save($ID, $values = array(), $log = FALSE, $IDfield = 'ID') {
		$timer = new timer();
		$f3 = \Base::instance();
		$return = array();
		
		//debug($values);
		
		
		$loadSearch = array(
			$IDfield . ' = ?',
			$ID,
		);
		
		
		$a = new \DB\SQL\Mapper($f3->get("DB"), $this->table);
		$a->load($loadSearch);
		
		
		$changes = array();
		foreach ( $values as $key => $value ) {
			if ( $value === "" ) {
				$value = NULL;
			}
			if ( is_array($value) ) {
				$value = json_encode($value);
			}
			if ( isset($a->$key) ) {
				if ( $a->$key != $value ) {
					$changes[$key] = array(
						"w" => $a->$key,
						"n" => $value,
					);
				}
			}
		}
		
		
		if ( count($changes) ) {
			foreach ( $values as $key => $value ) {
				if ( $value === "" ) {
					$value = NULL;
				}
				if ( is_array($value) ) {
					$value = json_encode($value);
				}
				if ( isset($a->$key) ) {
					$a->$key = $value;
					
				}
			}
			
			
			if ( isset($a->log) && $log ) {
				$log = (array) json_decode($a->log, TRUE);
				$log[] = array(
					"d" => date("Y-m-d H:i:s"),
					"u" => $f3->get("USER")['ID'],
					"c" => count($changes),
					"l" => $changes,
				);
				$a->log = json_encode($log);
			}
			
			$a->save();
			$ID_ = $ID;
			$ID = ($a->$IDfield) ? $a->$IDfield : $a->_id;
			
			
		}
		
		//		if ($this->table=="cc_tickets_uuid"){
		//			debug_file(array(
		//				"ID"=>$ID_,
		//				"idfield"=>$IDfield,
		//				"values"=>$values,
		//				"changes"=>$changes,
		//				"ID_record"=>$ID,
		//				"records"=>$this->f3->get("DB")->exec("SELECT * FROM {$this->table}")
		//			));
		//		}
		
		
		$timer->_stop("models");
		
		return array(
			"ID" => $ID,
			"changes" => $changes,
		);
	}
	
	function delete($ID, $field = "ID") {
		$timer = new timer();
		$f3 = \Base::instance();
		$user = $f3->get("user");
		
		
		$a = new \DB\SQL\Mapper($f3->get("DB"), $this->table);
		$a->load([
			$field . ' = ?',
			$ID,
		]);
		
		
		if ( !$a->dry() ) {
			$a->deleted = '1';
			$a->deleted_users_ID = $this->f3->get("USER")['ID'];
			$a->deleted_timestamp = date("Y-m-d H:i:s");
			$a->save();
		}
		
		
		$timer->_stop("models");
		
		return "done";
	}
	
	
}
