<?php
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
		$maverick = maverick::getInstance();
		
		$maverick->set_error_route(intval($code), route::get_full_action($action, $args));
	}
	
	
	private static function match_route($protocol, $route, $action, $args)
	{
		$maverick = maverick::getInstance();
		
		// return if a route has already been matched
		if(isset($maverick->route))
			return false;
		
		// return if the protocols don't match
		if(strtolower($_SERVER['REQUEST_METHOD']) == $protocol || $protocol == 'any')
		{
			preg_match("~^{$route}$~", $maverick->requested_route_string, $matches);
			
			// route match - assign the information back to the main maverick object
			if(!empty($matches))
				$maverick->controller = route::get_full_action($action, $args);

		}
		else
			return false;
	}
	
	private static function get_full_action($action, $args)
	{
		list($a['controller_name'], $a['method'], $a['protocol'], $a['args']) = array_merge(
			explode('->', $action),
			array(
				strtolower($_SERVER['REQUEST_METHOD']),
				$args
			)
		);
		return $a;
	}
}