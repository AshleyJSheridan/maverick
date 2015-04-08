<?php
/**
 * a class which handles errors for the application
 */
class error
{
	private function __clone() {}
	
	/**
	 * static method used to force show an error, with optional HTTP status code
	 * using the view file specified in the config
	 * this method terminates the rest of the application execution
	 * @param string $message the error message to show
	 * @param int $http_code the HTTP status code to respond with
	 */
	static function show($message, $http_code=500)
	{
		$maverick = \maverick\maverick::getInstance();
		
		if($maverick->get_config('config.debug'))
		{
			http_response_code($http_code);

			die($message);
		}
		else
		{
			$file = MAVERICK_BASEDIR . "views/{$maverick->error_routes[$http_code]['method']}.php";
			if(isset($maverick->error_routes[$http_code]) && file_exists($file) )
				$view = view::make($maverick->error_routes[$http_code]['method'])->render();
		}
	}
	
	/**
	 * static method used to log an error and optionally show it also
	 * @param string $message the error message to show
	 * @param bool $show whether or not to also show the error on screen
	 * @param int $http_code the HTTP status code to respond with
	 */
	static function log($message, $show=false, $http_code=500)
	{
		$maverick = \maverick\maverick::getInstance();
		
		//$caller = debug_backtrace();
		if($maverick->get_config('config.error_detail'))
			$message .= error::generate_call_trace();	
		
		if($maverick->get_config('config.log_errors'))	// only log the errors if the config says to
		{
			$log_date = date("y-m-d");
			error_log("\n\n$message", 3, MAVERICK_BASEDIR . "../logs/error-$log_date.log");
		}		
		
		if($show)
			error::show(nl2br($message), $http_code);
	}
	
	/**
	 * generates a full stack trace to be used in the log and output message
	 * @return string
	 */
	private static function generate_call_trace()
	{
		$e = new Exception();
		$trace = explode("\n", $e->getTraceAsString());

		$trace = array_reverse($trace);
		array_shift($trace); // remove {main}
		array_pop($trace); // remove call to this method
		$length = count($trace);
		$result = '';
		
		for ($i = 0; $i < $length; $i++)
			$result .= "\n" . ($i + 1)  . ')' . substr($trace[$i], strpos($trace[$i], ' ')); // replace '#someNum' with '$i)', set the right ordering
		
		return $result;
	}
}