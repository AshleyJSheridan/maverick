<?php
class pages_controller extends cms_controller
{	
	function __construct()
	{
		parent::__construct();
	}
	
	function pages($params)
	{
		var_dump($params);
	}
	
}