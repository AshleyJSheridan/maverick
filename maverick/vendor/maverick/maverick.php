<?php
namespace maverick;

/**
 * the main framework class that everything else is built around
 * @package Maverick
 * @author Ashley Sheridan <ash@ashleysheridan.co.uk>
 */
class maverick
{
	public static $_instance;
	private $config;
	private $requested_route;
	private $requested_route_string;
	private $controller;
	private $error_routes = array();
	private $language_culture = '';
	public $validator;
	public $db;
	public $view;
	public $view_data = array();

	/**
	 * magic getter for specific object values - returns null if the specified value is not in the array
	 * @param string $name the name of the member variable to retreive
	 * @return mixed
	 */
	public function __get($name)
	{
		if(in_array($name, array('requested_route_string', 'matched_route', 'controller', 'error_routes', 'language_culture') ) )
			return $this->$name;
		else
			return null;
	}
	
	/**
	 * magic setter for the main class - returns false for any member variable not in the array of allowed values to set
	 * @param string $name  the member variable to set
	 * @param mixed  $value the value to set it to
	 * @return boolean
	 */
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

	/**
	 * the main constructor - this just sets up the basic config
	 */
	private function __construct()
	{
		$this->load_config();
	}
	
	/**
	 * magic clone method - isn't currently used
	 * @return bool
	 */
	private function __clone()
	{
		
	}
	
	/**
	 * return the single instance of this class - there can be only one!
	 * @return \maverick\maverick
	 */
	public static function getInstance()
	{
		if(!(self::$_instance instanceof self))
			self::$_instance = new self;
		return self::$_instance;
	}
	
