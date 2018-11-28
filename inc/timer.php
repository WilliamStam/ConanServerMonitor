<?php

class timer {
	private $startTimer;
	private $endTimer;
	private $totalTime;
	private $force;
	private $noDEBUG;
	
	function __construct($force = FALSE) {
		$this->force = $force;
		$this->f3 = Base::instance();
		if ( !isset($this->f3->TIMER) ) {
			$this->f3->TIMER = array();
		}
		
		
		$this->_start();
	}
	
	private function _start() {
		
		$mtime = microtime();
		$mtime = explode(" ", $mtime);
		$this->startTimer = $mtime[1] + $mtime[0];
		
	}
	
	
	function _stopTimer() {
		
		$mtime = microtime();
		$mtime = explode(" ", $mtime);
		$mtime = $mtime[1] + $mtime[0];
		$this->endTimer = $mtime;
		
		
		if ( $this->endTimer && $this->startTimer ) {
			$return = $this->endTimer - $this->startTimer;
		} else {
			$return = FALSE;
		}
		
		
		return self::shortenTimer($this->totalTimer = $return);
	}
	
	function stop($msg = "") {
		
		$timerStop = $this->_stopTimer();
		
		
		if ( $this->f3->DEBUG && $msg ) {
			$bt = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 3);
			$caller = $bt[0];
			
			if ( !isset($this->f3->OUTPUT['DEBUG']["general"]) ) {
				$this->f3->OUTPUT['DEBUG']["general"] = array();
			}
			
			
			if ( isset($caller['file']) ) {
				//				debug($caller);
				$this->f3->OUTPUT['DEBUG']["general"][] = array(
					"msg" => $msg,
					"time" => $timerStop,
					"file" => $caller['file'],
					"line" => $caller['line'],
				);
			} else {
				$this->f3->OUTPUT['DEBUG']["general"][] = array(
					"msg" => $msg,
					"time" => $timerStop,
				);
			}
			
		}
		
		
		return $timerStop;
	}
	
	function _stop($group, $replacements = array()) {
		
		$time = $this->_stopTimer();
		
		if ( $this->f3->DEBUG ) {
			$bt = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 3);
			$caller = $bt[1];
			
			$caller = array_replace_recursive((array) $caller, (array) $replacements);
			$return = array(
				"class" => $caller['class'],
				"function" => $caller['function'],
				"file" => $caller['file'],
				"line" => $caller['line'],
				"args" => $caller['args'],
				"object" => $caller['object'] ?? "",
				"time" => $time,
			);
			
			
			if ( !isset($this->f3->OUTPUT['DEBUG'][$group][$caller['class']][$caller['function']]) ) {
				$this->f3->OUTPUT['DEBUG'][$group][$caller['class']][$caller['function']] = array(
					"time" => 0,
					"file" => $caller['file'],
					"line" => $caller['line'],
					"count" => 0,
				);
			}
			
			
			$this->f3->OUTPUT['DEBUG'][$group][$caller['class']][$caller['function']]['time'] = self::shortenTimer($this->f3->OUTPUT['DEBUG'][$group][$caller['class']][$caller['function']]['time'] + $return['time']);
			$this->f3->OUTPUT['DEBUG'][$group][$caller['class']][$caller['function']]['count'] = $this->f3->OUTPUT['DEBUG'][$group][$caller['class']][$caller['function']]['count'] + 1;
			
			
		}
		
		
		return $time;
		
	}
	
	
	static public function shortenTimer($time) {
		$t = number_format((float) $time, 5, '.', '');
		
		return $t;
	}
}

