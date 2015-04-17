<?php
namespace helpers\html;

/**
 * a class for generating html lists from a json string
 */
class lists
{
	private $data;
	private $types;

	/**
	 * the constructor for the list which accepts the json string and an array of list types
	 * @param string $list_json the json string representing the list - this can be any number of levels deep
	 * @param array $list_types an array of the list types which set the type of list to use for a given level. if this is not an array, the entire list will use the unordered type
	 */
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
	
	/**
	 * allows the list object to be used in a string context directly
	 * @return string
	 */
	public function __toString()
	{
		$list_html = $this->build_list($this->data);
		
		return $list_html;
	}
	
	/**
	 * builds the html for the list, calling itself recursively for sub-lists if necessary
	 * @param array $list the list array
	 * @param int $level the level deepness of this list within the whole list object - used to determine what list type this list uses
	 * @return string
	 */
	private function build_list($list, $level=0)
	{
		$list_html = '<' . ( (isset($this->types[$level]))?$this->types[$level]:end($this->types) ) . '>';
		
		foreach($list as $item)
		{
			if(!is_array($item))
				$list_html .= "<li>$item";
			
			if(is_array($item))
				$list_html .= $this->build_list($item, $level+1);
			
			if(!is_array($item))
				$list_html .= '</li>';
		}
		
		$list_html .= '</' . ( (isset($this->types[$level]))?$this->types[$level]:end($this->types) ) . '>';

		return $list_html;
	}
}
