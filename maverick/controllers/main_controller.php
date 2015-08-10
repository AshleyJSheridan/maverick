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
				->parse_handler('form', 'main_controller->parse_form_render')
				->parse_handler('template', 'main_controller->parse_template_render');
			
			$view = $this->add_variables($view, $page);
			
			$view->render();
			
			
			
		}
	}

	function error()
	{
		echo 'error';
	}

	/**
	 * this adds in to the view the main variables using the ->with() chainable method so that they can be used more easily in templates
	 * @param \maverick\view $view the view object
	 * @param array $page the returned array from the db query which matched for this page
	 */
	private function add_variables($view, $page)
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
		
		// add in the passed in $page bits
		$view->with('page', $page);
		
		return $view;
	}
	
	
	
	static function parse_form_render($matches = array() )
	{
		$form_id = intval($matches[1]);
		$language_culture = (!empty($matches[2]))?$matches[2]:null;
		
		if(!$form_id)
			return false;
		
		$form = cms::get_form_by_id($form_id, $language_culture);
		
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
				'values'=>'values',
				'placeholder'=>'placeholder',
				'label'=>'label',
			);
			
			foreach($attributes as $attribute => $mapped_to)
			{
				if(isset($element[$mapped_to]) && !empty($element[$mapped_to]) )
					$elements->{$element['element_name']}->{$attribute} = $element[$mapped_to];
			}
			
			// validation bits - handled a little bit messily
			$validation_rules = array();
			
			// required
			if(!empty($element['required']))
				$validation_rules[] = 'required';
			
			// min and max
			switch($element['type'])
			{
				case 'date':
				case 'number':
				case 'range':
				case 'time':
					if(!empty($element['min']))
						$validation_rules[] = "min:{$element['min'][0]}";
					if(!empty($element['max']))
						$validation_rules[] = "max:{$element['max'][0]}";
					if(!empty($element['between']))
						$validation_rules[] = "between:{$element['between'][0]}";
					
					break;
				case 'email':
				case 'file':
				case 'password':
				case 'tel':
				case 'textarea':
				case 'text':
				case 'url':
					if(!empty($element['min']))
						$validation_rules[] = "minlength:{$element['min'][0]}";
					if(!empty($element['max']))
						$validation_rules[] = "maxlength:{$element['max'][0]}";
					if(!empty($element['between']))
					{
						list($min, $max) = explode(':', $element['between'][0]);
						$validation_rules[] = "minlength:$min";
						$validation_rules[] = "maxlength:$max";
					}
					break;
			}
			
			// regex
			if(!empty($element['regex']))
				$validation_rules[] = "regex:{$element['regex'][0]}";

			// now add in the valdation rules to the element if the rule list is not empty
			if(!empty($validation_rules))
				$elements->{$element['element_name']}->validation = $validation_rules;
		}
		
		if(empty($form))
			return '';
			
		$form = new \helpers\html\form($form[0]['form_name'], json_encode($elements));
		
		return $form->render();
	}
	
	static function parse_template_render($matches)
	{
		var_dump($matches);
		
		if(!file_exists(MAVERICK_VIEWSDIR . "{$matches[1]}.php"))
			return '';
		
		$view = view::make(MAVERICK_VIEWSDIR . "{$matches[1]}.php");
	}
}