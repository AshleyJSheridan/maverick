<?php
class cultures_controller extends cms_controller
{	
	function __construct()
	{
		parent::__construct();
	}
	
	function cultures($params)
	{
		$cultures = cms::get_all_cultures();
		
		var_dump($cultures);
	}
}