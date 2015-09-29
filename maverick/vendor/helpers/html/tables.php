<?php
namespace helpers\html;

/**
 * a class to render an html table of various types
 * @package Maverick
 * @author Ashley Sheridan <ash@ashleysheridan.co.uk>
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
	
	/**
	 * create the table object
	 * @param string $name    the name of the table
	 * @param string $type    the type of table: data, xref
	 * @param array  $data    the data to use within the table
	 * @param array  $headers an array of headers for the table - multi-dimensional for xref tables
	 */
	public function __construct($name, $type, $data, $headers)
	{
		if(in_array($type, array('data', 'xref') ) )
			$this->type = $type;
		
		$this->data = $data;
		$this->name = $name;
		$this->headers = $headers;
	}
	
	/**
	 * magic setter for the table object
	 * @param string $param the member variable to set
	 * @param mixed  $value the value to set it to
	 * @return bool
	 */
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
	
	/**
	 * render a table object to HTML
	 * @return string
	 */
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

		return $html;
	}
	
	/**
	 * render a data tables contents
	 * @return string
	 */
	private function render_data()
	{
		// create if the headers exists
		// 2 header style table
		if(count($this->headers) == 2 && isset($this->headers[0][0]) && isset($this->headers[1][0]) )
		{
			// generate the column headers
			$html = '<tr><th></th>';
			foreach($this->headers[0] as $header)
				$html .= "<th>$header</th>";
			$html .= '</tr>';
			
			// generate the row headers and the data
			for($i=0; $i<count($this->headers[1]); $i++)
			{
				// the header
				$html .= "<tr><th>{$this->headers[1][$i]}</th>";
				
				// and now the data
				foreach($this->data[$i] as $datum)
					$html .= "<td>$datum</td>";
				
				$html .= '</tr>';
			}
			return $html;
		}//end if
		
		// 1 header style table
		if(count($this->headers) > 0 && isset($this->headers[0]) &&  !is_array($this->headers[0]) )
		{
			// generate the column headers
			$html = '<tr>';
			foreach($this->headers as $header)
				$html .= "<th>$header</th>";
			$html .= '</tr>';
			
			// generate the data
			for($i=0; $i<count($this->data); $i++)
			{
				foreach($this->data[$i] as $datum)
					$html .= "<td>$datum</td>";
				
				$html .= '</tr>';
			}
			
			return $html;
		}//end if
		
		return '';
	}
	
	/**
	 * render a xref tables contents
	 * @return string
	 */
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
				}//end foreach
			}//end if
			
			return $html;
		}//end if
		return '';
	}
	
	/**
	 * convert a json string to an array to use in the table object generation
	 * converts json objects into arrays after if necessary
	 * @param string $json the json string to decode
	 * @return array
	 */
	private function extract_json($json)
	{
		$array = json_decode($json);
		
		if(is_object($array))
		{
			$temp_array = array();
			
			foreach($array as $key => $value)
				$temp_array[$key] = $value;
			
			$array = $temp_array;
		}
		
		return $array;
	}	
}
