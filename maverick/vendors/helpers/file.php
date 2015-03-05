<?php
namespace helpers;

class file
{
	private $path = '';
	private $magic_file = '';
	
	function __construct($path='')
	{
		$this->path = $path;
	}
	
	function __set($name, $value)
	{
		if(in_array($name, array('path', 'magic_file') ) )
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
		
		if(!file_exists($path) && !is_dir($path) )
			return false;
		
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
	
	function info()
	{
		if(!file_exists($this->path) )
			return false;
		else
		{
			$info = new \stdClass();
			
			if(!empty($this->magic_file))
				$finfo = finfo_open(FILEINFO_MIME_TYPE, $this->magic_file);
			else
				$finfo = finfo_open(FILEINFO_MIME_TYPE);
			
			$info->mime = finfo_file($finfo, $this->path);
			$info->name = basename($this->path);
			$info->size = filesize($this->path);
			$info->human_size = self::human_size($info->size);
			$info->created = filectime($this->path);
			$info->human_created = date("F d Y H:i:s", $info->created);
			$info->modified = filemtime($this->path);
			$info->human_modified = date("F d Y H:i:s", $info->modified);
			$info->owner_id = fileowner($this->path);
			$info->group_id = filegroup($this->path);
			$info->type = filetype($this->path);
			$info->perms = decoct(fileperms($this->path) & 0777);
			$info->readable = is_readable($this->path);
			$info->writable = is_writable($this->path);
			$info->executable = is_executable($this->path);
			
			return $info;
		}
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