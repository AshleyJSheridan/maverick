<?php
namespace helpers;

class file
{
	private $path = '';
	
	function __construct($path='')
	{
		$this->path = $path;
	}
	
	function __set($name, $value)
	{
		if(in_array($name, array('path') ) )
		{
			$this->$name = $value;
			return true;
		}
		else
			return false;
	}
	
	function type($no_symbolic_check = false)
	{
		if(!file_exists($this->path))
			return false;
		else
		{
			$return_type = '';
			
			if(!$no_symbolic_check)
				$return_type .= (is_link($this->path)?'symbolic ':'' );
			
			if(is_file($this->path))
				$return_type .= 'file';
			
			if(is_dir($this->path))
				$return_type .= 'directory';
			
			return $return_type;
		}
	}
	
	function size($human_size = false)
	{
		$bytes = filesize($this->path);
		
		if(!$human_size)
			return $bytes;
		else
			return self::human_size($bytes);
	}
	
	function tree($dir=false)
	{
		$path = ($dir)?$dir:$this->path;
		
		$files = array();
		$dh = opendir($path);
		
		while(false !== ($entry = readdir($dh) ) )
		{
			if(in_array($entry, array('.', '..') ) )
				continue;
			
			if(is_dir("$path/$entry") )
				$files[] = $this->tree("$path/$entry");
			else
				$files[] = $entry;
		}
		
		sort($files);
		return $files;
	}
	
	// originally sourced from http://aidanlister.com/2004/04/human-readable-file-sizes/ with argument order changes
	static function human_size($size, $retstring = '%01.0f %s', $system = 'bi', $max = null)
	{
		// Pick units
		$systems['si']['prefix'] = array('B', 'K', 'MB', 'GB', 'TB', 'PB');
		$systems['si']['size']   = 1000;
		$systems['bi']['prefix'] = array('B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB');
		$systems['bi']['size']   = 1024;
		$sys = isset($systems[$system]) ? $systems[$system] : $systems['si'];

		// Max unit to display
		$depth = count($sys['prefix']) - 1;
		if ($max && false !== $d = array_search($max, $sys['prefix']))
			$depth = $d;

		$i = 0;
		while ($size >= $sys['size'] && $i < $depth)
		{
			$size /= $sys['size'];
			$i++;
		}

		return sprintf($retstring, $size, $sys['prefix'][$i]);
	}
}