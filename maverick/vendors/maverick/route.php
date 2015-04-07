<?php
namespace maverick;

/**
 * a routing class used to parse routes and match them against what is requested by the user
 */
class route
{
	private function __clone() {}
	
	/**
	 * match any type of request type
	 * @param string $route the route to match against
	 * @param string $action the action to take, usually in the form of controller->method
	 * @param array $args an array of matched parameters to pass to the routing controller class
	 */
	static function any($route, $action, $args=null)
	{
		route::match_route('any', $route, $action, $args);
	}
	
	/**
	 * match GET request types
	 * @param string $route the route to match against
	 * @param string $action the action to take, usually in the form of controller->method
	 * @param array $args an array of matched parameters to pass to the routing controller class
	 */
	static function get($route, $action, $args=null)
	{
		route::match_route('get', $route, $action, $args);
	}

	/**
	 * match POST request types
	 * @param string $route the route to match against
	 * @param string $action the action to take, usually in the form of controller->method
	 * @param array $args an array of matched parameters to pass to the routing controller class
	 */
	static function post($route, $action, $args=null)
	{
		route::match_route('post', $route, $action, $args);
	}
	
	/**
	 * set the actions for types of errors
	 * @param int $code HTTP status code
	 * @param string $action the action to take, usually in the form of controller->method
	 * @param array $args an array of matched parameters to pass to the routing controller class
	 */
	static function error($code, $action, $args=null)
	{
		$maverick = \maverick\maverick::getInstance();
		
		$maverick->set_error_route(intval($code), route::get_full_action($action, $args));
	}
	
	/**
	 * match the requested route with one of the routes added to the routes array on the main maverick class
	 * @param string $protocol the protocol specified in the routes, either post, get, or any
	 * @param string $route the route string
	 * @param string $action the action to take, usually in the form of controller->method
	 * @param array $args an array of matched parameters to pass to the routing controller class
	 * @return boolean
	 */
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
	
	/**
	 * use the matched routes information and assing the controller, and method if applicable, to the main maverick class member variables
	 * @param string $action the action to take, usually in the form of controller->method
	 * @param array $args the arguments to match in the route
	 * @param array $matches the matches found with preg_match to map against $args
	 * @return array
	 */
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