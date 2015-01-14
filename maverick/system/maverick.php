<?php
function __autoload($class)
{
	$maverick = maverick::getInstance();
	
	foreach($maverick->get_config('config.paths') as $path)
	{
		if(file_exists("/$path/$class.php"))
			require_once "/$path/$class.php";
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

	public function __get($name)
	{
		if(in_array($name, array('requested_route_string', 'matched_route', 'controller', 'error_routes') ) )
			return $this->$name;
		else
			return null;
	}
	
	public function __set($name, $value)
	{
		if(in_array($name, array('matched_route', 'controller', 'error_routes') ) )
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
		$this->get_request_uri();
		
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
		if(!preg_match('/^[a-z\d_-]+\.[a-z\d_-]+$/i', $item))
			return '';
		
		list($config, $param) = explode('.', $item);
		
		$c = ($this->config->$config);
		return isset($c[$param])?$c[$param]:'';
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
		// look at routes to find out which route the requested path best matches
		require_once (MAVERICK_BASEDIR . 'routes.php');
		
		if(!empty($this->controller))
		{
			$controller_name = $this->controller['controller_name'];
			
			if(class_exists($controller_name))
			{
				$this->controller['controller'] = new $controller_name;

				if(get_parent_class($this->controller['controller']) != 'base_controller')
				{
					// for some reason the controller referenced in the route isn't actually a controller
					unset($this->controller['controller']);
					//TODO: throw a server error
					error::show('controller specified in route is wrong type of class');
				}
				else
				{
					if(method_exists($this->controller['controller'], $this->controller['method']))
						$this->controller['controller']->{$this->controller['method']}();
					else
					{
						//TODO: throw missing method error
						error::show('controller method does not exist');
					}
				}
			}
			else
			{
				//TODO: throw an error as class specified in route doesn't exist
				error::show('controller specified in route does not exist');
			}
		}
		else
		{
			// throw a 404 - either look to see if an explicit route accounts for this, or throw some kind of default one
		}
	}
	
	public function set_error_route($code, $details)
	{
		$this->error_routes[$code] = $details;
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
	
	
}