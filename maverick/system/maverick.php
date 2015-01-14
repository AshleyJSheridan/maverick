<?php
class maverick
{
	static $_instance;
	private $requested_route;
	private $config;
	
	private function __construct()
	{
		$this->load_config();
		$this->get_request_uri();
		
		var_dump($this);
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
	}
	
	
}