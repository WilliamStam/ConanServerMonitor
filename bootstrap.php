<?php
/* INFO: namespace the basics */

namespace {
	$mtime = microtime();
	$mtime = explode(' ', $mtime);
	$GLOBALS['page_timer_start'] = $mtime[1] + $mtime[0];
	$GLOBALS["EVENTS_TRIGGERED"] = array();
	
	
	require('inc/functions.php');
	require('inc/strings.php');
	require('inc/arrays.php');
	
	/* INFO: getting the config files. uses the config.default.php as the "base" and replaces its values from config.php */
	$_CFG = require('config.default.php');
	if ( file_exists(__DIR__ . DIRECTORY_SEPARATOR . 'config.php') ) {
		$_CFG = arrays::magic_array_merge($_CFG, require('config.php'));
	}
	
	$GLOBALS['CFG'] = $_CFG;
	function getAppBuild() {
		global $_CFG;
		/* INFO: generate a build for the system from the git ref. if it doesnt exist then use "today" */
		$build = date('YmdH');
		$buildFile = __DIR__ . DIRECTORY_SEPARATOR . '.git/refs/heads/' . $GLOBALS['CFG']['GIT']['BRANCH'];
		$buildFile = realpath(str_replace(array("/"), DIRECTORY_SEPARATOR, $buildFile));
		//	exit($buildFile);
		if ( file_exists($buildFile) ) {
			$build = trim(file_get_contents($buildFile));
		}
		
		return $build;
	}
	
	$GLOBALS['BUILD'] = getAppBuild();
	
	
	date_default_timezone_set($_CFG['TIMEZONE']);
	setlocale(LC_ALL, $_CFG['LOCALE']);
	
	
	/* INFO: Setting the error file to be by default /storage/logs/php/<year>-<month>--<build>.log */
	$errorFile = ($_CFG['LOGS']['FOLDER'] . $_CFG['LOGS']['PHP'] . DIRECTORY_SEPARATOR . date('Y-m--') . $GLOBALS['BUILD'] . '.log');
	/* INFO: create the error folder / file for php to use */
	if ( !file_exists(dirname($errorFile)) ) {
		@mkdir(dirname($errorFile), 01777, TRUE);
	}
	ini_set('error_log', $errorFile);
	
	
	/* INFO: check the caches build. if it doesnt match then delete the cache dir */
	$cacheVersion = FALSE;
	$cacheDirectory = $_CFG['TEMP'] . "cache";
	$cacheVersionFile = $cacheDirectory . DIRECTORY_SEPARATOR . "build.txt";
	
	/* INFO: return the caches build */
	if ( file_exists($cacheVersionFile) ) {
		$cacheVersion = file_get_contents($cacheVersionFile);
	}
	
	/* INFO: if the cache files build doesnt exist or isnt the same as the system build then delete the cache directory and make it again therby clearing out the cache on a build change*/
	if ( $cacheVersion != $GLOBALS['BUILD'] ) {
		if ( file_exists($cacheDirectory) ) {
			rmrf($cacheDirectory);
			/* INFO: since we deleted the cache folder we need to recreate it again */
			if ( !file_exists($cacheDirectory) ) {
				try {
					@mkdir(($cacheDirectory), 0755, TRUE);
				} catch ( Exception $e ) {
				
				}
				
			}
		}
		
		
		/* INFO: we need to add the build.txt file for the cache */
		if ( file_exists($cacheDirectory) ) {
			$fp = fopen($cacheVersionFile, "wb");
			fwrite($fp, $GLOBALS['BUILD']);
			fclose($fp);
		}
	}
	
	
	/* INFO: setting up the session stuff for this request */
	
	
	if ( session_id() == '' ) {
		$SID = @session_start();
	} else {
		$SID = session_id();
	}
	if ( !$SID ) {
		session_start();
		$SID = session_id();
	}
	
