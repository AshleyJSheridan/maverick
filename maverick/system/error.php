<?php
class error
{
	private function __clone() {}
	
	static function show($message, $code=500)
	{
		http_response_code($code);
		
		die($message);
	}
}