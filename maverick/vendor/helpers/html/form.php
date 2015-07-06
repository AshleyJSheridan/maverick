<?php
namespace helpers\html;

/**
 * a class to render an html form including all of its elements and errors (if the form is posted)
 */
class form
{
	private $elements = array();
	private $name = 'form';
	private $method = 'post';
	private $action = null;
	private $enctype = 'application/x-www-form-urlencoded';
	private $labels = 'wrap';
	private $novalidate = false;
	private $autocomplete = false;
	private $snippets;
	private $class;
	private $id;
	
	/**
	 * basic constructor for the form html object
	 * @param string $name the name of the form
	 * @param string $elements a json formatted string of form elements - the format for which is outlined below:
	 * 
	 *	{
	 *		"title":{"type":"select","label":"Title","class":"form_title","id":"form_title","values":["Mr","Mrs","Miss","Other"],"validation":["required"]},
	 *		"name":{"type":"text","label":"Name","class":"form_name","id":"form_name","value":"John Smith","placeholder":"John Smith","validation":["required","alpha"]},
	 *		"age":{"type":"number","label":"Age","class":"form_age","placeholder":"42","validation":["required","numeric","between:18:100"]},
	 *		"email":{"type":"email","label":"Email","class":"form_email","placeholder":"email@test.com","validation":["required","email"]},
	 *		"postcode":{"type":"text","label":"Postcode","class":"form_postcode","placeholder":"w1 1ab","validation":["required","regex:/^([a-pr-uwyz][a-hk-y]{0,1}\\\d[\\\da-hjkst]{0,1} \\\d[abd-hjlnp-uw-z]{2})$/i"]},
	 *		"web_address":{"type":"text","label":"Web Address","class":"form_web_address","placeholder":"http://www.somesite.com","validation":["url"]},
	 *		"phone":{"type":"text","label":"Phone","class":"form_phone","placeholder":"0123456789","validation":["phone"]},
	 *		"colour":{"type":"radio","label":"Favourite Colour","class":"form_colour","values":["Red","Blue","Green","Yellow","Black"]},
	 *		"submit":{"type":"submit","value":"Submit","class":"form_submit"}
	 *	}
	 * 
	 * each element is a name object with a series of parameters for that item, including type, value (or values for things like checkboxes and select lists,)
	 * class, id, etc. 
	 */
	public function __construct($name, $elements = null)
	{
		$this->name = $name;
		
		if($elements && $elements = \json_decode($elements) )
			$this->set_elements ($elements);
	}
	
	/**
	 * a magic setter for the form html object
	 * @param string $param the name of the element to set
	 * @param string $value the value to set - which will be subject to certain constraints per element
	 */
	public function __set($param, $value)
	{
		switch($param)
		{
			case 'name':
			case 'class':
			case 'id':
				if(strlen($value))
					$this->$param = $value;
				break;
			case 'method':
				if(in_array($value, array('get', 'post') ) )
					$this->method = $value;
				break;
			case 'enctype':
				if(in_array($value, array('application/x-www-form-urlencoded', 'multipart/form-data', 'text/plain', 'application/json') ) )
					$this->enctype = $value;
				break;
			case 'labels':
				if(in_array($value, array('wrap', 'wrap-after', 'before', 'after', 'none') ) )
					$this->labels = $value;
				break;
			case 'novalidate':
			case 'autocomplete':
				if(is_bool($value))
					$this->$param = $value;
				break;
			case 'snippets':
				if(is_dir($value))
					$this->$param = $value;
				break;
		}
	}
	
	/**
	 * generates the html for the form object using the options set on the object itself
	 * @return string
	 */
	public function render()
	{
		// build the main form tag
		$html = '<form';
		foreach(array('name', 'method', 'action', 'enctype', 'class', 'id') as $form_attr)
		{
			if(!is_null($this->$form_attr) )
				$html .= " $form_attr=\"{$this->$form_attr}\"";
		}
		$html .= ($this->novalidate)?' novalidate':'';
		
		$html .= '>';
		
		// build the form elements
		foreach($this->elements as $element)
			$html .= $this->render_element($element, (isset($element->labels)?$element->labels:$this->labels), $this->snippets );

		$html .= '</form>';
		
		return $html;
	}
	