	/**
	 * retrieve a specific config option using dot syntax
	 * this method allows for deeply nested items to be fetched from the returned arrays within the config files themselves
	 * @param string $item the specific item to fetch
	 * @return mixed
	 */
	public function get_config($item)
	{
		$config = ''; // set a default return value of empty string - probably the safest option
		
		if(preg_match('/^([a-z\d_-]+)(\.([a-z\d_-]+))*$/i', $item))
		{
			$matches = explode('.', $item);

			if(count($matches)==1)
				$config = $this->config->{$matches[0]};
			else
			{
				// a check to see if we're loading from an non-existant config file (which would mean the member variable doesn't exist)
				if(isset($this->config->{$matches[0]}) )
				{
					$c = ($this->config->{$matches[0]});
					//$p = $config = '';
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
			}//end if
		}//end if

		return $config;
	}
	
	/**
	 * filter a string using PHP's built-in sanitisation filter
	 * @param string $data the string to sanitise
	 * @return string
	 */
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
	
	/**
	 * the main workhorse of the maverick class - this performs tasks like fetching views from cache to bypass the rest of the framework,
	 * handles route pre-parsing if that's set up, sets up any initial database connections if requested in the config and calls the 
	 * first controller matched by the requested route
	 * @return bool
	 */
	public function build()
	{
		// load from the cache if that is on and this is a GET request
		if($this->get_config('cache.on') !== false && isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'GET')
		{
			$hash = $this->get_request_route_hash();
			
			$headers = \maverick\cache::fetch("{$hash}_headers");
			$view = \maverick\cache::fetch($hash);
			
			if($headers)
			{
				$headers = json_decode($headers);
				
				foreach($headers as $header)
					header($header);
			}
			
			if($view)
				die($view);
		}
		
		/*if(strlen($this->get_config('config.route_preparser') ) )
			$this->route_preparser();*/
		
		$this->get_request_uri();
		$this->db = new \stdClass();
		
		if($this->get_config('lang.active') === true)
			$this->set_lang_culture();
		
		// look at routes to find out which route the requested path best matches
		include_once MAVERICK_BASEDIR . 'routes.php';

		// initialise base db object if the config exists, the engine is not an empty string, and the required parameters exist
		if(strlen($this->get_config('db.engine') ) && $this->check_required_fields($this->get_config('db'), array('engine','host','database','username','password') ) )
		{
			$dbname = $this->get_config('db.database');
			$dbhost = $this->get_config('db.host');
			$dbuser = $this->get_config('db.username');
			$dbpass = $this->get_config('db.password');
			
			$this->db->pdo = $pdo = new \PDO("mysql:dbname=$dbname;host=$dbhost", $dbuser, $dbpass, array(\PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8'"));;
		}

		// locate and initiate call to controller responsible for the requested route
		if(!empty($this->controller))
		{
			if($this->check_class_method_exists($this->controller['controller_name'], $this->controller['method'], $this->controller['controller']))
				$this->controller['controller']->{$this->controller['method']}($this->controller['args']);
			else
				\error::show("controller '{$this->controller['controller_name']}' or method '{$this->controller['method']}' not found");
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
				\error::show("404", 404);
		}
	}
	
	/**
	 * sets the view on the main class
	 * @param \maverick\view $view the view being set
	 * @return bool
	 */
	public function set_view(&$view)
	{
		$this->view = $view;
	}

	/**
	 * set the error route to use for a specific type of error
	 * @param int   $code    the HTTP status code
	 * @param array $details an array of details for the route
	 * @return bool
	 */
	public function set_error_route($code, $details)
	{
		if($this->check_required_fields($details, array('args', 'protocol', 'method', 'controller_name') ) )
			$this->error_routes[$code] = $details;
	}
	
	/**
	 * handle a route pre-parser method if it's been specified in the config
	 * @param string $preparser a string representing a class and method call to be used as a route preparser
	 * @return boolean
	 */
	private function route_preparser($preparser)
	{
		//$preparser = $this->get_config('config.route_preparser');

		// just check that the controller->method string is in the right format
		if(!preg_match('/^\p{L}[\p{L}\p{N}_]+\-\>\p{L}[\p{L}\p{N}_]+$/', $preparser) )
			return false;
		
		list($controller_name, $method) = explode('->', $preparser);

		// deal with the extra controller/method that preparses the route - ideally this will be a separate controller without a construct magic method
		if(class_exists($controller_name) && ($class_holder = new $controller_name) && method_exists($class_holder, $method) )
			$class_holder->$method();
	}

	/**
	 * set the language culture if the corresponding settings exist in the config
	 * this uses the standard I18N language functions
	 * @return bool
	 */
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

	/**
	 * fetch the error route details as an array for a specific error code
	 * @param int $code the HTTP status code of the error
	 * @return array
	 */
	private function get_error_route($code)
	{
		return isset($this->error_routes[$code])?array('controller_name'=>$this->error_routes[$code]['controller_name'], 'method'=>$this->error_routes[$code]['method']):array('controller_name'=>'', 'method'=>'');
	}
	
	/**
	 * load in the config files that exist in the config directory
	 * each config file will contain a returned array
	 * this generates an array of \stdClass objects, one for each config file
	 * @return bool
	 */
	private function load_config()
	{
		$this->config = new \stdClass();
		
		foreach(glob(MAVERICK_BASEDIR . 'config/*.php') as $config_file)
		{
			$config_name = str_replace('.php', '', basename($config_file) );
			
			$this->config->$config_name = include $config_file;
			
			// this seems bad to me, but it will allow multiple route preparsers to be added in any config file and run immediately
			if($this->get_config("$config_name.route_preparser") != '' )
				$this->route_preparser($this->get_config("$config_name.route_preparser"));
		}
	}
	
	/**
	 * get a hash for the requested route - used to create a unique identifier for the requested route to be used in caching methods
	 * the reason to include the $_GET array details is so that / and /?abc=xyz return different hashes
	 * @return string
	 */
	public function get_request_route_hash()
	{
		// this generates a hash from a combination of the $_GET values and the requested URL
		return md5(implode($_GET) . (isset($_SERVER['REDIRECT_URL'])?$_SERVER['REDIRECT_URL']:$_SERVER['REQUEST_URI'] ) );
	}

	/**
	 * get the requested URI and set the corresponding parts of it to the correct member variables of this class
	 * @return bool
	 */
	private function get_request_uri()
	{
		$this->requested_route = new \stdClass();
		
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
	
	/**
	 * determines if a method exists in a specified class
	 * note that this creates an instance of the class, so be wary of certain actions in class constructor 
	 * methods that you don't actually want to execute
	 * @param string          $class        the name of the class
	 * @param string          $method       the name of the method
	 * @param \maverick\class $class_holder the object used to hold the instantiated instance of this class
	 * @return type
	 */
	private function check_class_method_exists($class, $method, &$class_holder)
	{
		return (class_exists($class) && ($class_holder = new $class) && method_exists($class_holder, $method) );
	}
	
	/**
	 * check that required fields exist in a data set
	 * @todo consider breaking this out into a static helper class
	 * @param array $data   the dataset
	 * @param array $fields a list of fields to check exist
	 * @return bool
	 */
	private function check_required_fields($data, $fields)
	{
		$data_keys = array_keys($data);

		return !count(array_diff($fields, $data_keys) );
	}
}
