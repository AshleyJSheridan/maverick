<?php
use \maverick\data as data;

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

	public static function make($view)
	{
		$v = view::getInstance();
		$app = \maverick\maverick::getInstance();
		
		$v->reset();
		
		if(!strlen($view))
			error::show('View not specified');
		
		$v->set_view($view);
		$app->set_view($v);
		
		return $v;
	}
	
	public static function with($name, $data)
	{
		$v = view::getInstance();
		
		if(!strlen($name) || empty($data))
			return $v;	// probably nicest to just return the view without modification so that it doesn't break the chain
		
		$v->data[$name] = $data;
		
		return $v;
	}
	
	public static function render($echo=true)
	{
		$v = view::getInstance();
		$app = \maverick\maverick::getInstance();
		
		$view_file_path = MAVERICK_VIEWSDIR . "/$v->view.php";

		if(!file_exists($view_file_path))
			error::show("View '$v->view' does not exist");
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