	/**
	 * generates the html for an individual form element using a template file for that form element type
	 * @param \helpers\html\form_element $element the form element object
	 * @param string $labels a string representing the position of the element label in relation to the element
	 * @param array $error_tags an array of two strings which should be used to wrap errors when the form is posted, defaults to a span with an error class
	 * @return string
	 */
	private function render_element($element, $labels=null, $snippets_dir = null, $error_tags = array('<span class="error">', '</span>') )
	{
		$html = '';
		
		if($snippets_dir && file_exists("$snippets_dir/input_{$element->type}.php"))
			$snippet = "$snippets_dir/input_{$element->type}.php";
		else
			$snippet = __DIR__ . "/snippets/input_{$element->type}.php";
		
		switch($element->type)
		{
			case 'text':
			case 'email':
			case 'hidden':
			case 'file':
			case 'url':
			case 'tel':
			case 'number':
			case 'range':
			case 'color':
			case 'date':
			case 'time':
			case 'password':
				$value = isset($_REQUEST[$element->name])?$_REQUEST[$element->name]:(strlen($element->value)?$element->value:'');
				
				foreach($element->validation as $validation)
				{
					if(preg_match('/^([a-z]+)=(.+)$/', $validation, $matches))
					{
						${$matches[1]} = $matches[2];
					}
				}

				$html .= \helpers\html\html::load_snippet($snippet, array(
					'class' => ($element->class)?"class=\"{$element->class}\"":'',
					'id' => ($element->id)?"id=\"{$element->id}\"":'',
					'name' => $element->name,
					'value' => strlen($value)?"value=\"$value\"":'',
					'placeholder' => ($element->placeholder)?"placeholder=\"{$element->placeholder}\"":'',
					'required' => in_array('required', $element->validation)?'required="required"':'',
					'error' => \validator::get_first_error($element->name, $error_tags),
					'min' => isset($min)?"min=\"$min\"":'',
					'max' => isset($max)?"max=\"$max\"":'',
					'minlength' => isset($minlength)?"minlength=\"$minlength\"":'',
					'maxlength' => isset($maxlength)?"maxlength=\"$maxlength\"":'',
					'size' => isset($size)?"size=\"$size\"":'',
					'step' => isset($step)?"step=\"$step\"":'',
					'accept' => isset($accept)?"accept=\"$accept\"":'',
					'spellcheck' => $element->spellcheck?'spellcheck="true"':'spellcheck="false"',
				) );
				break;
			case 'textarea':
				$value = isset($_REQUEST[$element->name])?$_REQUEST[$element->name]:(strlen($element->value)?$element->value:'');
				
				$html .= \helpers\html\html::load_snippet($snippet, array(
					'class' => ($element->class)?"class=\"{$element->class}\"":'',
					'id' => ($element->id)?"id=\"{$element->id}\"":'',
					'name' => $element->name,
					'value' => strlen($value)?$value:'',
					'placeholder' => ($element->placeholder)?"placeholder=\"{$element->placeholder}\"":'',
					'required' => in_array('required', $element->validation)?'required="required"':'',
					'error' => \validator::get_first_error($element->name, $error_tags),
					'spellcheck' => $element->spellcheck?'spellcheck="true"':'spellcheck="false"',
				) );
				break;
			case 'submit':
				$html .= \helpers\html\html::load_snippet($snippet, array(
					'class' => ($element->class)?"class=\"{$element->class}\"":'',
					'id' => ($element->id)?"id=\"{$element->id}\"":'',
					'name' => $element->name,
					'value' => ($element->value)?"value=\"{$element->value}\"":'',
				) );
				$labels = null;	// don't wrap the submit in a label
				break;
			case 'select':
			case 'datalist':
				$html .= \helpers\html\html::load_snippet($snippet, array(
					'class' => ($element->class)?"class=\"{$element->class}\"":'',
					'id' => ($element->id)?"id=\"{$element->id}\"":'',
					'name' => $element->name,
					'values' => \helpers\html\form_element::build_select_options($element->values, $element->name, $snippets_dir),
					'error' => \validator::get_first_error($element->name, $error_tags),
				) );
				break;
			case 'checkbox':
			case 'radio':
				foreach($element->values as $value)
				{
					$element_html = \helpers\html\html::load_snippet($snippet, array(
						'class' => ($element->class)?"class=\"{$element->class}\"":'',
						'id' => ($element->id)?"id=\"{$element->id}\"":'',
						'name' => $element->name,
						'value' => "value=\"$value\"",
						'error' => \validator::get_first_error($element->name, $error_tags),
						'checked' => (isset($_REQUEST[$element->name]) && ($_REQUEST[$element->name] == $value || (is_array($_REQUEST[$element->name]) && in_array($value, $_REQUEST[$element->name]) ) ) )?'checked="checked"':'',
					) );
					$fake_element = new \stdClass();
					$fake_element->type = $element;
					$fake_element->class = ($element->class)?"class=\"{$element->class}\"":'';
					$fake_element->label = (count($element->values)>1)?$value:$element->label;
					$fake_element->id = '';
					$html .= $this->wrap_element($fake_element, $element_html, $labels, $snippets_dir);
				}
				$labels = "{$element->type}_group";
				
				break;
		}

		if(!is_null($labels) && $labels != 'none' && ($element->type != 'checkbox' || ($element->type == 'checkbox' && count($element->values) > 1 ) ) )
			$html = $this->wrap_element($element, $html, $labels, $snippets_dir);
		
		return $html;
	}
	
