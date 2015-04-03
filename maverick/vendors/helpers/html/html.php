<?php
namespace helpers\html;

class html
{
	
	
	public function __construct()
	{
		
	}
	
	public static function load_snippet($filename, $replacements)
	{
		//TODO: cache these views so that they don't require reading in the same files over and over
		if(!file_exists($filename))
			return false;
		
		$contents = '';
		$find = $replace = array();

		$fh = fopen($filename, 'r');
		while (!feof($fh))
			$contents .= fread($fh, 8192);
		fclose($fh);
		
		// replace the sections in the template snippet with the values from the array passed in
		foreach($replacements as $key => $value)
		{
			if($value)
			{
				$find[] = '{{' . $key . '}}';
				$replace[] = $value;
			}
		}
		$contents = str_replace($find, $replace, $contents);
		
		// now get rid of any placeholders left that weren't used
		$contents = preg_replace('/\{\{[^\}]+\}\}/', '', $contents);
		
		return $contents;
	}
}