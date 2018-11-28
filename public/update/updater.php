<?php


namespace update;
class updater {

	const

		H1=array(
			"web"=>"<h1>%s</h1>",
			"cli"=>"\n\n%s\n----------------------------------"
		),
		H2=array(
			"web"=>"<h2>%s</h2>",
			"cli"=>"\n\n%s\n----------------------------------"
		),
		H3=array(
			"web"=>"<h3>%s</h3>",
			"cli"=>"\n\n%s\n----------------------------------"
		),
		H4=array(
			"web"=>"<h4>%s</h4>",
			"cli"=>"\n\n%s\n----------------------------------"
		),
		DONE=array(
			"web"=>"<blockquote>%s</blockquote>",
			"cli"=>"\n\n%s\n\n"
		),
		LOG=array(
			"web"=>"<div><span style='color:#444;'>%s</span> <span>%s</span></div>",
			"cli"=>"\n%s: %s"
		),
		EXEC=array(
			"web"=>"<div><span style='color:#444;'>%s</span> <pre>%s</pre></div>",
			"cli"=>"\n%s: %s"
		),
		TXT=array(
			"web"=>"<div>%s</div>",
			"cli"=>"\n%s"
		);



	function __construct($cfg=false,$folder=false,$test=false) {
		$root_folder = dirname(__FILE__);
		$root_folder = $root_folder . DIRECTORY_SEPARATOR;

		$errorFolder = $root_folder . "logs";
		$errorFile = $errorFolder . DIRECTORY_SEPARATOR . "php-".date("Y-m") . ".log";
		ini_set("error_log", $errorFile);



		$dir = dirname( __FILE__ );


		$this->test = $test;

		foreach(glob($dir.DIRECTORY_SEPARATOR."_*.php") as $file){
			require_once($file);
		}


		$actors = array();
		foreach(glob($dir.DIRECTORY_SEPARATOR."actors".DIRECTORY_SEPARATOR."*.php") as $file){
			$pathinfo = pathinfo($file);
			require_once($file);
			$class = "\\update\\actors\\".$pathinfo['filename'];
			$actor = basename($file,".php");

			if (class_exists($class)) {
				$return = $class::_def();
				$return['file'] = $file;
				$return['actor'] = $actor;
				$actors[$actor] = $return;
			}
		}



		uasort($actors, function($a, $b) {
			return $a['order'] <=> $b['order'];
		});

		$this->actors = $actors;




		if ($cfg===false){


			
			$last_folder = null;
			$folder = dirname(__FILE__);
			while (is_dir($folder) && !file_exists($folder.DIRECTORY_SEPARATOR.'config.default.php') && $last_folder != $folder){
				$last_folder = $folder;
				$folder = dirname($folder);
			}
			$folder = $folder . DIRECTORY_SEPARATOR;
			
			
			$cfg = require('config.default.php');
			if ( file_exists(__DIR__ . DIRECTORY_SEPARATOR . 'config.php') ) {
				$cfg = $this->magic_array_merge($cfg, require('config.php'));
			}
			
			


		}
		$this->cfg = $cfg;
		if ($folder===false){
			$folder = "./";
		}


		$this->cfg_folder = $folder;




	}

	function run($actor=false){
		ob_start();
		header('X-Accel-Buffering: no');


		if ($actor===false){
			foreach ($this->actors as $ob){
				$ob['class']::getInstance($this->cfg,$this->cfg_folder,$this->test)->start();
			}
		} else {

			if (isset($this->actors[$actor])){
				$this->actors[$actor]['class']::getInstance($this->cfg,$this->cfg_folder,$this->test)->start();
			} else {
				echo "no actor like that exists here: " . $actor;
				exit();
			}

		}
	}


	function _output(){


		$args = array();
		foreach (func_get_args() as $item){
			$args[] = $item;
		};


		$template = $args[0];
		if (php_sapi_name() != 'cli'){
			$template = $template['web'];
		} else {
			$template = $template['cli'];
		}
		array_shift($args);

		$str = vsprintf($template,$args);



		echo $str;

		ob_flush();
		flush();
		return $str;
	}



	function _exec($cmd,$folder=false){

		if ($this->test){
			return $cmd;
		} else {
			$curfolder = getcwd();
			if ($folder){
				chdir($folder);
			}

			$return = shell_exec($cmd." 2>&1 &");

			if ($folder){
				chdir($curfolder);
			}

			return $return;
		}


	}
	function _sql($cmd,$link, $fn,$force=false){

		if ($this->test && !$force){
			return $cmd;
		} else {
			$result = mysqli_query($link,$cmd) or die(mysqli_error($link));

			$data = array();
			while($item = $result->fetch_assoc()){
				$data[] = $item;
			}


			//test(array($data,$cmd));

			return call_user_func_array($fn,array($data));
		}


	}
	function magic_array_merge($array1, $array2) {
		
		foreach ( $array1 as $key => $value ) {
			if ( isset($array2[$key]) ) {
				if ( is_numeric($key) ) {
					$array1[] = $array2[$key];
				} else if ( is_array($value) ) {
					$array1[$key] = self::magic_array_merge($value, $array2[$key]);
				} else {
					$array1[$key] = $array2[$key];
				}
				unset($array2[$key]);
			}
		}
		
		
		return $array1;
	}

}