	/* INFO: if somone manages to spoof the session ID we check if they were clever enough to also include another session variable of initiated. if not then we generate a new session id. have fun hackers */
	if ( !isset($_SESSION['initiated']) ) {
		session_regenerate_id();
		$_SESSION['initiated'] = TRUE;
		$SID = session_id();
	}
	
	
	if ( !isset($_SESSION['initiated']) ) {
		session_regenerate_id();
		$_SESSION['initiated'] = TRUE;
	}
	
	
	require('inc/template.php');
	require('inc/timer.php');
	require('inc/errors.php');
	
	
	require_once('vendor/autoload.php');
	
	
	/* INFO: Setup the debugger side of the app */
	
	
	/*
	
	
	if ( $_CFG['DEBUG'] ) {
		$debugger = Debugger::DEVELOPMENT;
	} else {
		$debugger = Debugger::PRODUCTION;
	}
	
	*/
	$f3 = \Base::instance();
	/* INFO: Setting up F3 Variables */
	
	$f3->set('TEMP', $_CFG['TEMP'] . "cache" . DIRECTORY_SEPARATOR);
	$f3->set('DEBUG', $_CFG['DEBUG'] ? 4 : 0);
	$f3->set('CFG', $_CFG);
	$f3->set('PACKAGE', $_CFG['POWERED-BY']);
	$f3->set('BUILD', $GLOBALS['BUILD']);
	$f3->set('VERSION', "18-02.1");
	
	switch ( $_CFG['DATABASE']['DRIVER'] ) {
		case 'mysql':
			$f3->set('DB', new DB\SQL('mysql:host=' . $_CFG['DATABASE']['HOST'] . ';dbname=' . $_CFG['DATABASE']['DATABASE'], $_CFG['DATABASE']['USERNAME'], $_CFG['DATABASE']['PASSWORD'], array(\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION)));
			break;
	}
	
	$f3->set('OUTPUT', array());
	$f3->set('CONTEXT', array(
		"VERSION" => $f3->get('VERSION'),
		"BUILD" => $f3->get('BUILD'),
	));
	
	
	//	debug($consumerCount);
	
	
	$f3->set('AUTOLOAD', '../|../inc/|./|./inc/');
	$f3->set('PLUGINS', 'vendor/bcosca/fatfree/lib/');
	$f3->set('CACHE', $_CFG['CACHE']);
	
	
	$f3->set('UI', 'app/**/ui/');
	
	$f3->set('MEDIA', $_CFG['MEDIA']['FOLDER']);
	
	$f3->set('TAGS', $_CFG['TAGS']);
	$f3->set('ERROR_LOG', $errorFile);
	$f3->set('SID', $SID);
	
	$f3->set('STATUS', array(
		"200" => array(
			"label" => "Ok",
			"desc" => "Ok",
			"code" => "200",
			"badge" => "badge-success",
		),
		"201" => array(
			"label" => "Ok",
			"desc" => "New record created",
			"code" => "201",
			"badge" => "badge-success",
		),
		"400" => array(
			"label" => "Bad Request",
			"desc" => "Data sent to server isn't understood or isn't in the right format",
			"code" => "400",
			"badge" => "badge-warning",
		),
		"404" => array(
			"label" => "Not Found",
			"desc" => "Record can't be found",
			"code" => "404",
			"badge" => "badge-warning",
		),
		"405" => array(
			"label" => "Method Not Allowed",
			"desc" => "Not a valid endpoint controller",
			"code" => "405",
			"badge" => "badge-danger",
		),
		"500" => array(
			"label" => "Internal Server Error",
			"desc" => "System Error",
			"code" => "500",
			"badge" => "badge-danger",
		),
	));
	
	
	$f3->set('ONERROR', function() use ($f3) {
		$error = $f3->get('ERROR');
		(new \errors())->init($error);
		
	});
	
	
	//	$f3->error(500,"500 error");
	
	
	$whoops = new \Whoops\Run;
	if ( $f3->get("DEBUG") ) {
		
		$handler = new \Whoops\Handler\PrettyPageHandler();
		$handler->setEditor(function($file, $line) {
			return "editor:$file:$line";
		});
		$handler->addDataTableCallback('CONTEXT', function() {
			global $f3;
			
			return $f3->get("CONTEXT");
		});
		
		
	} else {
		$handler = new \Whoops\Handler\CallbackHandler(function($e, $i, $r) {
			(new errors)->production($e);
		});
	}
	$whoops->pushHandler($handler);
	$handler = new \Whoops\Handler\CallbackHandler(function($e, $i, $r) {
		(new errors)->log($e);
	});
	$whoops->pushHandler($handler);
	
	
	
