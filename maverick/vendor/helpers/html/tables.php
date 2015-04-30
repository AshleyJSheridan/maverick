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
	private $xref_x = 'yes';
	private $snippets_dir;
	
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
			case 'xref_x':
				if(is_string($value))
					$this->$param = $value;
				break;
		}
	}
	
	public function render()
	{
		// convert json to PHP arrays
		if(!is_array($this->headers))
			$this->headers = $this->extract_json($this->headers);
		if(!is_array($this->data))
			$this->data = $this->extract_json($this->data);
		
		// generate a table of the appropriate type with sub-render method
		$table_html = $this->{"render_$this->type"}();
		
		// get the snippets directory if set
		if($this->snippets_dir && file_exists("$this->snippets_dir/table.php"))
			$snippet = "$this->snippets_dir/table.php";
		else
			$snippet = __DIR__ . "/snippets/table.php";
		
		// build up the replacements array
		$replacements = array(
			'body' => $table_html,
			'class' => strlen($this->class)?"class=\"$this->class\"":'',
			'id' => strlen($this->id)?"id=\"$this->id\"":'',
			'caption' => strlen($this->caption)?"<caption>$this->caption</caption>":'',
		);
		
		$html = \helpers\html\html::load_snippet($snippet, $replacements);
		
		//var_dump($table_html);
		
		return $html;
	}
	
	private function render_xref()
	{
		$name_id = \helpers\html\html::generate_id($this->name);

		// create if the header exists
		if(!empty($this->headers))
		{
			// generate the header
			$html = <<<HEADER
			<tr class="header_row">
				<th id="{$name_id}_$name_id">{$this->name}</th>
HEADER;
		
			foreach($this->headers as $header)
			{
				$header_id = \helpers\html\html::generate_id($header);
				
				$html .= <<<HEADER
				<th id="{$name_id}_$header_id">$header_id</th>
HEADER;
			}
		
			$html .= '</tr>';
			
			// generate the body of the table
			// this bit checks that only a single dimension array is used for the data of this type of table
			$values = array_values($this->data);
			if(!is_array(reset($values)))
			{
				foreach($this->data as $heading => $value)
				{
					$html .= <<<DATA
					<tr>
						<td headers="{$name_id}_$name_id">$heading</td>
DATA;
					// run through each header and output the right bits for each one
					foreach($this->headers as $header)
					{
						if(strcasecmp($header, $value) === 0 )
						{
							$header_id = \helpers\html\html::generate_id($header);
							
							$html .= <<< DATA
							<td headers="{$name_id}_$header_id">{$this->xref_x}</td>
DATA;
						}
						else
							$html .= '<td></td>';
					}
					$html .= '</tr>';
				}
			}
			
			return $html;
		}
		return '';
	}
	
	private function extract_json($json)
	{
		return json_decode($json);
	}
	
}