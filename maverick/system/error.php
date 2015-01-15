<?php
class error
{
	private function __clone() {}
	
	static function show($message, $code=500)
	{
		//TODO: check to see of debug mode is on before spitting out errors - maybe show something nicer if debug is off
		http_response_code($code);
		
		die($message);
	}
}