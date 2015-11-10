<?php
/**
 * the main controller that all the front-end routes point to
 * @package MaverickCMS
 * @author Ashley Sheridan <ash@ashleysheridan.co.uk>
 */
class main_controller extends base_controller
{
	/**
	 * main constructor - creates a session if one does not yet exist (although it shouldn't at this point in the code) and gets an instance of the main framework
	 */
	public function __construct()
	{
		if(!isset($_SESSION))
			session_start();
		
		$this->app = \maverick\maverick::getInstance();
	}
	
	/**
	 * the main method called by all front-end routes
	 * @param array $params the url parameters
	 * @return bool
	 */
	public function main($params)
	{
		$page = content::get_page($params);

		if(!$page || !file_exists(MAVERICK_VIEWSDIR . $page['template_path'] . '.php'))
		{
			// do 404 stuff here
			echo 'do 404 stuff here';
		}
		else
		{
			$view = new \maverick\mview($page['template_path']);
			$view = $this::add_variables($view, $page);
			
			$view->parse_handler('template', 'main_controller->parse_template_render');		// adds in other templates
			$view->parse_handler('form', 'main_controller->parse_form_render');				// adds rendered forms
			$view->parse_handler('each', 'main_controller->parse_list_render');				// loops over an array and parses a template for each item
			

			$view->render();
		}//end if
	}

	/**
	 * error method for 404s and stuff
	 * @todo implement this
	 * @return bool
	 */
	public function error()
	{
		echo 'error';
	}


	/**
	 * this adds in to the view the main variables using the ->with() chainable method so that they can be used more easily in templates
	 * @param \maverick\view $view the view object
	 * @param array          $page the returned array from the db query which matched for this page
	 * @return \maverick\view
	 */
	private static function add_variables($view, $page=null)
	{
		$app = \maverick\maverick::getInstance();

		// add in the super globals
		$vars = array('server', 'get', 'post', 'files', 'cookie', 'session', 'request', 'env');
		foreach($vars as $var)
		{
			if(isset($GLOBALS['_'.strtoupper($var)]) )
				$view->with($var, $GLOBALS['_'.strtoupper($var)]);
		}
		
		// add in the bits that are public on the main \maverick\maverick class instance
		$member_vars = array('requested_route_string', 'error_routes', 'language_culture');
		$maverick_vars = array();
		foreach($member_vars as $var)
			$maverick_vars[$var] = $app->{$var};
		$view->with('maverick', $maverick_vars);
		
		//var_dump($page);
		// add in the passed in $page bits
		if($page)
			$view->with('page', $page);

		return $view;
	}
	
	/**
	 * parses the view templates and inserts forms where a corresponding template tag was found
	 * @param array $matches any extra parameters for the parse renderer that were included in the template tag
	 * @return boolean|string
	 */
	public static function parse_form_render($matches = array() )
	{
		$form_id = intval($matches[1]);
		$language_culture = (!empty($matches[2]))?$matches[2]:null;
		
		if(!$form_id)
			return false;
		
		$form = cms::get_form_by_id($form_id, $language_culture);

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
			}//end switch
			
			// regex
			if(!empty($element['regex']))
				$validation_rules[] = "regex:{$element['regex'][0]}";

			// now add in the valdation rules to the element if the rule list is not empty
			if(!empty($validation_rules))
				$elements->{$element['element_name']}->validation = $validation_rules;
		}//end foreach
		
		if(empty($form))
			return '';
			
		$form = new \helpers\html\form($form[0]['form_name'], json_encode($elements));
		
		return $form->render();
	}
	
	/**
	 * a view parser that can include extra template snippets
	 * @param array $matches any extra parameters for the parse renderer that were included in the template tag
	 * @return string
	 */
	public static function parse_template_render($matches)
	{
		$app = \maverick\maverick::getInstance();
		
		// if the template include doesn't exist, just silently fail with an empty string
		if(!file_exists(MAVERICK_VIEWSDIR . "{$matches[1]}.php"))
			return '';

		// build up the replacements (if required) by taking the 3rd parameter in the {template} tag and parsing it as json
		// this requires a little adjustment to the content, as the templating braces don't allow the correct json object notation to be used
		$replacements = array();
		if(isset($matches[2]))
			$replacements = (array)json_decode(str_replace(array('=', '[', ']'), array(':', '{', '}'), $matches[2]) );

		// create a new view and return it
		$template_view = new maverick\mview($matches[1]);
		
		// push the replacements to the view_data array
		if(count($replacements) )
		{
			foreach($replacements as $var => $value)
				$template_view->with($var, $value);
		}
		
		// parse any sub-template includes within this
		$template_view->parse_handler('template', 'main_controller->parse_template_render');
		
		return $template_view->render(false);
	}
	
	/**
	 * uses a template to parse a simple list of items by building lots of mini views
	 * and concatenating the rendered results after performing the simple replacement
	 * @todo allow this to maybe generate more complicated lists based on more details available
	 * @param array $matches any extra parameters for the parse renderer that were included in the template tag
	 * @return string
	 */
	public static function parse_list_render($matches)
	{
		if(!file_exists(MAVERICK_VIEWSDIR . "{$matches[2]}.php"))
			return '';
		
		$renderd_list = '';
		$items = \mdata::get($matches[1]);
		if(is_array($items) )
		{
			foreach($items as $item)
			{
				$replacements = array('item' => $item);
				$list_view = new maverick\mview($matches[2]);
				
				foreach($replacements as $var => $value)
					$list_view->with($var, $value);
				
				$renderd_list .= $list_view->render(false);
			}
		}
		
		return $renderd_list;
	}
}
