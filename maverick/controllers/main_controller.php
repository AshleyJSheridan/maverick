<?php
class main_controller extends base_controller
{
	function __construct() {}
	
	function main($params)
	{
		var_dump($params, parse_url($params) );
	}

	function error()
	{
		echo 'error';
	}

}