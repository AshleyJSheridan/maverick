<?php
class view
{
	static $_instance;
	private $view = '';
	private $data = array();
	
	private function __construct() {}
	
	private function __clone() {}
	
	public static function getInstance()
	{
		if(!(self::$_instance instanceof self))
			self::$_instance = new self;

		return self::$_instance;
	}

	// all queries should start with a table request
	public static function make($view)
	{
		$v = view::getInstance(true);
		$app = maverick::getInstance();
		
		$v->reset();
		
		if(!strlen($view))
			return $v;	// TODO: consider throwing an error here
		
		$v->set_view($view);
		$app->set_view($v);
		
		return $v;
	}
	
	public static function with($name, $data)
	{
		$v = view::getInstance(true);
		
		if(!strlen($name) || empty($data))
			return false;	// TODO: consider throwing an error here
		
		$v->data[$name] = $data;
		
		return $v;
	}
	
	public static function render($echo=true)
	{
		$v = view::getInstance();
		$app = maverick::getInstance();
		
		$view_file_path = MAVERICK_BASEDIR . "views/$v->view.php";

		if(!file_exists($view_file_path))
			return false;
		else
		{
			ob_start();
			include $view_file_path;
			$view = ob_get_contents();
			ob_end_clean();

			if($echo)
				echo $view;
			else
				return $view;
		}
		
		return $v;
	}

	public function get_data($var)
	{
		$v = view::getInstance();
		
		return (isset($v->data[$var]))?$v->data[$var]:'';
	}
	
	private function reset()
	{
		// not everything needs to be reset, only those variables pertaining to an individual view
		$v = view::getInstance();
		
		foreach(array('view') as $var)
			$v->$var = '';
			
		foreach(array('data') as $var)
			$v->$var = array();
	}

	private function set_view($view)
	{
		$this->view = $view;
	}
}