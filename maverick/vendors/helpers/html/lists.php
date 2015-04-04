<?php
namespace helpers\html;

class lists
{
	private $data;
	private $types;

	public function __construct($list_json, $list_types)
	{
		$items = \json_decode($list_json);
		
		if(is_array($items))
			$this->data = $items;
		
		if(is_array($list_types))
			$this->types = $list_types;
		else
			$this->types = array('ul');
	}
	
	public function __toString()
	{
		$list_html = $this->build_list($this->data);
		
		return $list_html;
	}
	
	private function build_list($list, $level=0)
	{
		$list_html = '<' . ( (isset($this->types[$level]))?$this->types[$level]:end($this->types) ) . '>';
		
		foreach($list as $item)
		{
			if(!is_array($item))
				$list_html .= "<li>$item";
			
			if(is_array($item))
				$list_html .= $this->build_list($item, $level+1);
			
			$list_html .= '</li>';
		}
		
		$list_html .= '</' . ( (isset($this->types[$level]))?$this->types[$level]:end($this->types) ) . '>';

		return $list_html;
	}
}