	$whoops->register();
	
	
}

namespace bootstrap {
	class app {
		public function __construct() {
			$this->f3 = \Base::instance();
			
			
		}
		
		public function __destruct() {
		
		}
		
		
		
		function run() {
			
			
			/* INFO: all said and done, now to output the content. we use $this->f3->OUTPUT to keep all our data. this allows us to add timers and debugging info to the "stack". $this->f3->OUTPUT['RENDERED'] is the template output for a page (taking the data and throwing it at a twig template etc). */
			$this->f3->run();
			
			
			$mtime = microtime();
			$mtime = explode(' ', $mtime);
			$mtime = $mtime[1] + $mtime[0];
			
			$this->f3->OUTPUT['MEMORY'] = round(memory_get_peak_usage(TRUE) / 1024 / 1024, 2) . ' MB';
			$this->f3->OUTPUT['TIME'] = \timer::shortenTimer($mtime - $GLOBALS['page_timer_start']);
			
			$url = 'http' . (($_SERVER['SERVER_PORT'] == 443) ? 's://' : '://') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
			$this->f3->OUTPUT['DEBUG']['page'] = array(
				'uri' => $_SERVER['REQUEST_URI'],
				'url' => $url,
				'time' => $this->f3->OUTPUT['TIME'],
				'memory' => $this->f3->OUTPUT['MEMORY'],
			);
			
			
			if ( isset($_GET['raw']) ) {
				debug($this->f3->OUTPUT);
			}
			
			$thispagedebug = $this->f3->OUTPUT['DEBUG'] ?? (array());
			$this->f3->OUTPUT['DEBUG'] = array();
			$this->f3->OUTPUT['DEBUG'][] = $thispagedebug;
			//echo \app\views\renderer::getInstance($this->f3->OUTPUT)->output($this->f3->get('PARAMS['FORMAT']'));
			
			
			$this->render();
			
			
		}
		
		
		
		function render() {
			$data = $this->f3->OUTPUT;
			$order = array(
				'STATUS',
				'RESPONSE',
				'TIME',
				'MEMORY',
				'DEBUG',
			);
			
			
			uksort($data, function($key1, $key2) use ($order) {
				return (array_search($key1, $order) > array_search($key2, $order));
			});
			
			
			if ( isset($data['RENDERED']) ) {
				$timerStr = '';
				/* INFO: the rendered template should inclode a <!-- TIMERS --> block in it where the debug info needs to be added. */
				if ( isset($this->f3->OUTPUT['DEBUG']) ) {
					$timer = json_encode($this->f3->OUTPUT['DEBUG']);
					/* INFO: we take the debug info and throw it through a jqote template to display on the page */
					$timerStr = <<<Timer
<script>
$('#debugger').jqotesub($('#template-debugger'), $timer);
</script>
Timer;
				}
				
				echo str_replace('<!-- TIMERS -->', $timerStr, $data['RENDERED']);
			} else {
				/* INFO: the output is json here. output::json() simply sets it up that it will execute here */
				header('Content-Type: application/json');
				echo json_encode($data);
			}
			
			
		}
	}
}