	/**
	 * add a label to a form element, using the specified type of label layout and using the corresponding template file
	 * @param \helpers\html\form_element $element the form element object
	 * @param string $element_html the rendered html of the element
	 * @param string $labels_type the template to use for the label
	 * @return string
	 */
	private function wrap_element($element, $element_html, $labels_type, $snippets_dir)
	{
		if($snippets_dir && file_exists("$snippets_dir/label_$labels_type.php"))
			$snippet = "$snippets_dir/label_$labels_type.php";
		else
			$snippet = __DIR__ . "/snippets/label_$labels_type.php";
		
		$html = \helpers\html\html::load_snippet($snippet, array(
			'label' => $element->label,
			'element' => $element_html,
			'id' => ($element->id)?$element->id:'',
			'class' => $element->class,
		) );
		
		return $html;
	}
	
	/**
	 * add the form elements to the form, generating an form element object for each element
	 * returns false if a$elements is not an object
	 * @param \stdClass $elements an object generated from a json decoded string representing all the form elements for the form
	 * @return boolean
	 */
	private function set_elements($elements)
	{
		if(!is_object($elements))
			return false;

		foreach($elements as $element_name => $element)
			$this->elements[$element_name] = new \helpers\html\form_element($element_name, $element);
	}
}

/**
 * a class for individual form elements to be attached to a form
 */
class form_element
{
	private $type;
	private $name;
	private $label;
	private $class;
	private $id;
	private $value;
	private $values;
	private $checked;
	private $placeholder;
	private $spellcheck = false;
	private $validation = array();
	
	/**
	 * constructor for the form element objects
	 * @param string $name the name for this form element
	 * @param \stdClass $element_obj the json decoded object representing a form element
	 */
	public function __construct($name, $element_obj)
	{
		$this->name = $name;
		
		foreach(array('type', 'name', 'label', 'class', 'id', 'value', 'values', 'placeholder', 'validation', 'spellcheck') as $part)
		{
			if(isset($element_obj->$part))
				$this->$part = $element_obj->$part;
		}
	}
	
	/**
	 * getter method for the form element object
	 * @param string $param the name of the member variable to fetch
	 * @return mixed
	 */
	public function __get($param)
	{
		if(isset($this->$param))
			return $this->$param;
	}
	
	/**
	 * a static method to build a list of select list options using a template and return that list as an html string
	 * @param array $options a list of values for the select list
	 * @param string $element_name the name of the select list element - used to determine if this should be marked as selected in the rendered html or not (e.g. for a form posted with errors)
	 * @return string
	 */
	public static function build_select_options($options, $element_name, $snippets_dir)
	{
		$html = '';
		
		foreach($options as $option)
		{
			$selected = (isset($_REQUEST[$element_name]) && $_REQUEST[$element_name] == $option)?'selected="selected"':'';
			
			if($snippets_dir && file_exists("$snippets_dir/input_option.php"))
				$snippet = "$snippets_dir/input_option.php";
			else
				$snippet = __DIR__ . "/snippets/input_option.php";
			
			$html .= \helpers\html\html::load_snippet($snippet, array(
				'value' => $option,
				'selected' => $selected,
			) );
		}
		
		return $html;
	}
}