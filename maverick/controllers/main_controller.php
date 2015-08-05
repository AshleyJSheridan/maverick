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
		
		//var_dump(\json_decode('{"element":{"type":"select","label":"Title","class":"form_title","id":"form_title","values":["Mr","Mrs","Miss","Other"],"validation":["required"]}}') );

		$elements = new \stdClass();
		foreach($form as $element)
		{
			$elements->{$element['element_name']} = new \stdClass();
			
			$attributes = array(
				'type'=>'type',
				'class'=>'class',
				'id'=>'html_id',
				'label'=>'label',
				'value'=>'value',
				'placeholder'=>'placeholder',
			);
			
			foreach($attributes as $attribute => $mapped_to)
			{
				// TODO: check for elements that have a list of values and assign them to the values array in the object correctly
				if(isset($element[$mapped_to]) && strlen($element[$mapped_to]) )
					$elements->{$element['element_name']}->{$attribute} = $element[$mapped_to];
			}

			// validation bits
			
			
			var_dump($element);
		}
		
		var_dump($elements);
		
		return 'form';
	}
}