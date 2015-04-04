<?php
namespace helpers\html;

class html
{
	static $_instance;
	private $cached_snippets = array();
	
	private function __construct() {}
	
	private function __clone() {}
	
	public static function getInstance()
	{
		if(!(self::$_instance instanceof self))
			self::$_instance = new self;

		return self::$_instance;
	}
	
	public static function load_snippet($filename, $replacements)
	{
		$h = html::getInstance();

		if(!file_exists($filename))
			return false;
		
		$contents = '';
		$find = $replace = array();

		if(isset($h->cached_snippets[$filename]))
			$contents = $h->cached_snippets[$filename];
		else
		{
			$fh = fopen($filename, 'r');
			while (!feof($fh))
				$contents .= fread($fh, 8192);
			fclose($fh);
			
			var_dump($filename);
		}
		
		$h->cached_snippets[$filename] = $contents;
		
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