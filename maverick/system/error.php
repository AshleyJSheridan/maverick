<?php
class error
{
	private function __clone() {}
	
	static function show($message, $http_code=500)
	{
		//TODO: check to see of debug mode is on before spitting out errors - maybe show something nicer if debug is off
		http_response_code($http_code);
		
		die($message);
	}
	
	static function log($message, $show=false, $http_code=500)
	{
		$log_date = date("y-m-d");
		
		$caller = debug_backtrace();
		$message .= error::generate_call_trace();
		
		$maverick = maverick::getInstance();
		if($maverick->get_config('config.log_errors'))	// only log the errors if the config says to
			error_log("\n\n$message", 3, MAVERICK_BASEDIR . "../logs/error-$log_date.log");
		
		if($show)
			error::show(nl2br($message), $http_code);
	}
	
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