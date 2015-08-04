<?php
class main_controller extends base_controller
{
	function __construct()
	{
		if(!isset($_SESSION))
			session_start();
		
		$this->app = \maverick\maverick::getInstance();
	}
	
	function main($params)
	{
		$page = content::get_page($params);

		if(!$page || !file_exists(MAVERICK_VIEWSDIR . $page['template_path'] . '.php'))
		{
			// do 404 stuff here
		}
		else
		{
			$view = view::make($page['template_path'])
				->parse_handler('form', 'main_controller->parse_form_render');
			$view = $this->add_variables($view);
			
			$view->render();
			
			
			
		}
	}

	function error()
	{
		echo 'error';
	}

	/**
	 * this adds in to the view the main variables using the ->with() chainable method so that they can be used more easily in templates
	 * @param type $vars
	 */
	private function add_variables($view)
	{
		// add in the super globals
		$vars = array('server', 'get', 'post', 'files', 'cookie', 'session', 'request', 'env');
		foreach($vars as $var)
		{
			if(isset( $GLOBALS['_'.strtoupper($var)]) )
				$view->with($var, $GLOBALS['_'.strtoupper($var)]);
		}
		
		// add in the bits that are public on the main \maverick\maverick class instance
		$member_vars = array('requested_route_string', 'error_routes', 'language_culture');
		$maverick_vars = array();
		foreach($member_vars as $var)
			$maverick_vars[$var] = $this->app->{$var};
		$view->with('maverick', $maverick_vars);
		
		return $view;
	}
	
	
	
	static function parse_form_render($matches = array() )
	{
		$form_name = $matches[1];
		
		$form = cms::get_form_by_name($form_name);
		
		return 'form';
	}
}