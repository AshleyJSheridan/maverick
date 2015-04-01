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
				if(in_array($value, array('wrap', 'wrap-after', 'before', 'after') ) )
					$this->labels = $value;
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
		$html .= '>';
		
		
		// build the form elements
		foreach($this->elements as $element)
			$html .= $this->render_element($element);
		
		
		$html .= '</form>';
		
		return $html;
	}
	
	private function render_element($element)
	{
		$html = '';
		
		switch($element->type)
		{
			case 'text':
			case 'number':
				$snippet = __DIR__ . "/snippets/input_{$element->type}.php";	// TODO: allow this snippets directory to be overloaded with userland views
				$html .= \helpers\html\html::load_snippet($snippet, array(
					'class' => $element->class,
					'id' => $element->id,
					'name' => $element->name,
					'value' => $element->value,
					'placeholder' => $element->placeholder,
					'required' => in_array('required', $element->validation)?'required="required"':'',
				) );
				
				var_dump($element);

				break;
		}

		// TODO: add the labels in with sprintf call
		
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
	private $placeholder;
	private $validation = array();
	
	public function __construct($name, $element_obj)
	{
		$this->name = $name;
		
		foreach(array('type', 'name', 'label', 'class', 'id', 'value', 'placeholder', 'validation') as $part)
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
}