<?php
namespace helpers;

/**
 * a file helper class to deal with providing information about files and directories
 */
class file
{
	private $path = '';
	private $magic_file = '';
	
	/**
	 * creates the file object from a path
	 * @param string $path the path to use for this file object
	 */
	function __construct($path='')
	{
		$this->path = $path;
	}
	
	/**
	 * magic setter for the file object
	 * @param string $name the name of the member variable to set
	 * @param string $value the value to set the member variable to
	 * @return boolean
	 */
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
	
	/**
	 * returns a string determining the type of object this represents - returns false if the path is invalid
	 * @param bool $no_symbolic_check whether or not to check if this file/directory is also a symbolic link
	 * @return boolean|string
	 */
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
	
	/**
	 * returns the size of the file/directory
	 * if the file object points to a directory and the host system is Linux (for example), 
	 * the size will represent the size that that directory only takes on disk, and not its contents. This is typically something like 4096 bytes
	 * @param bool $human_size whether or not to represent the size in human-readable terms rather than just bytes
	 * @return type
	 */
	function size($human_size = false)
	{
		$bytes = filesize($this->path);
		
		if(!$human_size)
			return $bytes;
		else
			return self::human_size($bytes);
	}
	
	/**
	 * generate a directory tree as a multi-dimensional array using the path set in the file object
	 * a path can be supplied in the call to this method, but typically that is reserved for the method 
	 * to use when calling itself on sub-directories
	 * @param type $dir
	 * @return array
	 */
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
	
	/**
	 * build a flat tree with full paths of each file found
	 * a path can be supplied in the call to this method, but typically that is reserved for the method 
	 * to use when calling itself on sub-directories
	 * @param type $dir
	 * @return array
	 */
	function flat_tree($dir=false)
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
			{
				$temp = $this->flat_tree("$path/$entry");
				$files = array_merge($files, $temp);
			}
			else
				$files[] = "$path/$entry";
		}
		
		sort($files);
		return $files;
	}


	/**
	 * returns an object containing information about a file or directory, including size, permissions, dates, etc
	 * @return boolean|\stdClass
	 */
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
	
	/**
	 * converts a number of bytes into a human-readable value
	 * 
	 * originally sourced from http://aidanlister.com/2004/04/human-readable-file-sizes/ with argument order changes
	 * 
	 * @param int $size the byte value to convert
	 * @param string $retstring the sprintf format of the string to return
	 * @param string $system the byte system to use, si is 1000 bytes to a kilobyte, bi is 1024 bytes to a kibibyte
	 * @param string|null $max the maximum unit to measure to, null means no maximum
	 * @return string
	 */
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