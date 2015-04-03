<?php
namespace helpers\html;

class form
{
	private $elements = array();
	private $name = 'form';
	private $method = 'post';
	private $action = null;
	private $enctype = 'application/x-www-form-urlencoded';
	private $labels = 'wrap';
	private $novalidate = false;
	
	public function __construct($name, $elements = null)
	{
		$this->name = $name;
		
		if($elements && $elements = \json_decode($elements) )
			$this->set_elements ($elements);
	}
	
	public function __set($param, $value)
	{
		switch($param)
		{
			case 'name':
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
				if(is_bool($value))
					$this->$param = $value;
				break;
		}
	}
	
	public function render()
	{
		// build the main form tag
		$html = '<form';
		foreach(array('name', 'method', 'action', 'enctype') as $form_attr)
		{
			if(!is_null($this->$form_attr) )
				$html .= " $form_attr=\"{$this->$form_attr}\"";
		}
		$html .= ($this->novalidate)?' novalidate':'';
		
		$html .= '>';
		
		
		// build the form elements
		foreach($this->elements as $element)
			$html .= $this->render_element($element, (isset($element->labels)?$element->labels:$this->labels) );

		$html .= '</form>';
		
		return $html;
	}
	
	private function render_element($element, $labels=null)
	{
		$html = '';
		
		$snippet = __DIR__ . "/snippets/input_{$element->type}.php";	// TODO: allow this snippets directory to be overloaded with userland views
		
		switch($element->type)
		{
			case 'text':
			case 'number':
			case 'email':
			case 'hidden':
				$html .= \helpers\html\html::load_snippet($snippet, array(
					'class' => ($element->class)?"class=\"{$element->class}\"":'',
					'id' => ($element->id)?"id=\"{$element->id}\"":'',
					'name' => $element->name,
					'value' => ($element->value)?"value=\"{$element->value}\"":'',
					'placeholder' => ($element->placeholder)?"placeholder=\"{$element->placeholder}\"":'',
					'required' => in_array('required', $element->validation)?'required="required"':'',
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
				$html .= \helpers\html\html::load_snippet($snippet, array(
					'class' => ($element->class)?"class=\"{$element->class}\"":'',
					'id' => ($element->id)?"id=\"{$element->id}\"":'',
					'name' => $element->name,
					'values' => \helpers\html\form_element::build_select_options($element->values, $element->name),
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
						'value' => $value,
					) );
					$fake_element = new \stdClass();
					$fake_element->label = $value;
					$fake_element->id = '';
					$html .= $this->wrap_element($fake_element, $element_html, $labels);
				}
				$labels = "{$element->type}_group";
				break;
		}

		if(!is_null($labels) && $labels != 'none')
			$html = $this->wrap_element($element, $html, $labels);
		
		return $html;
	}
	
	private function wrap_element($element, $element_html, $labels_type)
	{
		$snippet = __DIR__ . "/snippets/label_$labels_type.php";	// TODO: allow this snippets directory to be overloaded with userland views
		$html = \helpers\html\html::load_snippet($snippet, array(
			'label' => $element->label,
			'element' => $element_html,
			'id' => ($element->id)?$element->id:'',
		) );
		
		return $html;
	}
	
	private function set_elements($elements)
	{
		if(!is_object($elements))
			return false;

		foreach($elements as $element_name => $element)
			$this->elements[$element_name] = new \helpers\html\form_element($element_name, $element);
		
	}
}

class form_element
{
	private $type;
	private $name;
	private $label;
	private $class;
	private $id;
	private $value;
	private $values;
	private $placeholder;
	private $validation = array();
	
	public function __construct($name, $element_obj)
	{
		$this->name = $name;
		
		foreach(array('type', 'name', 'label', 'class', 'id', 'value', 'values', 'placeholder', 'validation') as $part)
		{
			if(isset($element_obj->$part))
				$this->$part = $element_obj->$part;
		}
	}
	
	public function __get($param)
	{
		if(isset($this->$param))
			return $this->$param;
	}
	
	public static function build_select_options($options, $element_name)
	{
		$html = '';
		
		foreach($options as $option)
		{
			$selected = (isset($_REQUEST[$element_name]) && $_REQUEST[$element_name] == $option)?'selected="selected"':'';
			
			$snippet = __DIR__ . "/snippets/input_option.php";	// TODO: allow this snippets directory to be overloaded with userland views
			$html .= \helpers\html\html::load_snippet($snippet, array(
				'value' => $option,
				'selected' => $selected,
			) );
		}
		
		return $html;
	}
}