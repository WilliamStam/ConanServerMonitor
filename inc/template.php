<?php


class template {
	private $config = array(), $vars = array(), $cfg = array();
	
	function __construct($template, $folder = "./public/") {
		$this->f3 = Base::instance();
		$this->cfg = $this->f3->get("CFG");
		
		$cacheDir = FALSE;
		if ( $this->f3->get('CACHE') ) {
			$cacheDir = $this->f3->get('TEMP');
		}
		
		$this->config['debug'] = $this->cfg['DEBUG'] ? TRUE : FALSE;
		$this->config['cache'] = $cacheDir;
		$this->vars['folder'] = array(
			$folder,
			'./views/',
		);
		
		$this->template = $template;
		
		$this->timer = new \timer();
		
		
	}
	
	function __destruct() {
		$page = $this->template;
		//test_array($page);
		
		$this->timer->stop("Template: " . $page);
	}
	
	public function __get($name) {
		return $this->vars[$name];
	}
	
	public function __set($name, $value) {
		$this->vars[$name] = $value;
	}
	
	private function default_vars() {
		
		/* INFO: use these in any template being generated. {{ _VERSION }} etc */
		
		
		/* INFO: any default variables you want to pass to "all" templates. optional*/
		$this->vars['_USER'] = $this->f3->get('USER');
		
		
		/* INFO: we need version here for some templates cachebusting. like calling the public assets - not optional*/
		$this->vars['VERSION'] = $this->f3->get('VERSION');
		$this->vars['BUILD'] = $this->f3->get('BUILD');
	}
	
	
	public function render_template() {
		$this->default_vars();
		
		//debug($this->vars);
		
		
		$folder = $this->vars['folder'];
		$twig = $this->twigify($folder);
		
		
		return $twig->render($this->template, $this->vars);
	}
	
	private function twigify($folder, $options = array()) {
		
		if ( !is_array($folder) ) {
			$folder = array(
				$folder,
			);
		}
		
		
		$folders = array();
		foreach ( $folder as $f ) {
			$f = $this->f3->fixslashes($f);
			$folders[] = $f;
		}
		
		
		//debug($folders,$this->config);
		$loader = new Twig_Loader_Filesystem($folders, dirname(__DIR__));
		
		//debug($folder,dirname(__DIR__));
		
		$options = $options + $this->config;
		
		
		//		$options['debug'] = $this->cfg['DEBUG'];
		//		$options['autoescape'] = FALSE;
		//		$options['cache'] = $cacheDir;
		
		
		//		debug($options);
		
		$twig = new Twig_Environment($loader, $options);
		$twig->addExtension(new Twig_Extension_Debug());
		
		
		/* INFO: define some usefull functions for twig */
		$twig->addFilter(new Twig_SimpleFilter('toAscii', function($string) {
			$string = strings::toAscii($string);
			
			return ($string);
		}));
		
		$twig->addTest(new Twig_Test('is_numeric', function($str) {
			return is_numeric($str);
		}));
		
		
		return $twig;
	}
	
	
	public function render_string($folder = "", $twig_options = array()) {
		$this->default_vars();
		
		if ( $this->vars['template'] ) {
			$twig = $this->twigify($folder, $twig_options);
			
			$template = $twig->createTemplate($this->vars['template']);
			$return = $template->render($this->vars);
			
			return $return;
		}
		
	}
	
	
	public function renderPage($obj = "", $method = "") {
		//debug($this->config);
		/* INFO: check the object to get the class / namespace */
		if ( is_object($obj) ) {
			$class = get_class($obj);
			$reflection = (new \ReflectionClass($obj));
			$classname = $reflection->getShortName();
			$namespace = $reflection->getNamespaceName();
			
			
			if ( isset($this->vars['page']) && $this->vars['page'] ) {
				
				/* INFO: added so that you can do a if page.class == "blah" then active */
				$this->vars['page']["_"]['NAMESPACE'] = $namespace;
				$this->vars['page']["_"]['CLASS'] = $classname;
				$this->vars['page']["_"]['METHOD'] = $method;
				
				
				$path = dirname(__DIR__) . DIRECTORY_SEPARATOR;
				$assets_path = "/" . ($this->f3->fixslashes(str_replace("\\controllers\\", "\\ui\\{section}\\", $namespace . "\\")));
				
				$parts = explode("/", $assets_path);
				
				$assets_fake_prefix = "/assets/" . $this->f3->get("VERSION") . "/{$parts[2]}/";
				$assets_fake_prefix = rtrim($assets_fake_prefix, "/");
				
				$this->vars['page']['assets'] = $assets_fake_prefix;
				
				if ( $assets_path == "//" ) {
					$assets_path = "/views/";
				}
				
				/* INFO: generate the assets items function*/
				
				$fixPath = function($section, $filename) use ($path, $assets_path, $assets_fake_prefix) {
					$asset = str_replace("{section}", $section, $assets_path . $filename);
//					debug($assets_path);
					
					/* INFO: not the best way to do it im sure but this just removes the first /app/web/ui for the relative path */
					$asset_ = explode("/", $asset);
					unset($asset_[0]);
					unset($asset_[1]);
					unset($asset_[2]);
					unset($asset_[3]);
					// This line will re-set the indexes (the above just nullifies the values...) and make a     new array without the original first two slots.
					$asset_ = implode("/", ($asset_));
					
					
					//					debug($asset);
					$relative = $asset_;
					
					$real = str_replace(array(
						"/",
						DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR,
					), DIRECTORY_SEPARATOR, $path . $asset);
//					debug($real);
					
					/* INFO: return false if the file doesnt exist */
					$return = file_exists($real) ? array(
						"real" => $real,
						"absolute" => $asset,
						"relative" => $relative,
						"filename" => $filename,
						"asset" => $assets_fake_prefix . "/" . $relative,
					) : FALSE;
					
					
					return $return;
				};
				
				
				/* INFO: setting up some variables for the template to be aware of regarding css etc */
				
				
				$this->vars['page']['template']['template'] = $fixPath("templates", "template.twig");
				$this->vars['page']['template']['twig'] = $fixPath("templates", $classname . ".twig");
				$this->vars['page']['template']['css'] = $fixPath("css", $classname . ".css");
				$this->vars['page']['template']['js'] = $fixPath("js", $classname . ".js");
				
				
				/* INFO: by default the template looks for files in the ./public folder. we need to direct it to the app's template folder as a fallback if it doesnt find it in public */
				if ( $this->vars['page']['template']['twig'] ) {
					$this->vars['folder'][] = "." . dirname($this->vars['page']['template']['twig']['absolute']);
				}
				if ( $this->vars['page']['template']['template'] ) {
					$this->vars['folder'][] = "." . dirname($this->vars['page']['template']['template']['absolute']);
				}
				
				
//				debug($this->vars);
			}
			
			
		}
		
		
		$return = $this->render_template();
		
		return \output::string($return);
		
	}
	
}
