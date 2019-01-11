<?php

class errors {
	private $error;
	
	function __construct() {
		$this->f3 = \Base::instance();
		
		
	}
	
	function init($error) {
		$timer = new timer();
		
		/* INFO: we need to be able to have custom errors for certain codes. simply add methods _<code>() here for them */
		if ( method_exists($this, "_" . $error['code']) ) {
			$this->{"_" . $error['code']}();
		} else {
			
			$e = $this->f3->get('EXCEPTION');
			
			// INFO: There isn't an exception when calling `Base->error()`.
			if ( !$e instanceof Throwable) {
				//debug($this->error);
				
				$text = $error['text'];
				$trace = $this->f3->trace(NULL, FALSE);
				
				
				array_shift($trace);
				array_shift($trace);
				array_shift($trace);
				array_shift($trace);
				array_shift($trace);
				
				$trace = $trace[0];
				
			
				
				/* INFO: we need to try add in an exception for F3's ->error() */
				
				try {
					throw new ErrorException($text, $error['code'], $error['level'], $trace['file'], $trace['line']);
				} catch ( \Throwable $e ) {
					$this->log($e);
				}
				
				
			} else {
				$this->log($e);
			}
			
			
		}
		
		
		$timer->_stop("errors");
	}
	
	function error($code,$text='',array $trace=NULL,$level=0) {
		
		try {
			$trace = $this->f3->trace($trace, FALSE);
			$trace = $trace[0];
			throw new ErrorException($text, $code,$level, $trace['file'], $trace['line']);
		} catch ( \Throwable $e ) {
			$this->log($e);
		}
		
		
	}
	
	
	
	function production(Throwable $exception) {
		$timer = new timer();
		$message = $exception->getMessage();
		$file = $exception->getFile();
		$line = $exception->getLine();
		$code = $exception->getCode();
		$trace = $exception->getTrace();
		
		//		debug($this->f3->get("CLI"));
		
		
		
		
		
		if ( $this->f3->get("CLI") ) {
			
			
			echo "ERROR:" . $message . PHP_EOL;
		} else {
			
			
			$this->f3->OUTPUT['STATUS'] = 500;
			$this->f3->OUTPUT['ERRORS'] = array(
				"The engine had a moment",
				"We hit a bridge at high speed, a team of forensic hamsters have been dispatched to scour through the wreckage and attempt to make sense of the situation",
			);
			$this->f3->OUTPUT['RESPONSE'] = array(
				"type" => "error",
			);
			
			
			$timer->_stop("errors");
			echo \views\renderer::getInstance($this->f3->OUTPUT)->output($this->f3->get("PARAMS['FORMAT']"));
			//exit();
			
		}
		
		
		$timer->_stop("errors");
		
	}
	
	
	function log(Throwable $exception) {
		$timer = new timer();
		
		$message = $exception->getMessage();
		$file = $exception->getFile();
		$line = $exception->getLine();
		$code = $exception->getCode();
		$trace = $exception->getTrace();
		
		$build = $this->f3->get("BUILD");
		
		$payload = array(
			"message" => $message,
			"code" => $code,
			"file" => $file,
			"line" => $line,
			"timestamp" => date("Y-m-d H:i:s"),
			"trace"=>$trace
		);
		
		echo json_encode($payload, JSON_PRETTY_PRINT);
		
//		debug($payload);
		
		
		$timer->_stop("errors");
		
		return TRUE;
		
	}
	
	function _404() {
		$timer = new timer();
		
		$this->f3->OUTPUT['STATUS'] = 404;
		$this->f3->OUTPUT['ERRORS'] = array(
			"The page you seek seems to have wandered off...",
			"The page catches have been dispatched, but generally they seem to have a rather bad track record of finding missing pages. We keep them around because of Quota restrictions",
		);
		$this->f3->OUTPUT['RESPONSE'] = array(
			"type" => "error",
		);
		
		if ( $this->f3->ajax() ) {
			output::json($this->f3->OUTPUT);
		} else {
			$tmpl = new \template("template.twig");
			$tmpl->page = array(
				"title" => $this->f3->OUTPUT['STATUS'],
			);
			$tmpl->data = $this->f3->OUTPUT;
			
			echo $tmpl->renderPage($this,__FUNCTION__);
			
			//echo $tmpl->render_template($this,__FUNCTION__);
			
		}
		
		
		$timer->_stop("errors");
		//echo \views\renderer::getInstance($this->f3->OUTPUT)->output($this->f3->get("PARAMS['FORMAT']"));
		exit();
		
	}
	
	
}

