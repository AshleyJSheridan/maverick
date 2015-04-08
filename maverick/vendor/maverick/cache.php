<?php
namespace maverick;

/**
 * a class used to handle caching of strings of information, typically generated views
 * the caching is handled either with files in the cache directory, or by APC which is faster but requires an extra Apache module
 */
class cache
{
	/**
	 * stores an item in the cache
	 * @param string $key the key to store, which should be unique
	 * @param string $value the value to store against the key
	 */
	static function store($key, $value)
	{
		$app = \maverick\maverick::getInstance();
		
		$cache_duration = intval($app->get_config('cache.duration'));
		
		// if apc is selected as the mechanism, but not available on the system, switch to file-based caching
		if($app->get_config('cache.type') == 'apc' && function_exists('apc_fetch') )
			apc_add($key, $value, $cache_duration);
		else
		{
			$cache_file = MAVERICK_BASEDIR . "cache/$key";

			// check to see if either the cached view file does not exist, or if it does, that it's not too old
			if(!file_exists($cache_file) || (file_exists($cache_file) && ((time() - filemtime($cache_file)) > $cache_duration) ) )
			{
				// create the cache file and store the contents of the view in it
				$fh = fopen($cache_file, 'w');
				fwrite($fh, $value);
				fclose($fh);
			}

		}
	}
	
	/**
	 * retreives an item from the cache
	 * @param string $key the key to retreive the item for
	 * @return string
	 */
	static function fetch($key)
	{
		$app = \maverick\maverick::getInstance();
		
		$cache_duration = intval($app->get_config('cache.duration'));
		
		$contents = '';
		
		// if apc is selected as the mechanism, but not available on the system, switch to file-based caching
		if($app->get_config('cache.type') == 'apc' && function_exists('apc_fetch') )
			$contents = apc_fetch($key);
		else
		{
			$cache_file = MAVERICK_BASEDIR . "cache/$key";
			
			if(file_exists($cache_file) && ((time() - filemtime($cache_file)) < $cache_duration))
			{
				$fh = fopen($cache_file, 'rb');
				while (!feof($fh))
					$contents .= fread($fh, 8192);
				fclose($fh);
			}
		}
		
		return $contents;
	}
	
	/**
	 * clear all caches held for the cache type specified in the cache config file
	 */
	static function clear()
	{
		$app = \maverick\maverick::getInstance();
		
		// if apc is selected as the mechanism, but not available on the system, switch to file-based caching
		if($app->get_config('cache.type') == 'apc' && function_exists('apc_fetch') )
			apc_clear_cache('user');
		else
		{
			$cache_dir = MAVERICK_BASEDIR . "cache";
			
			if($dh = opendir($cache_dir))
			{
				while (false !== ($file = readdir($dh)))
				{
					if(substr($file, 0, 1) != '.')
						unlink("$cache_dir/$file");
				}
			}
		}
	}
}