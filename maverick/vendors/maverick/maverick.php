<?php
function __autoload($class)
{
	// a more traditional autoloader - kept in until everything can be transitioned to PSR-0 standard
	$maverick = maverick::getInstance();
	$class_found = false;
	
	foreach($maverick->get_config('config.paths') as $path)
	{
		if(file_exists("$path/$class.php"))
		{
			require_once "$path/$class.php";
			$class_found = true;
			break;
		}
	}
	
	// add the maverick directory in as the namespace if it doesn't exist
	if(strpos($class, DIRECTORY_SEPARATOR) === false)
		$class = 'maverick' . DIRECTORY_SEPARATOR . $class;
	
	// PSR-0 autoloader - sourced from http://www.sitepoint.com/autoloading-and-the-psr-0-standard/
	if(!$class_found)
	{
		$className = ltrim($class, '\\');
		$fileName  = '';
		$namespace = '';
		if ($lastNsPos = strripos($className, '\\'))
		{
			$namespace = substr($className, 0, $lastNsPos);
			$className = substr($className, $lastNsPos + 1);
			$fileName  = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
		}
		$fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';

		set_include_path(MAVERICK_BASEDIR . 'vendors');

		require $fileName;
	}
}

class maverick
{
	static $_instance;
	private $config;
	private $requested_route;
	private $requested_route_string;
	private $controller;
	private $error_routes = array();
	public $validator;
	public $db;
	public $view;
	private $language_culture = '';
	

	public function __get($name)
	{
		if(in_array($name, array('requested_route_string', 'matched_route', 'controller', 'error_routes', 'language_culture') ) )
			return $this->$name;
		else
			return null;
	}
	
	public function __set($name, $value)
	{
		if(in_array($name, array('matched_route', 'controller', 'error_routes', 'language_culture') ) )
		{
			$this->$name = $value;
			return true;
		}
		else
			return false;
	}

	private function __construct()
	{
		$this->load_config();
	}
	
	private function __clone() {}
	
	public static function getInstance()
	{
		if(!(self::$_instance instanceof self))
			self::$_instance = new self;
		return self::$_instance;
	}
	
	public function get_config($item)
	{
		$config = ''; // set a default return value of empty string - probably the safest option
		
		if(preg_match('/^([a-z\d_-]+)(\.([a-z\d_-]+))*$/i', $item))
		{
			$matches = explode('.', $item);
			
			switch(count($matches))
			{
				case 1:
					$config = $this->config->{$matches[0]};
					break;
				default:
					// a check to see if we're loading from an non-existant config file (which would mean the member variable doesn't exist)
					if(isset($this->config->{$matches[0]}) )
					{
						$c = ($this->config->{$matches[0]});
						$p = $config = '';
						array_shift($matches);

						foreach($matches as $item)
						{
							if(isset($c[$item]))
							{
								$config = $c[$item];
								$c = $c[$item];
							}
							else
								break;

						}
					}
					
					break;
			}

		}

		return $config;
	}
	
