<?php
namespace maverick;

class route
{
	private function __clone() {}
	
	static function any($route, $action, $args=null)
	{
		route::match_route('any', $route, $action, $args);
	}
	
	static function get($route, $action, $args=null)
	{
		route::match_route('get', $route, $action, $args);
	}

	static function post($route, $action, $args=null)
	{
		route::match_route('post', $route, $action, $args);
	}
	static function error($code, $action, $args=null)
	{
		$maverick = \maverick\maverick::getInstance();
		
		$maverick->set_error_route(intval($code), route::get_full_action($action, $args));
	}
	
	
	private static function match_route($protocol, $route, $action, $args)
	{
		$maverick = \maverick\maverick::getInstance();
		
		// return if a route has already been matched
		if(isset($maverick->route))
			return false;
		
		// return if the protocols don't match
		if(strtolower($_SERVER['REQUEST_METHOD']) == $protocol || $protocol == 'any')
		{
			preg_match("~^{$route}$~", $maverick->requested_route_string, $matches);
			
			// route match - assign the information back to the main maverick object
			if(!empty($matches))
				$maverick->controller = route::get_full_action($action, $args, $matches);

		}
		else
			return false;
	}
	
	private static function get_full_action($action, $args, $matches=array() )
	{
		// some routes don't specify the controller (e.g. errors), hence the check for the -> and assignment of null to the controller part otherwise
		list($a['controller_name'], $a['method'], $a['protocol'], $a['args']) = array_merge(
			((strpos($action, '->'))?explode('->', $action):array(null, $action)),
			array(
				strtolower($_SERVER['REQUEST_METHOD']),
				$args
			)
		);
		
		// individual segments of the URL have been matched by a route are and have been requested by the route to be passed on as arguments to the controller
		if(!empty($args) && count($matches) > 1 )
		{
			$a['args'] = (array)$a['args'];

			// go through array of arguments that are being passed and check them for placeholders ($1, $2, etc)
			foreach($a['args'] as $key => &$arg)
			{
				// the placeholder check
				if(preg_match_all('/\$(\d+)/', $arg, $arg_matches) && !empty($arg_matches[1]) )
				{
					// now go through the matched arguments and see if they are valid, and replace each one with the part of the route matched by the regex in the routes file
					foreach($arg_matches[1] as $arg_key => $arg_m)
					{
						$arg_m = intval($arg_m);
						if(!$arg_m || !isset($matches[$arg_m]) )
							continue;
						
						$arg = str_replace($arg_matches[0][$arg_key], $matches[$arg_m], $arg);
					}
				}
			}
		}
		
		return $a;
	}
}