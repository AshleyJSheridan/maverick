<?php
use \maverick\route as route;

if(!defined('MAVERICK_BASEDIR'))
	define('MAVERICK_BASEDIR', dirname(__FILE__) . '/../maverick/');

if(!defined('MAVERICK_VIEWSDIR'))
	define('MAVERICK_VIEWSDIR', dirname(__FILE__) . '/../maverick/views/');

if(!defined('MAVERICK_LOGSDIR'))
	define('MAVERICK_LOGSDIR', dirname(__FILE__) . '/../logs/');

if(!defined('MAVERICK_HTDOCS'))
	define('MAVERICK_HTDOCS', dirname(__FILE__) . '/');

function __autoload($class)
{
	// a more traditional autoloader - used for loading in controllers and models
	$maverick = \maverick\maverick::getInstance();
	$class_found = false;
	
	foreach($maverick->get_config('config.paths') as $path)
	{
		if(file_exists("$path/$class.php"))
		{
			require_once "$path/$class.php";
			$class_found = true;
			break;
		}
	}

	// add the maverick directory in as the namespace if it doesn't exist
	if(strpos($class, '\\') === false)
		$class = 'maverick/' . $class;

	// PSR-0 autoloader - sourced from http://www.sitepoint.com/autoloading-and-the-psr-0-standard/
	if(!$class_found)
	{
		$className = ltrim($class, '\\');
		$fileName  = '';
		$namespace = '';
		if ($lastNsPos = strripos($className, '\\'))
		{
			$namespace = substr($className, 0, $lastNsPos);
			$className = substr($className, $lastNsPos + 1);
			$fileName  = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
		}

		//$fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';	// this is breaking cases where a function name is using underscores instead of camel case
		$fileName .= $className . '.php';

		set_include_path(MAVERICK_BASEDIR . 'vendor');

		require_once $fileName;
	}
}

require_once MAVERICK_BASEDIR . 'vendor/maverick/maverick.php';

$app = \maverick\maverick::getInstance();
$app->build();
