<?php
namespace helpers\html;

/**
 * a class to render an html table of various types
 */
class tables
{
	private $type = 'data';
	private $headers = array();
	private $data = array();
	private $caption = '';
	private $class = '';
	private $id = '';
	private $name = '';
	
	public function __construct($name, $type, $data, $headers)
	{
		if(in_array($type, array('data', 'xref') ) )
			$this->type = $type;
		
		$this->data = $data;
		$this->name = $name;
		$this->headers = $headers;
	}
	
	public function __set($param, $value)
	{
		switch($param)
		{
			case 'type':
				if(in_array($type, array('data', 'xref') ) )
					$this->type = $type;
				break;
			case 'caption':
			case 'class':
			case 'id':
				$this->caption = $value;
				break;
		}
	}
	
	public function render()
	{
		$table_html = $this->{"render_$this->type"}();
		
		var_dump($table_html);
	}
	
	private function render_xref()
	{
		if(!is_array($this->headers))
			$this->extract_json_headers();
		
		// create the header
		if(!empty($this->headers))
		{
			$html = '<tr class="header_row">';
		
			foreach($this->headers as $header)
			{
				$header_id = \helpers\html\html::generate_id($header);
				
				$html .= <<<HEADER
				<th id="{$this->name}_$header_id">$header_id</th>
HEADER;
			}
		
			$html .= '</tr>';
			
			return $html;
		}
		return '';
	}
	
	private function extract_json_headers()
	{
		$this->headers = json_decode($this->headers);
	}
	
}