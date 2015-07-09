<?php
namespace helpers\html;

/**
 * a class containing basic shared methods for all html helper sub-classes
 */
class html
{
	static $_instance;
	protected $cached_snippets = array();
	
	private function __construct() {}
	
	private function __clone() {}
	
	/**
	 * returns an instance of the singleton html object - there can be only one!
	 * @return type
	 */
	public static function getInstance()
	{
		if(!(self::$_instance instanceof self))
			self::$_instance = new self;

		return self::$_instance;
	}
	
	/**
	 * loads in a string snippet of html from a file (if it can find the file) and returns that
	 * if the snippet file is not found, this method returns false
	 * {{placeholders}} within the snippet are replaced with values in the $replacements array if available
	 * any leftover placeholders are then removed to avoid them being left in the returned html
	 * 
	 * the loaded snippet is saved to an internal array to avoid reading in the same file repeatedly
	 * (such as for form elements where there may be many fields of the same type, for example)
	 * @param string $filename the path to the snippet file
	 * @param array $replacements an array of find/replace values to replace {{placeholders}} within the snippet with
	 * @param bool $replace_all whether or not to replace all the {{placeholders}} within the snippet being loaded
	 * @return boolean
	 */
	public static function load_snippet($filename, $replacements, $replace_all=true)
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
		if($replace_all)
			$contents = preg_replace('/\{\{[^\}]+\}\}/', '', $contents);
		
		return $contents;
	}
	
	public static function generate_id($string)
	{
		$id = preg_replace('/[^\p{L}]/', '_', $string);
		$id = strtolower(str_replace('__', '_', $id) );
		
		return $id;
	}
}