	public function xss_filter($data)
	{
		foreach($data as $key => &$datum)
		{
			if(is_array($datum))
				$datum = $this->xss_filter($datum);
			else
				$datum = filter_var($datum, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
		}
		return $data;
	}
	
	public function build()
	{
		if(strlen($this->get_config('config.route_preparser') ) )
			$this->route_preparser();
		
		$this->get_request_uri();
		$this->db = new stdClass();
		
		if($this->get_config('lang.active') === true)
			$this->set_lang_culture();
		
		// look at routes to find out which route the requested path best matches
		require_once (MAVERICK_BASEDIR . 'routes.php');

		// initialise base db object if the config exists, the engine is not an empty string, and the required parameters exist
		if(strlen($this->get_config('db.engine') ) && $this->check_required_fields($this->get_config('db'), array('engine','host','database','username','password') ) )
		{
			$dbname = $this->get_config('db.database');
			$dbhost = $this->get_config('db.host');
			$dbuser = $this->get_config('db.username');
			$dbpass = $this->get_config('db.password');
			
			$this->db->pdo = $pdo = new PDO("mysql:dbname=$dbname;host=$dbhost",$dbuser,$dbpass, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8'"));;
		}

		// locate and initiate call to controller responsible for the requested route
		if(!empty($this->controller))
		{
			if($this->check_class_method_exists($this->controller['controller_name'], $this->controller['method'], $this->controller['controller']))
				$this->controller['controller']->{$this->controller['method']}($this->controller['args']);
			else
				error::show("controller '{$this->controller['controller_name']}' or method '{$this->controller['method']}' not found");
		}
		else
		{
			// throw a 404 - either look to see if an explicit route accounts for this, or throw some kind of default one
			$error_controller = $this->get_error_route(404);

			if($this->check_class_method_exists($error_controller['controller_name'], $error_controller['method'], $this->controller['controller']))
			{
				// add the error controller details in as the main controller details for consistency later on
				list($this->controller['controller_name'], $this->controller['method'], $this->controller['protocol'], $this->controller['args']) = array($error_controller['controller_name'], $error_controller['method'], 'any', null);
				$this->controller['controller']->{$this->controller['method']}();
			}
			else
				error::show("404", 404);
		}
	}
	
	public function set_view(&$view)
	{
		$this->view = $view;
	}

	public function set_error_route($code, $details)
	{
		if($this->check_required_fields($details, array('args', 'protocol', 'method', 'controller_name') ) )
			$this->error_routes[$code] = $details;
	}
	
	private function route_preparser()
	{
		$preparser = $this->get_config('config.route_preparser');

		// just check that the controller->method string is in the right format
		// TODO - consider making a better method of this, as the routing code could benefit from this also
		if(!strpos($preparser, '->'))
			return false;
		
		list($controller_name, $method) = explode('->', $preparser);

		// deal with the extra controller/method that preparses the route - ideally this will be a separate controller without a construct magic method
		if(class_exists($controller_name) && ($class_holder = new $controller_name) && method_exists($class_holder, $method) )
			$class_holder->$method();
	}
	
	private function set_lang_culture()
	{
		// language specific stuff - this sets the language the app will use and sets up ini and location of translation bits
		if(strlen($this->get_config('lang.default')) && !strlen($this->language_culture) )
			$this->language_culture = str_replace('-', '_', $this->get_config('lang.default') );
		
		// I18N support information here
		putenv("LANG=$this->language_culture.utf8");
		setlocale(LC_ALL, "$this->language_culture.utf8");
		
		// Set the text domain as the app_name in config
		$domain = $this->get_config('config.app_name');
		bindtextdomain($domain, MAVERICK_BASEDIR . 'locale');
		bind_textdomain_codeset($domain, 'UTF-8');
		textdomain($domain);
	}

	private function get_error_route($code)
	{
		return isset($this->error_routes[$code])?array('controller_name'=>$this->error_routes[$code]['controller_name'], 'method'=>$this->error_routes[$code]['method']):array('controller_name'=>'', 'method'=>'');
	}
	
	private function load_config()
	{
		$this->config = new stdClass();
		
		//TODO: add functionality to allow sub-directories to override configs per environment
		foreach(glob(MAVERICK_BASEDIR . 'config/*.php') as $config_file)
		{
			$config_name = str_replace('.php', '', basename($config_file) );
			
			$this->config->$config_name = include $config_file;
		}
	}
	
	private function get_request_uri()
	{
		$this->requested_route = new stdClass();
		
		foreach(array('get', 'post', 'cookie') as $request)
		{
			$global = '_'.strtoupper($request);

			$this->requested_route->$request = ($this->get_config('config.xss_protection'))?$this->xss_filter($GLOBALS[$global]):$GLOBALS[$global];
		}
		
		if(isset($_SERVER['REDIRECT_URL']))
			$path = $_SERVER['REDIRECT_URL'];
		else
			$path = (strpos($_SERVER['REQUEST_URI'], '?') !== false)?substr($_SERVER['REQUEST_URI'], 0, strpos($_SERVER['REQUEST_URI'], '?')):$_SERVER['REQUEST_URI'];
		
		$path = explode('/', trim($path, '/') );
		
		$this->requested_route->path = ($this->get_config('config.xss_protection'))?$this->xss_filter($path):$path;
		$this->requested_route_string = implode('/', $this->requested_route->path);
	}
	
	private function check_class_method_exists($class, $method, &$class_holder)
	{
		return (class_exists($class) && ($class_holder = new $class) && method_exists($class_holder, $method) );
	}
	
	//TODO: consider breaking this out into a static helper class
	private function check_required_fields($data, $fields)
	{
		$data_keys = array_keys($data);

		return !count( array_diff($fields, $data_keys) );
	}
}